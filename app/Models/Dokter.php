<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;

    protected $table = 'dokter';

    protected $primaryKey = 'kd_dokter';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kd_dokter',
        'nm_dokter',
        'jk',
        'tmp_lahir',
        'tgl_lahir',
        'gol_drh',
        'agama',
        'almt_tgl',
        'no_telp',
        'email',
        'stts_nikah',
        'kd_sps',
        'alumni',
        'no_ijn_praktek',
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
            'tgl_lahir' => 'date',
        ];
    }

    // /**
    //  * Get the spesialis that owns the Dokter.
    //  */
    // public function spesialis()
    // {
    //     return $this->belongsTo(Spesialis::class, 'kd_sps', 'kd_sps');
    // }

    // /**
    //  * Get the pegawai that owns the Dokter.
    //  */
    // public function pegawai()
    // {
    //     return $this->belongsTo(Pegawai::class, 'kd_dokter', 'nik');
    // }
}
