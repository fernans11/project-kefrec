<?php

namespace App\Models;

use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Complaint extends Model
{
    protected $fillable = [
        'customer_id',
        'handled_by',
        'name',
        'email',
        'phone',
        'message',
        'status',
        'response',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public static function allowedStatusTransitions(): array
    {
        return [
            'open' => ['progress', 'closed'],
            'progress' => ['closed'],
            'closed' => [],
        ];
    }

    public static function canTransition(string $fromStatus, string $nextStatus): bool
    {
        if ($fromStatus === $nextStatus) {
            return true;
        }

        $allowed = self::allowedStatusTransitions()[$fromStatus] ?? [];
        return in_array($nextStatus, $allowed, true);
    }

    public function canTransitionTo(string $nextStatus): bool
    {
        return self::canTransition((string) $this->status, $nextStatus);
    }

    protected static function booted(): void
    {
        static::created(function (Complaint $complaint) {
            AuditLogger::log(
                'complaint.created',
                $complaint,
                'Komplain dibuat',
                ['status' => $complaint->status]
            );
        });

        static::updating(function (Complaint $complaint) {
            if (! $complaint->isDirty('status')) {
                return;
            }

            $next = (string) $complaint->status;
            $current = (string) $complaint->getOriginal('status');

            if ($current === '') {
                return;
            }

            if (! self::canTransition($current, $next)) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status komplain tidak valid.',
                ]);
            }
        });

        static::updated(function (Complaint $complaint) {
            if (! $complaint->wasChanged(['status', 'response', 'message', 'handled_by'])) {
                return;
            }

            AuditLogger::log(
                'complaint.updated',
                $complaint,
                'Komplain diperbarui',
                [
                    'status' => $complaint->status,
                    'changed' => array_keys($complaint->getChanges()),
                ]
            );
        });
    }
}
