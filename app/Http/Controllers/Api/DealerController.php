<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dealer;
use Illuminate\Support\Facades\Validator;

class DealerController extends Controller
{
    public function index()
    {
        return response()->json(Dealer::all(), 200);
    }

    public function store(Request $request)
    {
        $inputData = $request->all();
        $commonRules = [
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kontak' => 'required|string|max:50',
        ];

        if (isset($inputData[0]) && is_array($inputData[0])) {
            $createdDealers = [];
            $errors = [];
            foreach ($inputData as $index => $item) {
                $validator = Validator::make($item, $commonRules);
                if ($validator->fails()) {
                    $errors[] = ['item_index' => $index, 'errors' => $validator->errors()];
                    continue;
                }
                $createdDealers[] = Dealer::create($validator->validated());
            }
            if (!empty($errors)) {
                return response()->json(['message' => 'Beberapa dealer gagal ditambahkan.', 'errors' => $errors, 'created' => $createdDealers], 422);
            }
            return response()->json(['message' => 'Semua dealer berhasil ditambahkan.', 'data' => $createdDealers], 201);
        } else {
            $validator = Validator::make($inputData, $commonRules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $dealer = Dealer::create($validator->validated());
            return response()->json($dealer, 201);
        }
    }

    public function show(string $id)
    {
        $dealer = Dealer::find($id);
        if (!$dealer) {
            return response()->json(['message' => 'Dealer tidak ditemukan.'], 404);
        }
        return response()->json($dealer, 200);
    }

    public function update(Request $request, string $id)
    {
        $dealer = Dealer::find($id);
        if (!$dealer) {
            return response()->json(['message' => 'Dealer tidak ditemukan.'], 404);
        }
        $updateRules = [
            'nama' => 'sometimes|required|string|max:255',
            'alamat' => 'sometimes|required|string',
            'kontak' => 'sometimes|required|string|max:50',
        ];
        $validator = Validator::make($request->all(), $updateRules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $dealer->update($validator->validated());
        return response()->json($dealer, 200);
    }

    public function destroy(string $id)
    {
        $dealer = Dealer::find($id);
        if (!$dealer) {
            return response()->json(['message' => 'Dealer tidak ditemukan.'], 404);
        }
        $dealer->delete();
        return response()->json(['message' => 'Dealer berhasil dihapus.'], 200);
    }
}