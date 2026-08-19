<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    public function send(string $phoneNumber, string $message)
    {
        return Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])
            ->asForm()
            ->post('https://api.fonnte.com/send', [
                'target' => $phoneNumber,
                'message' => $message,
                'countryCode' => '62',
            ]);
    }
}