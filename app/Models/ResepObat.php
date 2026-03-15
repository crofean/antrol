<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResepObat extends Model
{
    use HasFactory;

    protected $table = 'resep_obat';

    protected $primaryKey = 'no_resep';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'no_resep',
        'tgl_perawatan',
        'jam',
        'no_rawat',
        'kd_dokter',
        'tgl_peresepan',
        'jam_peresepan',
        'status',
        'tgl_penyerahan',
        'jam_penyerahan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'tgl_perawatan' => 'date',
        'jam' => 'datetime:H:i:s',
        'tgl_peresepan' => 'date',
        'jam_peresepan' => 'datetime:H:i:s',
        'tgl_penyerahan' => 'date',
        'jam_penyerahan' => 'datetime:H:i:s',
    ];

    /**
     * Get the reg_periksa that owns the ResepObat.
     */
    public function regPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }

    /**
     * Get the dokter that owns the ResepObat.
     */
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }
}
