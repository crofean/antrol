<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjab extends Model
{
    use HasFactory;

    protected $table = 'penjab';

    protected $primaryKey = 'kd_pj';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kd_pj',
        'png_jawab',
        'nama_perusahaan',
        'alamat_asuransi',
        'no_telp',
        'attn',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    /**
     * Get the pasien for the Penjab.
     */
    public function pasien()
    {
        return $this->hasMany(Pasien::class, 'kd_pj', 'kd_pj');
    }

    /**
     * Get the reg_periksa for the Penjab.
     */
    public function regPeriksa()
    {
        return $this->hasMany(RegPeriksa::class, 'kd_pj', 'kd_pj');
    }
}
