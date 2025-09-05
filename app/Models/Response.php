<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $fillable = ['vacancy_id', 'response_id', 'fio', 'email', 'phone', 'comment', 'category', 'title'];
}
