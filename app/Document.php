<?php

namespace App;

use App\Enums\DocumentStatus;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use MadWeb\Enum\EnumCastable;

class Document extends Model
{
    use EnumCastable;

    /**
     * @var array
     */
    protected $fillable = ['status', 'payload', 'user_id'];

    /**
     * @var array
     */
    protected $hidden = ['user_id'];

    /**
     * @var array
     */
    protected $casts = [
        'status' => DocumentStatus::class,
        'payload' => 'array',
        self::CREATED_AT => 'date:' . DateTime::ATOM,
        self::UPDATED_AT => 'date:' . DateTime::ATOM,
    ];
}
