<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BrokenLinksNotification;

class CheckLinksCommand extends Command
{
    protected $signature = 'links:check {--notify : Send notifications for broken links}';
    protected $description = 'Check for broken links in the application';

    protected $brokenLinks = [];
    protected $checkedUrls = [];
    protected $totalChecked = 0;
    protected $totalBroken = 0;

    public function handle()
    {
        $this->info('Starting link check...');
        $this->newLine();

        // Verificar enlaces en la base de datos
        $this->checkDatabaseLinks();

        // Verificar enlaces en archivos de vista
        $this->checkViewFiles();

        // Verificar enlaces en archivos públicos
        $this->checkPublicFiles();

        $this->displayResults();

        if ($this->option('notify') && !empty($this->brokenLinks)) {
            $this->sendNotifications();
        }

        return empty($this->brokenLinks) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function checkDatabaseLinks()
    {
        $this->info('Checking database links...');

        // Lista de tablas y columnas a verificar
        $tablesToCheck = [
            'users' => ['avatar'],
            'pacientes' => ['foto', 'documentos'],
            'cirugias' => ['documentos', 'imagenes'],
            'reportes' => ['archivos'],
        ];

        foreach ($tablesToCheck as $table => $columns) {
            if (Schema::hasTable($table)) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $this->checkTableColumn($table, $column);
                    }
                }
            }
        }
    }

    protected function checkTableColumn($table, $column)
    {
        $urls = DB::table($table)
                  ->whereNotNull($column)
                  ->pluck($column)
                  ->filter()
                  ->toArray();

        foreach ($urls as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $this->checkUrl($url, "Database: {$table}.{$column}");
            }
        }
    }

    protected function checkViewFiles()
    {
        $this->info('Checking view files...');

        $viewPath = resource_path('views');
        $pattern = '/href=["\']([^"\']+)["\']|src=["\']([^"\']+)["\']|url\(["\']?([^"\'\)]+)["\']?\)/i';

        $this->findAndCheckUrls($viewPath, $pattern, '*.blade.php');
    }

    protected function checkPublicFiles()
    {
        $this->info('Checking public files...');

        $publicPath = public_path();
        $pattern = '/href=["\']([^"\']+)["\']|src=["\']([^"\']+)["\']|url\(["\']?([^"\'\)]+)["\']?\)/i';

        $this->findAndCheckUrls($publicPath, $pattern, '*.{html,css,js}');
    }

    protected function findAndCheckUrls($path, $pattern, $filePattern)
    {
        $files = glob($path . '/**/' . $filePattern, GLOB_BRACE);

        foreach ($files as $file) {
            $content = file_get_contents($file);
            preg_match_all($pattern, $content, $matches);

            $urls = array_merge(...array_filter($matches));
            foreach ($urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $this->checkUrl($url, "File: " . str_replace(base_path(), '', $file));
                }
            }
        }
    }

    protected function checkUrl($url, $source)
    {
        if (isset($this->checkedUrls[$url])) {
            return;
        }

        $this->totalChecked++;
        $this->checkedUrls[$url] = true;

        try {
            $response = Http::timeout(10)->head($url);
            
            if ($response->failed()) {
                $this->addBrokenLink($url, $source, $response->status());
            }
        } catch (\Exception $e) {
            $this->addBrokenLink($url, $source, 'Error: ' . $e->getMessage());
        }

        // Mostrar progreso
        if ($this->totalChecked % 10 === 0) {
            $this->info("Checked {$this->totalChecked} URLs...");
        }
    }

    protected function addBrokenLink($url, $source, $status)
    {
        $this->totalBroken++;
        $this->brokenLinks[] = [
            'url' => $url,
            'source' => $source,
            'status' => $status,
            'checked_at' => now(),
        ];

        // Registrar en la base de datos
        try {
            DB::table('broken_links')->insert([
                'url' => $url,
                'source' => $source,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Error registering broken link: " . $e->getMessage());
        }
    }

    protected function displayResults()
    {
        $this->newLine();
        $this->info("Link check completed:");
        $this->line("- Total URLs checked: {$this->totalChecked}");
        $this->line("- Broken links found: {$this->totalBroken}");

        if (!empty($this->brokenLinks)) {
            $this->newLine();
            $this->error('Broken Links:');
            
            foreach ($this->brokenLinks as $link) {
                $this->line("URL: {$link['url']}");
                $this->line("Source: {$link['source']}");
                $this->line("Status: {$link['status']}");
                $this->line(str_repeat('-', 50));
            }
        }
    }

    protected function sendNotifications()
    {
        // Notificar a los administradores
        $admins = \App\Models\User::role('admin')->get();
        
        Notification::send($admins, new BrokenLinksNotification($this->brokenLinks));
        
        $this->info('Notifications sent to administrators.');
    }
}
