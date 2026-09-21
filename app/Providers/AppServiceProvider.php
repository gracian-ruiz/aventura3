<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Alquiler;
use App\Models\WhatsAppMessage;

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
    public function boot()
    {
        View::composer('*', function ($view) {
            $notificaciones = Alquiler::where('notificacion', true)->count();
            $hasUnreadWhatsApp = false;

            if (Schema::hasTable('whatsapp_messages')) {
                $hasUnreadWhatsApp = WhatsAppMessage::query()
                    ->where('direction', 'inbound')
                    ->where(function ($query) {
                        $query->where('is_read', false)
                            ->orWhereNull('is_read')
                            ->orWhereNull('read_at');
                    })
                    ->exists();
            }


            $view->with('notificaciones', $notificaciones);
            $view->with('hasUnreadWhatsApp', $hasUnreadWhatsApp);
        });
    }
}
