<?php
// src/Controller/RepairServicesUsageController.php
declare(strict_types=1);

namespace App\Controller;

class RepairServicesUsageController extends AppController
{
    private $serviceMap = [
        "Software/OS Issue" => [
            "Firmware Reinstall",
            "OS Update",
            "Factory Reset",
            "System Reflash"
        ],
        "Water Damage - Inspect All Components" => [
            "Ultrasonic Cleaning",
            "Mainboard Cleaning",
            "Full Diagnostic Test"
        ],
        "Display Issue" => [
            "Screen Replacement Service"
        ],
        "Power IC Issue" => [
            "Mainboard Repair"
        ],
        "Battery Issue" => [
            "Battery Health Check",
            "Battery Replacement Service"
        ],
        "Charging Port Issue" => [
            "Charging Port Cleaning",
            "Charging Port Replacement Service"
        ],
        "Speaker Issue" => [
            "Speaker Diagnostic",
            "Speaker Replacement Service"
        ],
        "Microphone Issue" => [
            "Microphone Diagnostic",
            "Microphone Replacement Service"
        ],
        "Touch Controller Issue" => [
            "Digitizer Replacement Service",
            "Touch Calibration"
        ],
        "Baseband Issue" => [
            "Signal Diagnostic",
            "Baseband Repair"
        ],
        "Antenna Issue" => [
            "Antenna Repair Service"
        ],
    ];

    /**
     * Get suggested services by diagnosis name
     */
    public function getByDiagnosis()
    {
        $this->request->allowMethod(['post']);

        $diagnosis = $this->request->getData('diagnosis');

        if (!$diagnosis) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => 'Diagnosis is required',
                ]));
        }

        $diagnoses   = array_map('trim', explode('+', $diagnosis));
        $allServices = [];

        foreach ($diagnoses as $diag) {
            if (isset($this->serviceMap[$diag])) {
                $allServices = array_merge($allServices, $this->serviceMap[$diag]);
            }
        }

        $allServices = array_values(array_unique($allServices));

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success'  => true,
                'services' => $allServices,
            ]));
    }

    /**
     * Get services performed for a device
     */
    public function getUsed()
    {
        $this->request->allowMethod(['post']);

        $deviceId = $this->request->getData('device_id');

        if (!$deviceId) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => 'Device ID is required',
                ]));
        }

        $usage = $this->fetchTable('RepairServicesUsage')
            ->find()
            ->contain(['Services'])
            ->where(['RepairServicesUsage.device_id' => $deviceId])
            ->all();

        $usedServices = [];
        foreach ($usage as $item) {
            $usedServices[] = [
                'id'           => $item->id,
                'service_name' => $item->service->service_name,
                'category'     => $item->service->category ?? '',
                'price'        => $item->service->price ?? null,
            ];
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success'      => true,
                'used_services' => $usedServices,
            ]));
    }

    /**
     * Log a service as performed on a device.
     * Looks up the service by name, then records the usage.
     */
    public function add()
    {
        $this->request->allowMethod(['post']);

        $deviceId    = $this->request->getData('device_id');
        $serviceName = trim((string)($this->request->getData('service_name') ?? ''));

        if (!$deviceId || !$serviceName) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => 'device_id and service_name are required',
                ]));
        }

        // Look up the service record
        $service = $this->fetchTable('Services')
            ->find()
            ->where(['service_name' => $serviceName])
            ->first();

        if (!$service) {
            // Service doesn't exist in the catalogue — skip silently or return error
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => "Service '{$serviceName}' not found in catalogue. Please add it first.",
                ]));
        }

        // Prevent duplicate entries for the same device + service
        $existing = $this->fetchTable('RepairServicesUsage')
            ->find()
            ->where([
                'device_id'  => $deviceId,
                'service_id' => $service->id,
            ])
            ->first();

        if ($existing) {
            // Already logged — treat as success so the UI doesn't show an error
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Service already logged for this device',
                ]));
        }

        $usage = $this->fetchTable('RepairServicesUsage')->newEntity([
            'device_id'  => $deviceId,
            'service_id' => $service->id,
        ]);

        if ($this->fetchTable('RepairServicesUsage')->save($usage)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Service logged successfully',
                ]));
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => false,
                'error'   => 'Failed to save service usage',
            ]));
    }
}