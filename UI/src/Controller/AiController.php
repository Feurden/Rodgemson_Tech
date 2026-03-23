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
                    'symptom_parts' => $json['symptom_parts'] ?? (object)[],
                    'symptom_diagnoses' => $json['symptom_diagnoses'] ?? (object)[],
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

    public function checkFeedback(): Response
    {
        $this->request->allowMethod(['post']);

        $data  = $this->request->getData();
        $jobId = $data['job_id'] ?? null;

        if (!$jobId) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode(['exists' => false]));
        }

        $table    = $this->getTableLocator()->get('RepairDiagnoses');
        $existing = $table->find()->where(['job_id' => $jobId])->first();

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['exists' => $existing !== null]));
    }

    public function saveFeedback(): Response
    {
        $this->request->allowMethod(['post']);

        $data      = $this->request->getData();
        $jobId     = $data['job_id'] ?? null;
        $isCorrect = $data['diagnosis_correct'] ?? null;

        if (!$jobId || $isCorrect === null) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => 'Missing required fields'
                ]));
        }

        try {
            $table    = $this->getTableLocator()->get('RepairDiagnoses');

            // Check for existing feedback — update instead of duplicate insert
            $existing = $table->find()->where(['job_id' => $jobId])->first();
            $entity   = $existing ?? $table->newEmptyEntity();

            $entity = $table->patchEntity($entity, [
                'job_id'           => $jobId,
                'ai_diagnosis'     => $data['ai_diagnosis']     ?? null,
                'ai_confidence'    => $data['ai_confidence']    ?? null,
                'actual_diagnosis' => $data['actual_diagnosis'] ?? null,
                'actual_root_cause'=> $data['root_cause']       ?? null,
                'parts_replaced'   => $data['parts_replaced']   ?? null,
                'diagnosis_correct'=> $isCorrect,
                'technician_notes' => $data['notes']            ?? null,
                'completed_at'     => date('Y-m-d H:i:s'),
            ]);

            if ($table->save($entity)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'updated' => $existing !== null,
                    ]));
            }

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => 'Failed to save feedback'
                ]));

        } catch (\Exception $e) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => $e->getMessage()
                ]));
        }
    }
}