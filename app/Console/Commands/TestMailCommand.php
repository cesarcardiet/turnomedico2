<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email}';
    protected $description = 'Send a test email using current SMTP settings';

    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Sending test email to: {$email}");

        try {
            Mail::raw('Este es un correo de prueba de TurnoMédico para verificar la configuración SMTP.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Prueba de Conexión SMTP - TurnoMédico');
            });

            $this->info('¡Correo enviado con éxito!');
        } catch (\Exception $e) {
            $this->error('Falló el envío del correo: ' . $e->getMessage());
        }
    }
}
