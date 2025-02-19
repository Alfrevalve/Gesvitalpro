<?php

namespace Tests;

use PHPUnit\Framework\TestListener as PHPUnitTestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\AssertionFailedError;
use Throwable;
use Illuminate\Support\Facades\Log;
use App\Services\PerformanceMonitor;

class TestListener implements PHPUnitTestListener
{
    protected PerformanceMonitor $monitor;
    protected array $testTimes = [];
    protected array $testMemory = [];

    public function __construct()
    {
        $this->monitor = app(PerformanceMonitor::class);
    }

    public function addError(Test $test, Throwable $t, float $time): void
    {
        $this->logTestResult($test, 'Error', $t->getMessage());
        $this->recordTestMetrics($test, $time, 'error');
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->logTestResult($test, 'Warning', $e->getMessage());
        $this->recordTestMetrics($test, $time, 'warning');
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->logTestResult($test, 'Failure', $e->getMessage());
        $this->recordTestMetrics($test, $time, 'failure');
    }

    public function addSkipped(Test $test, Throwable $t, float $time): void
    {
        $this->logTestResult($test, 'Skipped', $t->getMessage());
    }

    public function startTestSuite(TestSuite $suite): void
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        Log::info("Starting test suite: {$suite->getName()}", [
            'tests_count' => $suite->count(),
            'start_time' => date('Y-m-d H:i:s'),
            'start_memory' => $this->formatBytes($startMemory)
        ]);

        $this->testTimes[$suite->getName()] = $startTime;
        $this->testMemory[$suite->getName()] = $startMemory;
    }

    public function endTestSuite(TestSuite $suite): void
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $startTime = $this->testTimes[$suite->getName()] ?? 0;
        $startMemory = $this->testMemory[$suite->getName()] ?? 0;

        $duration = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;

        Log::info("Completed test suite: {$suite->getName()}", [
            'duration' => round($duration, 2) . ' seconds',
            'memory_used' => $this->formatBytes($memoryUsed),
            'peak_memory' => $this->formatBytes(memory_get_peak_usage(true))
        ]);

        // Registrar métricas de rendimiento
        $this->monitor->recordTestSuiteMetrics([
            'suite' => $suite->getName(),
            'duration' => $duration,
            'memory_used' => $memoryUsed,
            'tests_count' => $suite->count(),
            'timestamp' => now()
        ]);
    }

    public function startTest(Test $test): void
    {
        $testName = $test->getName();
        $this->testTimes[$testName] = microtime(true);
        $this->testMemory[$testName] = memory_get_usage(true);

        Log::info("Starting test: $testName", [
            'class' => get_class($test),
            'start_time' => date('Y-m-d H:i:s'),
            'start_memory' => $this->formatBytes($this->testMemory[$testName])
        ]);
    }

    public function endTest(Test $test, float $time): void
    {
        $testName = $test->getName();
        $startTime = $this->testTimes[$testName] ?? 0;
        $startMemory = $this->testMemory[$testName] ?? 0;
        $endMemory = memory_get_usage(true);

        $duration = microtime(true) - $startTime;
        $memoryUsed = $endMemory - $startMemory;

        Log::info("Completed test: $testName", [
            'duration' => round($duration, 2) . ' seconds',
            'memory_used' => $this->formatBytes($memoryUsed)
        ]);

        $this->recordTestMetrics($test, $duration, 'completed');

        // Limpiar datos del test
        unset($this->testTimes[$testName]);
        unset($this->testMemory[$testName]);
    }

    protected function logTestResult(Test $test, string $status, string $message): void
    {
        Log::warning("Test {$status}: {$test->getName()}", [
            'message' => $message,
            'class' => get_class($test)
        ]);
    }

    protected function recordTestMetrics(Test $test, float $duration, string $status): void
    {
        $this->monitor->recordTestMetrics([
            'test' => $test->getName(),
            'class' => get_class($test),
            'duration' => $duration,
            'status' => $status,
            'memory_used' => memory_get_usage(true),
            'timestamp' => now()
        ]);
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
