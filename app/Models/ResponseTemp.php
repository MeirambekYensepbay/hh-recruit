<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseTemp extends Model
{
    protected $fillable = ['response_id', 'fio', 'url', 'vacancy_id'];

    public function response(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Response::class, 'fio', 'fio');
    }
}
