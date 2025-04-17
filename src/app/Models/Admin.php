<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; //fortifyを使うために追加
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable //fortifyを使うためにAuthenticatableを継承
{
    use HasFactory, Notifiable;

    protected $fillable = ['email', 'password'];

    protected $hidden = ['password', 'remember_token'];
    
}
