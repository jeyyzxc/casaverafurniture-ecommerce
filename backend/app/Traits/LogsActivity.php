<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Log an activity
     */
    protected function logActivity(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $properties = null
    ): void {
        try {
            ActivityLog::log(
                $action,
                $module,
                $description,
                $subject,
                $oldValues,
                $newValues,
                $properties
            );
        } catch (\Exception $e) {
            // Silently fail logging to prevent breaking the main operation
            \Log::warning('Failed to log activity', [
                'error' => $e->getMessage(),
                'action' => $action,
                'module' => $module,
            ]);
        }
    }

    /**
     * Get old values from model before update
     */
    protected function getOldValues(Model $model, array $fields): array
    {
        $oldValues = [];
        foreach ($fields as $field) {
            if (isset($model->getOriginal()[$field])) {
                $oldValues[$field] = $model->getOriginal()[$field];
            }
        }
        return $oldValues;
    }

    /**
     * Get new values from request or model
     */
    protected function getNewValues($data, array $fields): array
    {
        $newValues = [];
        if (is_array($data)) {
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $newValues[$field] = $data[$field];
                }
            }
        } elseif ($data instanceof Model) {
            foreach ($fields as $field) {
                if (isset($data->$field)) {
                    $newValues[$field] = $data->$field;
                }
            }
        }
        return $newValues;
    }
}
