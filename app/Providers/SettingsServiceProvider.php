<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use App\Settings\SiteScriptSettings;
use App\Settings\SiteSeoSettings;
use App\Settings\SiteSettings;
use App\Settings\SiteSocialSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('*', function ($view) {
            $view->with([
                'generalSettings' => app(GeneralSettings::class),
                'siteSettings' => app(SiteSettings::class),
                'seoSettings' => app(SiteSeoSettings::class),
                'siteSocialSettings' => app(SiteSocialSettings::class),
                'scriptSettings' => app(SiteScriptSettings::class),
            ]);
        });
    }
}
