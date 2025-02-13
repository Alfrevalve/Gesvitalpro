<?php

namespace App\Events\Performance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlowRequestDetected
{
    use Dispatchable, SerializesModels;

    public $duration;
    public $route;
    public $queries;
    public $timestamp;

    public function __construct(float $duration, ?string $route, array $queries)
    {
        $this->duration = $duration;
        $this->route = $route;
        $this->queries = $queries;
        $this->timestamp = now();
    }
}
