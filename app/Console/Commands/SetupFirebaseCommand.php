<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class SetupFirebaseCommand extends Command
{
    protected $signature = 'firebase:setup';
    protected $description = 'Inject Firebase credentials from user into the settings table';

    public function handle()
    {
        $this->info('Iniciando configuración de Firebase...');

        // Web Config
        Setting::set('firebase_api_key', 'AIzaSyCt_ZnTdWNbqsH_Dksqg_UGPDDxL2RL59o', 'firebase');
        Setting::set('firebase_auth_domain', 'turnomedicords.firebaseapp.com', 'firebase');
        Setting::set('firebase_project_id', 'turnomedicords', 'firebase');
        Setting::set('firebase_storage_bucket', 'turnomedicords.firebasestorage.app', 'firebase');
        Setting::set('firebase_messaging_sender_id', '495355940071', 'firebase');
        Setting::set('firebase_app_id', '1:495355940071:web:f183ae21e6d1d10fb8a642', 'firebase');
        Setting::set('firebase_measurement_id', 'G-53PPMXW0KW', 'firebase');

        // Service Account
        $serviceAccount = [
            "type" => "service_account",
            "project_id" => "turnomedicords",
            "private_key_id" => "f77401e5867a4154a8334cbe2c6e65884b840abb",
            "private_key" => "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDPMSWGfy/Tey38\nC/Dcc/oLTPvK3bHjJBtqKRBYQNcql+GYabQiHeUup74z6ZJlalWxEXTBAF0tkUrg\nR2LIwJd1QKj7AzvicANDfpStqk6nbs8AFXh8Vke2fky2SSFGOYL7pn8RYSRta68V\nGwimkBrogNuciOlEXBQdsazPc9TIDCax74dglvQIcT1o7W1ybN+hynCi00MiWJrx\nVXJvkjw+YnqCUOCW6COMSdDxECdaIHOK4qUKhkpllnqUs70l2QtIpqneU2DFnzGA\n62wSaeZRM2nhGilxhUDRheJxNUwPF/eQQLDlYQFa8GeOakOL7eWGZGF6hkqLzNfM\nAaMAuLlLAgMBAAECggEABzbk4ZwIJEsDvOUFjAeVy1Lw6Y1ypkAZnEa1xPztARSs\nzEvR8+YqlsdMfi5B5LohJbs/34CNvgpzGwAhVnAuVezcdXEVYrsXwf/kWW+2sWnQ\nV2Zn/ZxbfuVC8o31VKaoAu83OXmydQTs4cVBNduKtFHYU8kIplK2eJo5NDgKTrTd\njM4Jtg2pw1ZU1Aaip1yMBiiraG6jAo7yZ8iqozv+hacZpAFGZISZVDhpYbtts+EH\nds0/CPbYrHelffLTCBSB+NI+IvIMYwi2UFJRJ+29ePEJF9TQFy4ubAZZAp7mwy0J\n7dWOwipo2+MkKf1W0TGpGMpng1FsTOiVh439BXI6QQKBgQDyVn1nmjGKa7CpyKuI\nEydLRyug0h/fLY+Bc9uh2E9a2Lb2DDqjn33YHnrdHdlKSkQMJehanVWH+eQArKpy\ndgM1lVUrteE/ET8isgIhr643ZQiJA7eGuZPeFVR64w68ZBC2WhwdUX23jQdaC4Md\nn3Ek5loWNCeNOzb5nL4PCM4VGwKBgQDa32scZjbD60Tu5v4PxwSVLBldCpTwf6wM\nZR/MHVxCz5zJ4+w8w+yq6pWPSyiRTe2Hf19/Wltt6YegiuO2gTgZwhoxEVDGtysu\nU+OEBRKUXvw0534EUWC3ErYcu7Tgp69netskjFpPmsFVoT91EI5x0pQFFHTFIV6g\nBSM0c9mfkQKBgGHUqrGdXOyNhvczzJOVb+KC68jzquw0/176P6s5oOeC33G7BB0z\n8ODDhUQonaTREGF7GC8knvfS2Mmw6upkW+1QyN5pgXItazh9dkDVJFa2kdiGSJu5\n5UjdgYpOiY9iMiD7hagMUt07sMEYLqlRyaJk3+9gxKrOQeZI8a0uF8wLAoGATSSw\nAPt51AYJbtbVt0PjNpyrd+Kx6i5lupyt32h9y0KXtYTzD9vSf89c1XudGdHIpZhc\nVWvIi+3iktBBQGM9Hb8PMjozKUcIHjUNHMwY51ivNgpdnTH9j6k1rNzv/Lq9lRB1\nSuV5M1ONTxwdXKpwOmgJKd0y5wRC8M2+wc8PZjECgYEAiULoRcdeOkwy5wBA20xg\n5dF8r62OXFDajDWvWZfPGrzlfZoqR3/MAMRcsiP6Yiiau3e26+otvqfWgjqsMwhy\n/yJjZa0WM0SmTU/dqc8rn+jOJlpbdop6jgHjOlvf0OKpVloOqH2jJ6TZaf68Q/wG\no0jco64tCCdWNH4ciOXc2dI=\n-----END PRIVATE KEY-----\n",
            "client_email" => "firebase-adminsdk-fbsvc@turnomedicords.iam.gserviceaccount.com",
            "client_id" => "105369522368262687051",
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
            "client_x509_cert_url" => "https://www.googleapi.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40turnomedicords.iam.gserviceaccount.com",
            "universe_domain" => "googleapis.com"
        ];
        Setting::set('firebase_service_account', json_encode($serviceAccount), 'firebase');

        $this->info('¡Configuración de Firebase guardada correctamente!');
    }
}
