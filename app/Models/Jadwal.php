<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';

    // Composite primary key (kd_dokter, hari_kerja, jam_mulai) - Laravel does not support composite keys natively.
    // We'll keep kd_dokter as primaryKey for Eloquent convenience and disable auto-incrementing.
    protected $primaryKey = 'kd_dokter';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'kd_dokter',
        'hari_kerja',
        'jam_mulai',
        'jam_selesai',
        'kd_poli',
        'kuota',
    ];

    /**
     * Relation to Dokter
     */
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    /**
     * Relation to Poliklinik
     */
    public function poliklinik()
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }
}
