<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Mobil;
use App\Models\Dealer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->tipe_akun === 'administrator') {
            return response()->json(Booking::with(['user', 'mobil', 'dealer'])->latest('tanggal')->latest('waktu')->get(), 200);
        }
        return response()->json($user->bookings()->with(['mobil', 'dealer'])->latest('tanggal')->latest('waktu')->get(), 200);
    }

    public function store(Request $request)
    {
        $inputDataArray = $request->all();
        $loggedInUser = Auth::user();

        $baseRules = [
            'mobil_id' => 'required|exists:mobil,id',
            'dealer_id' => 'required|exists:dealer,id',
            'tanggal' => 'required|date_format:Y-m-d|after_or_equal:today',
            'waktu' => 'required|date_format:H:i',
            'status' => 'nullable|string|in:menunggu,selesai,batal',
        ];

        if (isset($inputDataArray[0]) && is_array($inputDataArray[0])) {
            $createdBookings = [];
            $errors = [];

            foreach ($inputDataArray as $index => $itemData) {
                $rulesForThisItem = $baseRules;
                $userIdToUse = $loggedInUser->id;

                if ($loggedInUser->tipe_akun === 'administrator' && isset($itemData['user_id'])) {
                    $rulesForThisItem['user_id'] = 'required|exists:users,id';
                    $userIdToUse = $itemData['user_id'];
                }

                if (isset($itemData['tanggal']) && isset($itemData['waktu'])) {
                    try {
                        $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i', $itemData['tanggal'] . ' ' . $itemData['waktu']);
                        if ($itemData['tanggal'] == Carbon::today()->toDateString() && $bookingDateTime->isPast()) {
                            $errors[] = ['item_index' => $index, 'errors' => ['waktu' => ['Waktu booking untuk hari ini tidak boleh di masa lalu.']]];
                            continue;
                        }
                    } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                        $errors[] = ['item_index' => $index, 'errors' => ['waktu' => ['Format waktu tidak valid (HH:MM).']]];
                        continue;
                    } catch (\Exception $e) {
                        $errors[] = ['item_index' => $index, 'errors' => ['waktu' => ['Terjadi kesalahan pada tanggal/waktu.']]];
                        continue;
                    }
                }

                $validator = Validator::make($itemData, $rulesForThisItem);
                if ($validator->fails()) {
                    $errors[] = ['item_index' => $index, 'errors' => $validator->errors()];
                    continue;
                }

                $validatedData = $validator->validated();
                $validatedData['user_id'] = $userIdToUse;

                if (!isset($validatedData['status'])) {
                    $validatedData['status'] = 'menunggu';
                }
                $createdBookings[] = Booking::create($validatedData);
            }

            if (!empty($errors)) {
                return response()->json(['message' => 'Beberapa booking gagal disimpan.', 'errors' => $errors, 'created_count' => count($createdBookings), 'created_items' => $createdBookings], 422);
            }
            return response()->json(['message' => 'Semua booking berhasil disimpan.', 'data' => $createdBookings], 201);
        } else {
            $rulesForSingleItem = $baseRules;
            $userIdToUse = $loggedInUser->id;

            if ($loggedInUser->tipe_akun === 'administrator' && $request->filled('user_id')) {
                $rulesForSingleItem['user_id'] = 'required|exists:users,id';
                $userIdToUse = $request->input('user_id');
            }

            if ($request->filled('tanggal') && $request->filled('waktu')) {
                try {
                    $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->input('tanggal') . ' ' . $request->input('waktu'));
                    if ($request->input('tanggal') == Carbon::today()->toDateString() && $bookingDateTime->isPast()) {
                        return response()->json(['errors' => ['waktu' => ['Waktu booking untuk hari ini tidak boleh di masa lalu.']]], 422);
                    }
                } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                    return response()->json(['errors' => ['waktu' => ['Format waktu tidak valid (HH:MM).']]], 422);
                } catch (\Exception $e) {
                    return response()->json(['errors' => ['waktu' => ['Terjadi kesalahan pada tanggal/waktu.']]], 422);
                }
            }

            $validator = Validator::make($inputDataArray, $rulesForSingleItem);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();
            $validatedData['user_id'] = $userIdToUse;

            if (!isset($validatedData['status'])) {
                $validatedData['status'] = 'menunggu';
            }

            $booking = Booking::create($validatedData);
            return response()->json($booking->load(['user', 'mobil', 'dealer']), 201);
        }
    }

    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $booking = Booking::with(['user', 'mobil', 'dealer'])->find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan.'], 404);
        }

        if ($user->tipe_akun !== 'administrator' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($booking, 200);
    }

    public function update(Request $request, string $id)
    {
        $loggedInUser = Auth::user();
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan.'], 404);
        }

        if ($loggedInUser->tipe_akun !== 'administrator' && $booking->user_id !== $loggedInUser->id) {
            return response()->json(['message' => 'Unauthorized to update this booking.'], 403);
        }

        $updateRules = [
            'mobil_id' => 'sometimes|required|exists:mobil,id',
            'dealer_id' => 'sometimes|required|exists:dealer,id',
            'tanggal' => 'sometimes|required|date_format:Y-m-d',
            'waktu' => 'sometimes|required|date_format:H:i',
            'status' => 'sometimes|required|string|in:menunggu,selesai,batal',
        ];

        $inputData = $request->all();

        $tanggalToValidate = $request->input('tanggal', $booking->tanggal->format('Y-m-d'));
        $waktuToValidate = $request->input('waktu', $booking->waktu->format('H:i'));

        if ($tanggalToValidate < Carbon::today()->toDateString()) {
            return response()->json(['errors' => ['tanggal' => ['Tanggal booking tidak boleh di masa lalu.']]], 422);
        }

        if ($tanggalToValidate == Carbon::today()->toDateString()) {
            try {
                $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i', $tanggalToValidate . ' ' . $waktuToValidate);
                if ($bookingDateTime->isPast()) {
                    return response()->json(['errors' => ['waktu' => ['Waktu booking untuk hari ini tidak boleh di masa lalu.']]], 422);
                }
            } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                return response()->json(['errors' => ['waktu' => ['Format waktu tidak valid (HH:MM).']]], 422);
            } catch (\Exception $e) {
                return response()->json(['errors' => ['waktu' => ['Terjadi kesalahan pada tanggal/waktu.']]], 422);
            }
        }

        $validator = Validator::make($request->all(), $updateRules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if ($loggedInUser->tipe_akun !== 'administrator' && isset($validatedData['user_id']) && $validatedData['user_id'] != $booking->user_id) {
            unset($validatedData['user_id']);
        }
        if ($loggedInUser->tipe_akun !== 'administrator' && isset($validatedData['status']) && $booking->user_id == $loggedInUser->id && $validatedData['status'] != 'batal' && $booking->status != 'menunggu') {
            if ($booking->status == $validatedData['status']) {
                // Biarkan jika statusnya sama
            } else if ($booking->status == 'menunggu' && $validatedData['status'] == 'batal') {
                // Biarkan user membatalkan jika masih menunggu
            } else {
                return response()->json(['message' => 'Anda hanya dapat membatalkan booking atau status tidak dapat diubah.'], 403);
            }
        }

        $booking->update($validatedData);
        return response()->json($booking->load(['user', 'mobil', 'dealer']), 200);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan.'], 404);
        }

        if ($user->tipe_akun !== 'administrator' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to delete this booking.'], 403);
        }

        if ($user->tipe_akun !== 'administrator' && $booking->user_id == $user->id && !in_array($booking->status, ['menunggu', 'batal'])) {
            return response()->json(['message' => 'Booking yang sudah diproses atau selesai tidak dapat dihapus oleh Anda.'], 403);
        }

        $booking->delete();
        return response()->json(['message' => 'Booking berhasil dihapus.'], 200);
    }
}