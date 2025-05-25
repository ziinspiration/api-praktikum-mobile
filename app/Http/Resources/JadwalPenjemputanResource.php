<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JadwalPenjemputanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lokasi_penjemputan' => $this->lokasi_penjemputan,
            'jenis_sampah' => $this->jenis_sampah,
            'tanggal_penjemputan' => $this->tanggal_penjemputan->format('Y-m-d'),
            'waktu_estimasi' => $this->waktu_estimasi,
            'status' => $this->status,
            'catatan' => $this->catatan,
            'nama_pelapor' => $this->nama_pelapor,
            'volume_sampah_kg' => (float) $this->volume_sampah_kg,
            'dibuat_pada' => $this->created_at->format('Y-m-d H:i:s'),
            'diperbarui_pada' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
