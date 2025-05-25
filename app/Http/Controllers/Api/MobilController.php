<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mobil;
use Illuminate\Support\Facades\Validator;

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
            'gambar' => 'required|string|max:255',
        ];

        if (isset($inputData[0]) && is_array($inputData[0])) {
            $createdMobil = [];
            $errors = [];
            foreach ($inputData as $index => $item) {
                $validator = Validator::make($item, $commonRules);
                if ($validator->fails()) {
                    $errors[] = ['item_index' => $index, 'errors' => $validator->errors()];
                    continue;
                }
                $createdMobil[] = Mobil::create($validator->validated());
            }
            if (!empty($errors)) {
                return response()->json(['message' => 'Beberapa mobil gagal ditambahkan.', 'errors' => $errors, 'created' => $createdMobil], 422);
            }
            return response()->json(['message' => 'Semua mobil berhasil ditambahkan.', 'data' => $createdMobil], 201);
        } else {
            $validator = Validator::make($inputData, $commonRules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $mobil = Mobil::create($validator->validated());
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
            'gambar' => 'sometimes|required|string|max:255',
        ];
        $validator = Validator::make($request->all(), $updateRules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $mobil->update($validator->validated());
        return response()->json($mobil, 200);
    }

    public function destroy(string $id)
    {
        $mobil = Mobil::find($id);
        if (!$mobil) {
            return response()->json(['message' => 'Mobil tidak ditemukan.'], 404);
        }
        $mobil->delete();
        return response()->json(['message' => 'Mobil berhasil dihapus.'], 200);
    }
}