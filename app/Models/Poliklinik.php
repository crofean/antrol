<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poliklinik extends Model
{
    use HasFactory;

    protected $table = 'poliklinik';

    protected $primaryKey = 'kd_poli';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kd_poli',
        'nm_poli',
        'registrasi',
        'registrasilama',
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
            'registrasi' => 'decimal:2',
            'registrasilama' => 'decimal:2',
            'status' => 'string',
        ];
    }

    /**
     * Get the reg_periksa for the Poliklinik.
     */
    public function regPeriksa()
    {
        return $this->hasMany(RegPeriksa::class, 'kd_poli', 'kd_poli');
    }
}
