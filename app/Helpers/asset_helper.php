<?php

if (! function_exists('should_load_asset')) {
    /**
     * Determina si un asset debe ser cargado basado en la ruta actual
     */
    function should_load_asset(string $asset, array $routes = []): bool
    {
        if (empty($routes)) {
            return true;
        }

        $currentRoute = request()->route()->getName();

        return in_array($currentRoute, $routes);
    }
}
