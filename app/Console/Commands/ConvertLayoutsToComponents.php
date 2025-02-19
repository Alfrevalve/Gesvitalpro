<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConvertLayoutsToComponents extends Command
{
    protected $signature = 'layouts:convert';
    protected $description = 'Convert @extends layouts to x-app-layout components';

    public function handle()
    {
        $viewPath = resource_path('views');
        $files = File::allFiles($viewPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            
            if (str_contains($content, "@extends('layouts.app')")) {
                $this->info("Converting {$file->getRelativePathname()}");
                
                // Replace the layout syntax
                $content = preg_replace(
                    "/@extends\('layouts\.app'\)(.*?)@section\('content'\)/s",
                    "<x-app-layout>",
                    $content
                );
                
                $content = preg_replace(
                    "/@endsection(.*?)@push\('styles'\)(.*?)@endpush/s",
                    "</x-app-layout>\n\n<style>$2</style>",
                    $content
                );
                
                // If there's no styles section
                $content = preg_replace(
                    "/@endsection/s",
                    "</x-app-layout>",
                    $content
                );

                file_put_contents($file->getPathname(), $content);
            }
        }

        $this->info('All layouts have been converted to components.');
    }
}
