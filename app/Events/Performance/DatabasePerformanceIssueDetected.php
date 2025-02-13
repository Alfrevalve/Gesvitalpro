<?php

namespace App\Events\Performance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DatabasePerformanceIssueDetected
{
    use Dispatchable, SerializesModels;

    public $type;
    public $details;
    public $timestamp;

    public function __construct(string $type, array $details)
    {
        $this->type = $type;
        $this->details = $details;
        $this->timestamp = now();
    }

    public function isCritical(): bool
    {
        return in_array($this->type, [
            'deadlock',
            'connection_failure',
            'replication_lag',
        ]);
    }
}
