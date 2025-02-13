<?php

namespace App\Events\Performance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowCacheHitRateDetected
{
    use Dispatchable, SerializesModels;

    public $hitRate;
    public $hits;
    public $misses;
    public $timestamp;

    public function __construct(float $hitRate, int $hits, int $misses)
    {
        $this->hitRate = $hitRate;
        $this->hits = $hits;
        $this->misses = $misses;
        $this->timestamp = now();
    }

    public function getTotalRequests(): int
    {
        return $this->hits + $this->misses;
    }

    public function getMissRate(): float
    {
        return 100 - $this->hitRate;
    }
}
