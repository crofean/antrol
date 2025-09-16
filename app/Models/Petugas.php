<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    use HasFactory;

    protected $table = 'petugas';

    protected $primaryKey = 'nip';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nip',
        'nama',
        'jk',
        'tmp_lahir',
        'tgl_lahir',
        'gol_darah',
        'agama',
        'stts_nikah',
        'alamat',
        'kd_jbtn',
        'no_telp',
        'email',
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
    //  * Get the pegawai that owns the Petugas.
    //  */
    // public function pegawai()
    // {
    //     return $this->belongsTo(Pegawai::class, 'nip', 'nik');
    // }

    // /**
    //  * Get the jabatan that owns the Petugas.
    //  */
    // public function jabatan()
    // {
    //     return $this->belongsTo(Jabatan::class, 'kd_jbtn', 'kd_jbtn');
    // }
}
