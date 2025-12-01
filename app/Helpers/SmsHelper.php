<?php

namespace App\Helpers;

class SmsHelper
{
    public static function send($phone, $message)
    {
        // Trim to avoid hidden spaces in env (e.g., sender id with leading space)
        $sid = trim((string) config('services.sms.senderid'));
        $token = trim((string) config('services.sms.token'));
        $key = trim((string) config('services.sms.key'));
        $url = trim((string) config('services.sms.url'));

        $postData = [
            'source_addr' => $sid,
            'encoding' => 0,
            'schedule_time' => '',
            'message' => $message,
            'recipients' => [
                [
                    'recipient_id' => '1',
                    'dest_addr' => $phone
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($ch, [
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => [
                'Authorization:Basic ' . base64_encode("$key:$token"),
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($postData)
        ]);

        $response = curl_exec($ch);

        // Optional: handle error or return response
        if (curl_errno($ch)) {
            return curl_error($ch);
        }

        return $response;
    }
}
