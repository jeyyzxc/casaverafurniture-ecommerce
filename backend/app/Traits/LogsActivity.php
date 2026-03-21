<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    
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
            
            \Log::warning('Failed to log activity', [
                'error' => $e->getMessage(),
                'action' => $action,
                'module' => $module,
            ]);
        }
    }

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
