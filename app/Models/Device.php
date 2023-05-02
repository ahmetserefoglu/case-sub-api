<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table='devices';
    protected $fillable = [
        'uid',
        'app_id',
        'language',
        'os',
        'client_token',
    ];

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }
}
