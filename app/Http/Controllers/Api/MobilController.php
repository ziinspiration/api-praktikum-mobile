<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mobil;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MobilController extends Controller
{
    public function index()
    {
        return response()->json(Mobil::all(), 200);
    }

    public function store(Request $request)
    {
        $inputData = $request->all();

        $commonRules = [
            'nama' => 'required|string|max:100',
            'tipe' => 'required|string|max:100',
            'tahun' => 'required|integer|digits:4',
            'harga' => 'required|numeric|min:0',
            'mesin' => 'required|string|max:100',
            'transmisi' => 'required|string|max:100',
            'kapasitas_bensin' => 'required|string|max:50',
            'warna' => 'required|string|max:50',
            'fitur_lain' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        if (isset($inputData[0]) && is_array($inputData[0])) {
            $createdMobil = [];
            $errors = [];
            foreach ($inputData as $index => $item) {
                $dataToValidate = $item;
                if ($request->hasFile("{$index}.gambar")) {
                    $dataToValidate['gambar'] = $request->file("{$index}.gambar");
                }

                $validator = Validator::make($dataToValidate, $commonRules);
                if ($validator->fails()) {
                    $errors[] = ['item_index' => $index, 'errors' => $validator->errors()];
                    continue;
                }
                $validatedData = $validator->validated();
                if ($request->hasFile("{$index}.gambar")) {
                    $validatedData['gambar'] = $request->file("{$index}.gambar")->store('mobil_images', 'public');
                } else if (isset($item['gambar']) && filter_var($item['gambar'], FILTER_VALIDATE_URL)) {
                    $validatedData['gambar'] = $item['gambar'];
                }
                $createdMobil[] = Mobil::create($validatedData);
            }
            if (!empty($errors)) {
                return response()->json(['message' => 'Beberapa mobil gagal ditambahkan.', 'errors' => $errors, 'created' => $createdMobil], 422);
            }
            return response()->json(['message' => 'Semua mobil berhasil ditambahkan.', 'data' => $createdMobil], 201);
        } else {
            $dataToValidate = $inputData;
            if ($request->hasFile('gambar')) {
                $dataToValidate['gambar'] = $request->file('gambar');
            }
            $validator = Validator::make($dataToValidate, $commonRules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $validatedData = $validator->validated();
            if ($request->hasFile('gambar')) {
                $validatedData['gambar'] = $request->file('gambar')->store('mobil_images', 'public');
            } else if (isset($inputData['gambar']) && filter_var($inputData['gambar'], FILTER_VALIDATE_URL)) {
                $validatedData['gambar'] = $inputData['gambar'];
            }
            $mobil = Mobil::create($validatedData);
            return response()->json($mobil, 201);
        }
    }

    public function show(string $id)
    {
        $mobil = Mobil::find($id);
        if (!$mobil) {
            return response()->json(['message' => 'Mobil tidak ditemukan.'], 404);
        }
        return response()->json($mobil, 200);
    }

    public function update(Request $request, string $id)
    {
        $mobil = Mobil::find($id);
        if (!$mobil) {
            return response()->json(['message' => 'Mobil tidak ditemukan.'], 404);
        }
        $updateRules = [
            'nama' => 'sometimes|required|string|max:100',
            'tipe' => 'sometimes|required|string|max:100',
            'tahun' => 'sometimes|required|integer|digits:4',
            'harga' => 'sometimes|required|numeric|min:0',
            'mesin' => 'sometimes|required|string|max:100',
            'transmisi' => 'sometimes|required|string|max:100',
            'kapasitas_bensin' => 'sometimes|required|string|max:50',
            'warna' => 'sometimes|required|string|max:50',
            'fitur_lain' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'gambar' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $dataToValidate = $request->all();
        if ($request->hasFile('gambar')) {
            $dataToValidate['gambar'] = $request->file('gambar');
        }

        $validator = Validator::make($dataToValidate, $updateRules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if ($request->hasFile('gambar')) {
            if ($mobil->gambar && Storage::disk('public')->exists($mobil->gambar)) {
                Storage::disk('public')->delete($mobil->gambar);
            }
            $validatedData['gambar'] = $request->file('gambar')->store('mobil_images', 'public');
        } else if ($request->filled('gambar') && filter_var($request->gambar, FILTER_VALIDATE_URL)) {
            $validatedData['gambar'] = $request->gambar;
        } else if ($request->exists('gambar') && is_null($request->gambar) && $mobil->gambar) {
            Storage::disk('public')->delete($mobil->gambar);
            $validatedData['gambar'] = null;
        }


        $mobil->update($validatedData);
        return response()->json($mobil, 200);
    }

    public function destroy(string $id)
    {
        $mobil = Mobil::find($id);
        if (!$mobil) {
            return response()->json(['message' => 'Mobil tidak ditemukan.'], 404);
        }
        if ($mobil->gambar && Storage::disk('public')->exists($mobil->gambar)) {
            Storage::disk('public')->delete($mobil->gambar);
        }
        $mobil->delete();
        return response()->json(['message' => 'Mobil berhasil dihapus.'], 200);
    }
}
