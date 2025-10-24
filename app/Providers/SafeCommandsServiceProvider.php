<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Service Provider para prevenir comandos destructivos en producción
 *
 * Este provider bloquea comandos peligrosos que pueden destruir datos:
 * - migrate:fresh (borra todas las tablas)
 * - migrate:refresh (borra y recrea)
 * - migrate:reset (borra todas las migraciones)
 * - db:wipe (limpia la base de datos)
 */
class SafeCommandsServiceProvider extends ServiceProvider
{
    /**
     * Comandos completamente bloqueados en TODOS los ambientes
     */
    protected $blockedCommands = [
        'tenancy:migrate:fresh',
        'tenancy:migrate:refresh',
        'tenancy:migrate:reset',
    ];

    /**
     * Comandos bloqueados SOLO en producción
     */
    protected $productionBlockedCommands = [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'db:wipe',
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Interceptar comandos antes de ejecutarse
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            $commandName = $event->command;

            // Bloquear comandos peligrosos SIEMPRE (en todos los ambientes)
            if ($this->isCommandBlocked($commandName)) {
                $this->blockCommand($commandName, 'bloqueado en TODOS los ambientes');
            }

            // Bloquear comandos peligrosos SOLO en producción
            if (app()->environment('production') && $this->isProductionBlocked($commandName)) {
                $this->blockCommand($commandName, 'bloqueado en PRODUCCIÓN');
            }
        });
    }

    /**
     * Verificar si el comando está bloqueado globalmente
     */
    protected function isCommandBlocked($commandName)
    {
        foreach ($this->blockedCommands as $blocked) {
            if (str_contains($commandName, $blocked)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si el comando está bloqueado en producción
     */
    protected function isProductionBlocked($commandName)
    {
        foreach ($this->productionBlockedCommands as $blocked) {
            if (str_contains($commandName, $blocked)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Bloquear el comando y terminar la ejecución
     */
    protected function blockCommand($commandName, $reason)
    {
        $message = "\n";
        $message .= "⛔️  ==========================================\n";
        $message .= "⛔️  COMANDO BLOQUEADO POR SEGURIDAD\n";
        $message .= "⛔️  ==========================================\n";
        $message .= "\n";
        $message .= "Comando: {$commandName}\n";
        $message .= "Razón: Este comando está {$reason}\n";
        $message .= "Ambiente: " . app()->environment() . "\n";
        $message .= "\n";
        $message .= "Este comando puede DESTRUIR TODOS LOS DATOS de los tenants.\n";
        $message .= "\n";
        $message .= "Si realmente necesitas ejecutarlo:\n";
        $message .= "1. Realiza un backup completo\n";
        $message .= "2. Verifica que estás en ambiente de desarrollo\n";
        $message .= "3. Comenta temporalmente esta protección en:\n";
        $message .= "   app/Providers/SafeCommandsServiceProvider.php\n";
        $message .= "\n";
        $message .= "Comandos SEGUROS que puedes usar:\n";
        $message .= "  - php artisan tenancy:migrate           (ejecuta migraciones nuevas)\n";
        $message .= "  - php artisan migrate                   (ejecuta migraciones nuevas)\n";
        $message .= "  - php artisan tenancy:migrate:rollback  (revierte última migración)\n";
        $message .= "\n";
        $message .= "⛔️  ==========================================\n";

        // Imprimir en consola
        echo $message;

        // Registrar en log
        Log::warning('Comando destructivo bloqueado', [
            'command' => $commandName,
            'environment' => app()->environment(),
            'user' => get_current_user(),
            'time' => now()->toDateTimeString(),
        ]);

        // Terminar la ejecución
        exit(1);
    }
}
