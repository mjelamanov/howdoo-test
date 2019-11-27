<?php

namespace App\Enums;

use MadWeb\Enum\Enum;

/**
 * Class DocumentStatus
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 *
 * @method static \App\Enums\DocumentStatus DRAFT()
 * @method static \App\Enums\DocumentStatus PUBLISHED()
 */
final class DocumentStatus extends Enum
{
    const __default = self::DRAFT;

    private const DRAFT = 'draft';
    private const PUBLISHED = 'published';
}
