<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\Client;

class DiagnosisService
{
    private string $apiUrl = 'http://127.0.0.1:8000/diagnose';

    public function diagnose(string $description): array
    {
        $http = new Client();

        try {
            $response = $http->post($this->apiUrl, [
                'description' => $description
            ]);

            if (!$response->isOk()) {
                return [
                    'success' => false,
                    'error' => 'AI service unavailable'
                ];
            }

            return [
                'success' => true,
                'diagnosis' => $response->getJson()['prediction'] ?? 'Unknown',
                'confidence' => $response->getJson()['confidence'] ?? 0,
                'replacement_parts' => $response->getJson()['replacement_parts'] ?? []
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Cannot connect to AI server'
            ];
        }
    }
}