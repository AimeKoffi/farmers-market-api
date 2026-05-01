<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'supervisor_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Un supervisor a plusieurs operators
    public function operators()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    // Un operator appartient à un supervisor
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    // Transactions effectuées par cet operator
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'operator_id');
    }

    // Remboursements enregistrés par cet operator
    public function repayments()
    {
        return $this->hasMany(Repayment::class, 'operator_id');
    }

    // Helpers de rôle
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }
}