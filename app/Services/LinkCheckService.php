<?php

namespace App\Services;

use App\Models\BrokenLink;
use App\Models\LinkCheckHistory;
use App\Models\LinkCheckExclusion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LinkCheckService
{
    protected $exclusions;
    protected $options;

    public function __construct()
    {
        $this->exclusions = LinkCheckExclusion::active()->get();
        $this->options = [
            'timeout' => config('links.check_timeout', 10),
            'verify' => config('links.verify_ssl', true),
            'max_redirects' => config('links.max_redirects', 5),
        ];
    }

    /**
     * Verificar un enlace específico
     */
    public function checkUrl(string $url, string $source = null): array
    {
        // Verificar si el URL está excluido
        if ($this->isExcluded($url)) {
            return [
                'status' => 'excluded',
                'message' => 'URL excluded from checks',
            ];
        }

        $startTime = microtime(true);

        try {
            // Realizar la verificación
            $response = Http::withOptions($this->options)
                          ->withHeaders($this->getRequestHeaders())
                          ->get($url);

            $duration = microtime(true) - $startTime;

            $result = [
                'status' => $response->status(),
                'duration' => round($duration, 3),
                'response_data' => $this->getResponseData($response),
            ];

            // Registrar el resultado
            $this->logCheckResult($url, $source, $result);

            return $result;
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            $result = [
                'status' => 'error',
                'duration' => round($duration, 3),
                'message' => $e->getMessage(),
            ];

            // Registrar el error
            $this->logCheckResult($url, $source, $result);

            return $result;
        }
    }

    /**
     * Verificar múltiples enlaces en paralelo
     */
    public function checkUrls(array $urls): array
    {
        $results = [];
        $promises = [];

        foreach ($urls as $url => $source) {
            if ($this->isExcluded($url)) {
                $results[$url] = [
                    'status' => 'excluded',
                    'message' => 'URL excluded from checks',
                ];
                continue;
            }

            $startTime = microtime(true);

            $promises[$url] = Http::async()->withOptions($this->options)
                ->withHeaders($this->getRequestHeaders())
                ->get($url)
                ->then(
                    function ($response) use ($url, $source, $startTime, &$results) {
                        $duration = microtime(true) - $startTime;
                        $result = [
                            'status' => $response->status(),
                            'duration' => round($duration, 3),
                            'response_data' => $this->getResponseData($response),
                        ];
                        $this->logCheckResult($url, $source, $result);
                        $results[$url] = $result;
                    },
                    function ($e) use ($url, $source, $startTime, &$results) {
                        $duration = microtime(true) - $startTime;
                        $result = [
                            'status' => 'error',
                            'duration' => round($duration, 3),
                            'message' => $e->getMessage(),
                        ];
                        $this->logCheckResult($url, $source, $result);
                        $results[$url] = $result;
                    }
                );
        }

        // Esperar a que todas las verificaciones terminen
        Http::pool(fn () => $promises);

        return $results;
    }

    /**
     * Verificar enlaces que necesitan ser revisados
     */
    public function checkPendingLinks(): array
    {
        $links = BrokenLink::unfixed()
            ->needsCheck()
            ->get();

        $urls = $links->pluck('source', 'url')->toArray();
        return $this->checkUrls($urls);
    }

    /**
     * Registrar el resultado de una verificación
     */
    protected function logCheckResult(string $url, ?string $source, array $result): void
    {
        $brokenLink = BrokenLink::firstOrNew(['url' => $url]);
        
        if (!$brokenLink->exists) {
            $brokenLink->fill([
                'source' => $source,
                'status' => $result['status'],
                'check_count' => 1,
                'last_checked_at' => now(),
            ])->save();
        } else {
            $brokenLink->incrementCheckCount();
            $brokenLink->status = $result['status'];
            $brokenLink->save();
        }

        // Registrar el historial
        $brokenLink->addCheckHistory(
            $result['status'],
            $result['response_data'] ?? null,
            $result['duration'] ?? null
        );
    }

    /**
     * Verificar si un URL está excluido
     */
    protected function isExcluded(string $url): bool
    {
        foreach ($this->exclusions as $exclusion) {
            if ($exclusion->matches($url)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtener headers para la petición
     */
    protected function getRequestHeaders(): array
    {
        return [
            'User-Agent' => 'GesVitalPro Link Checker/1.0',
            'Accept' => '*/*',
            'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
        ];
    }

    /**
     * Obtener datos relevantes de la respuesta
     */
    protected function getResponseData($response): array
    {
        return [
            'headers' => $response->headers(),
            'redirect_chain' => $response->handlerStats()['redirect_count'] ?? 0,
            'total_time' => $response->handlerStats()['total_time'] ?? null,
            'content_type' => $response->header('Content-Type'),
            'content_length' => $response->header('Content-Length'),
        ];
    }

    /**
     * Limpiar registros antiguos
     */
    public function cleanOldRecords(int $days = 30): int
    {
        $date = Carbon::now()->subDays($days);
        
        // Eliminar historiales antiguos
        $deletedHistories = LinkCheckHistory::where('checked_at', '<', $date)->delete();
        
        // Eliminar enlaces arreglados antiguos
        $deletedLinks = BrokenLink::where('fixed_at', '<', $date)
            ->where('is_fixed', true)
            ->delete();

        return $deletedHistories + $deletedLinks;
    }
}
