<?php

namespace App\Filament\Themes;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Theme;

class SneatTheme extends Theme
{
    public static function getName(): string
    {
        return 'sneat';
    }

    public static function getAssets(): array
    {
        return [
            // CSS Assets
            Css::make('sneat-core', asset('assets/vendor/css/core.css')),
            Css::make('sneat-theme', asset('assets/vendor/css/theme-default.css')),
            Css::make('sneat-demo', asset('assets/css/demo.css')),
            Css::make('sneat-perfect-scrollbar', asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')),

            // JavaScript Assets
            Js::make('sneat-helpers', asset('assets/vendor/js/helpers.js')),
            Js::make('sneat-config', asset('assets/js/config.js')),
            Js::make('sneat-jquery', asset('build/assets/jquery-HZPLgPdu.js')),
            Js::make('sneat-popper', asset('build/assets/popper-qgXTp54-.js')),
            Js::make('sneat-bootstrap', asset('build/assets/bootstrap-Okd2Wccf.js')),
            Js::make('sneat-perfect-scrollbar', asset('build/assets/perfect-scrollbar-gmvmg5IH.js')),
            Js::make('sneat-menu', asset('build/assets/menu-BQx7Hjbx.js')),
            Js::make('sneat-main', asset('build/assets/main-CWila6Zz.js')),
        ];
    }

    public static function getThemeColor(): string
    {
        return '#696cff';
    }
}
