<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\ActivityLog;
use App\Notifications\SecurityAlert;

class SecurityMonitor
{
    public function checkSecurityEvents(): void
    {
        $today = now()->format('Y-m-d');
        $deniedAttempts = $this->getCacheValue('denied_attempts:' . $today, 0);
        $failedLogins = $this->getCacheValue('failed_logins:' . $today, 0);
        $suspiciousActivities = $this->getCacheValue('suspicious:' . $today, 0);

        if ($deniedAttempts > 50 || $failedLogins > 20 || $suspiciousActivities > 10) {
            $this->notifySecurityIssue([
                'denied_attempts' => $deniedAttempts,
                'failed_logins' => $failedLogins,
                'suspicious_activities' => $suspiciousActivities,
                'timestamp' => now(),
            ]);
        }
    }

    protected function notifySecurityIssue(array $data): void
    {
        // Log the security issue
        Log::warning('Alerta de seguridad detectada', $data);

        // Send Slack notification if configured
        if (config('logging.slack_webhook_url')) {
            Notification::route('slack', config('logging.slack_webhook_url'))
                ->notify(new SecurityAlert($data));
        }

        // Store in activity logs
        ActivityLog::create([
            'type' => 'security_alert',
            'description' => 'Alerta de seguridad detectada',
            'data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function getCacheValue(string $key, $default = null)
    {
        try {
            if (config('cache.default') === 'redis' && Cache::supportsTags()) {
                return Cache::tags(['security'])->get($key, $default);
            }
            return Cache::get($key, $default);
        } catch (\Exception $e) {
            logger()->error('Error accessing security cache: ' . $e->getMessage());
            return $default;
        }
    }

    protected function incrementCacheValue(string $key, array $tags = []): void
    {
        try {
            if (config('cache.default') === 'redis' && Cache::supportsTags()) {
                Cache::tags($tags)->increment($key);
            } else {
                Cache::increment($key);
            }
        } catch (\Exception $e) {
            logger()->error('Error incrementing security cache: ' . $e->getMessage());
        }
    }

    public function recordDeniedAccess(): void
    {
        $key = 'denied_attempts:' . now()->format('Y-m-d');
        $this->incrementCacheValue($key, ['security', 'access']);
    }

    public function recordFailedLogin(): void
    {
        $key = 'failed_logins:' . now()->format('Y-m-d');
        $this->incrementCacheValue($key, ['security', 'auth']);
    }

    public function recordSuspiciousActivity(): void
    {
        $key = 'suspicious:' . now()->format('Y-m-d');
        $this->incrementCacheValue($key, ['security', 'suspicious']);
    }
}
