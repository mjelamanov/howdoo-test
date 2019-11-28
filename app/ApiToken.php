<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'token', 'expires_at'];

    protected $casts = [
        self::CREATED_AT => 'date:' . DateTime::ATOM,
        self::UPDATED_AT => 'date:' . DateTime::ATOM,
        'expires_at' => 'date:' . DateTime::ATOM,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
