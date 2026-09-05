<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;

class AuditLogService
{
    public function log(
        User $user,
        string $action,
        string $module,
        ?int $recordId = null,
        ?array $oldData = null,
        ?array $newData = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId ? (string) $recordId : null,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => RequestFacade::ip(),
            'user_agent' => RequestFacade::userAgent(),
        ]);
    }

    public function getRecent(int $days = 7, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with('user')
            ->recent($days)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getByUser(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with('user')
            ->byUser($userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getByModule(string $module, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with('user')
            ->byModule($module)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
