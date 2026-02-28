<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Client;
use Cake\Http\Response;

class AiController extends AppController
{
    public function diagnose(): Response
    {
        $this->request->allowMethod(['post']);

        $data = $this->request->getData();
        $description = (string)($data['description'] ?? '');

        if (!$description) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'No description provided'
                ]));
        }

        $http = new Client();

        try {

            $response = $http->post(
                'http://127.0.0.1:8000/diagnose',
                json_encode(['description' => $description]),
                [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ]
                ]
            );

            if (!$response->isOk()) {
                throw new \Exception('AI service error');
            }

            $json = $response->getJson();

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => $json['success'] ?? false,
                    'mode' => $json['mode'] ?? null,
                    'detected_symptoms' => $json['detected_symptoms'] ?? [],
                    'diagnosis' => $json['diagnosis'] ?? 'Unknown',
                    'confidence' => $json['confidence'],
                    'replacement_parts' => $json['replacement_parts'] ?? [],
                    'top2' => $json['top2'] ?? [],
                    'rule_suggestion' => $json['rule_suggestion'] ?? null
                ]));

        } catch (\Exception $e) {

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Cannot connect to AI server'
                ]));
        }
    }
}