<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    /**
     * Send a push notification via FCM HTTP v1 API.
     */
    public static function sendNotification($token, $title, $body, $data = [])
    {
        $serviceAccount = json_decode(Setting::get('firebase_service_account'), true);

        if (!$serviceAccount) {
            Log::error('Firebase Service Account not configured.');
            return false;
        }

        $projectId = $serviceAccount['project_id'];
        $accessToken = self::getAccessToken($serviceAccount);

        if (!$accessToken) {
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $response = Http::withToken($accessToken)->post($url, [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $data),
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high'
                    ],
                    'notification' => [
                        'icon' => Setting::get('site_logo') ? asset('storage/' . Setting::get('site_logo')) : null,
                    ]
                ]
            ]
        ]);

        if ($response->failed()) {
            Log::error('Firebase Notification failed: ' . $response->body());
            return false;
        }

        return true;
    }

    /**
     * Generate OAuth2 Access Token for Firebase using Service Account.
     * Note: This usually requires google/auth library. 
     * If not present, we might need to implement the JWT manually or instruct the user to install it.
     */
    protected static function getAccessToken($serviceAccount)
    {
        // Simple manual implementation of JWT for Firebase FCM v1
        // Usually: composer require google/auth
        // For now, we'll log that it needs the library if it fails.

        try {
            // This is a placeholder for the actual JWT signing logic which requires openssl and base64 encoding.
            // In a production environment, 'google/auth' is highly recommended.
            if (!class_exists('\Google\Auth\Credentials\ServiceAccountCredentials')) {
                Log::warning('Google Auth library not found. Run: composer require google/auth');
                return null;
            }

            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials(
                'https://www.googleapis.com/auth/cloud-platform',
                $serviceAccount
            );

            return $credentials->fetchAuthToken()['access_token'];
        } catch (\Exception $e) {
            Log::error('Firebase Token Generation failed: ' . $e->getMessage());
            return null;
        }
    }
}
