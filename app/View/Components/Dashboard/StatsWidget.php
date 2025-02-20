<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;
use App\Services\UiOptimizationService;
use App\Services\PerformanceMonitor;

class StatsWidget extends Component
{
    public $title;
    public $value;
    public $icon;
    public $color;
    public $trend;
    public $trendValue;
    public $refreshInterval;

    protected $uiOptimizer;
    protected $performanceMonitor;

    public function __construct(
        string $title,
        $value,
        string $icon = 'chart-bar',
        string $color = 'primary',
        string $trend = 'neutral',
        $trendValue = null,
        int $refreshInterval = 30,
        UiOptimizationService $uiOptimizer,
        PerformanceMonitor $performanceMonitor
    ) {
        $this->title = $title;
        $this->value = $value;
        $this->icon = $icon;
        $this->color = $color;
        $this->trend = $trend;
        $this->trendValue = $trendValue;
        $this->refreshInterval = $refreshInterval;
        $this->uiOptimizer = $uiOptimizer;
        $this->performanceMonitor = $performanceMonitor;
    }

    public function render()
    {
        return view('components.dashboard.stats-widget', [
            'darkMode' => session('dark_mode', false),
            'animations' => $this->uiOptimizer->getAnimationsConfig(),
            'chartConfig' => $this->getChartConfig(),
        ]);
    }

    protected function getChartConfig()
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'theme' => session('dark_mode', false) ? 'dark' : 'light',
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeInOutQuart'
            ],
        ];
    }

    public function shouldComponentUpdate(): bool
    {
        return $this->performanceMonitor->shouldShowPerformanceAlert();
    }

    public function formatValue($value)
    {
        if (is_numeric($value)) {
            return number_format($value);
        }
        return $value;
    }

    public function getTrendClass(): string
    {
        return match($this->trend) {
            'up' => 'text-success',
            'down' => 'text-danger',
            default => 'text-muted'
        };
    }

    public function getTrendIcon(): string
    {
        return match($this->trend) {
            'up' => 'arrow-up',
            'down' => 'arrow-down',
            default => 'minus'
        };
    }
}
