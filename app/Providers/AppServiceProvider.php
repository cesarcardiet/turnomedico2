<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Dynamic Mail Settings
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $mailSettings = \App\Models\Setting::getGroup('mail');

            if (!empty($mailSettings)) {
                config([
                    'mail.mailers.smtp.host' => $mailSettings['mail_host'] ?? config('mail.mailers.smtp.host'),
                    'mail.mailers.smtp.port' => $mailSettings['mail_port'] ?? config('mail.mailers.smtp.port'),
                    'mail.mailers.smtp.username' => $mailSettings['mail_username'] ?? config('mail.mailers.smtp.username'),
                    'mail.mailers.smtp.password' => $mailSettings['mail_password'] ?? config('mail.mailers.smtp.password'),
                    'mail.mailers.smtp.encryption' => $mailSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'),
                    'mail.from.address' => $mailSettings['mail_from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $mailSettings['mail_from_name'] ?? config('mail.from.name'),
                ]);
            }
        }
    }
}
