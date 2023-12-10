<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'authorization_code',
        'value',
        'source_account_id',
        'destination_account_id',
    ];

    // Se você quiser adicionar relacionamentos ou outros métodos, faça isso aqui
}
