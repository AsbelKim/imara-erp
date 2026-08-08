<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Request;

/**
 * AuditLogService handles comprehensive audit logging for all model changes
 */
class AuditLogService
{
    /**
     * Log a model change
     */
    public static function log(
        string $action,
        Model $model,
        ?array $beforeValues = null,
        ?array $afterValues = null,
        ?string $description = null
    ): AuditLog {
        return AuditLog::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'model_type'   => get_class($model),
            'model_id'     => $model->id,
            'before_value' => $beforeValues,
            'after_value'  => $afterValues,
            'description'  => $description,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }

    /**
     * Log a model creation
     */
    public static function logCreated(Model $model, ?string $description = null): AuditLog
    {
        return self::log(
            'created',
            $model,
            beforeValues: null,
            afterValues: $model->getAttributes(),
            description: $description ?? "Created {$model::class}"
        );
    }

    /**
     * Log a model update
     */
    public static function logUpdated(Model $model, array $changes, ?string $description = null): AuditLog
    {
        $before = [];
        $after = [];

        foreach ($changes as $key => $value) {
            $before[$key] = $model->getOriginal($key);
            $after[$key] = $value;
        }

        return self::log(
            'updated',
            $model,
            beforeValues: $before,
            afterValues: $after,
            description: $description ?? "Updated {$model::class}"
        );
    }

    /**
     * Log a model deletion
     */
    public static function logDeleted(Model $model, ?string $description = null): AuditLog
    {
        return self::log(
            'deleted',
            $model,
            beforeValues: $model->getAttributes(),
            afterValues: null,
            description: $description ?? "Deleted {$model::class}"
        );
    }

    /**
     * Log a custom action
     */
    public static function logAction(
        string $action,
        Model $model,
        ?string $description = null,
        ?array $data = null
    ): AuditLog {
        return self::log(
            $action,
            $model,
            beforeValues: null,
            afterValues: $data,
            description: $description
        );
    }

    /**
     * Get audit history for a model
     */
    public static function getHistory(Model $model): array
    {
        return AuditLog::forModel(get_class($model), $model->id)
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * Get audit history for a user
     */
    public static function getUserHistory(int $userId, int $limit = 100): array
    {
        return AuditLog::byUser($userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get recent audit logs
     */
    public static function getRecent(int $limit = 50): array
    {
        return AuditLog::recent($limit)
            ->get()
            ->toArray();
    }
}
