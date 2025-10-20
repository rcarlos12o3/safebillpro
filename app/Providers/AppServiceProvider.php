<?php

namespace App\Providers;

use App\Models\Tenant\Document;
use App\Observers\DocumentObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Modules\LevelAccess\Helpers\SessionLifetimeHelper;


class AppServiceProvider extends ServiceProvider
{
	public function boot()
	{
        SessionLifetimeHelper::setTenantSessionLifetime();

		if (config('tenant.force_https')) {
			URL::forceScheme('https');
		}
		Document::observe(DocumentObserver::class);
	}

	public function register()
	{
		// Registrar Debugbar solo en ambientes de desarrollo (local, testing)
		if ($this->app->environment('local', 'testing')) {
			if (class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
				$this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
			}
		}
	}
}
