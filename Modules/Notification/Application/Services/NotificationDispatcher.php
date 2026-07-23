<?php

declare(strict_types=1);

namespace Modules\Notification\Application\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Modules\Notification\Domain\Models\NotificationLog;
use Throwable;

class NotificationDispatcher
{
    public function dispatchEmail(string $tenantId, string $recipient, Mailable $mailable): void
    {
        $log = NotificationLog::create([
            'tenant_id' => $tenantId,
            'type' => 'email',
            'recipient' => $recipient,
            'subject' => $mailable->subject ?? get_class($mailable),
            'payload' => ['class' => get_class($mailable)],
            'status' => 'pending',
        ]);

        try {
            Mail::to($recipient)->send($mailable);

            $log->update(['status' => 'sent']);
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
