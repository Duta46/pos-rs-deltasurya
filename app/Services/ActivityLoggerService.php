<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLoggerService
{
    /**
     * Log an activity.
     *
     * @param string $action
     * @param string|null $description
     * @param int|null $userId
     * @return ActivityLog
     */
    public function log(string $action, ?string $description = null, ?int $userId = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}
