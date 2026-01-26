<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'causer_type',
        'causer_id',
        'causer_name',
        'subject_type',
        'subject_id',
        'subject_name',
        'action',
        'module',
        'description',
        'old_values',
        'new_values',
        'properties',
        'ip_address',
        'user_agent',
        'url',
        'method',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'properties' => 'array',
        ];
    }

    public function causer()
    {
        return $this->morphTo();
    }

    public function subject()
    {
        return $this->morphTo();
    }

    // Helper to create log entry
    public static function log(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $properties = null
    ): static {
        $causer = auth('admin')->user() ?? auth()->user();
        $request = request();

        // Get subject name if available
        $subjectName = null;
        if ($subject) {
            if (method_exists($subject, 'name')) {
                $subjectName = $subject->name;
            } elseif (method_exists($subject, 'full_name')) {
                $subjectName = $subject->full_name;
            } elseif (method_exists($subject, 'title')) {
                $subjectName = $subject->title;
            } elseif (isset($subject->name)) {
                $subjectName = $subject->name;
            }
        }

        return static::create([
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer?->id,
            'causer_name' => $causer?->full_name ?? $causer?->name ?? null,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'subject_name' => $subjectName,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'properties' => $properties,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);
    }
}
