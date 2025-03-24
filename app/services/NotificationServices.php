<?php

namespace App\services;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationServices {
    public static function sendNotification($token, $message) {
        $factory = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
        $firebaseMessaging = $factory->createMessaging();

        // Prepare the Firebase message
        $message = CloudMessage::fromArray([
            'token' => $token,
            'notification' => [
                'title' => 'Commission Earned',
                'body' => $message,
            ],
        ]);
        $firebaseMessaging->send($message);
    }
}
