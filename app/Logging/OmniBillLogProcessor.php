<?php

namespace App\Logging;

use Illuminate\Support\Facades\Auth;
use Monolog\LogRecord;

class OmniBillLogProcessor
{
    /**
     * Add OmniBill standard fields to every log record.
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $userId = Auth::check() ? Auth::id() : null;

        $extraFields = [
            'user_id' => $userId,
        ];

        return $record->with(extra: array_merge($record->extra, $extraFields));
    }
}
