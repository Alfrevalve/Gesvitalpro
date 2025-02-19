<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

if (!function_exists('activity')) {
    function activity() {
        return new class {
            protected $causedBy = null;
            protected $properties = [];

            public function causedBy($model) {
                $this->causedBy = $model;
                return $this;
            }

            public function withProperties(array $properties) {
                $this->properties = $properties;
                return $this;
            }

            public function log(string $action) {
                $properties = $this->properties;

                // Extract ip and user_agent if they exist
                $ip = $properties['ip'] ?? request()->ip();
                $userAgent = $properties['user_agent'] ?? request()->userAgent();
                unset($properties['ip'], $properties['user_agent']);

                return ActivityLog::create([
                    'action' => (string) $action, // Ensure action is cast to string
                    'model_type' => $this->causedBy ? get_class($this->causedBy) : null,
                    'model_id' => $this->causedBy ? $this->causedBy->getKey() : null,
                    'user_id' => Auth::id() ?? null,
                    'changes' => $properties, // Store remaining properties
                    'original' => [],
                    'ip_address' => $ip,
                    'user_agent' => $userAgent
                ]);
            }
        };
    }
}
