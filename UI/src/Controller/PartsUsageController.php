<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;

class PartsUsageController extends AppController
{
    // Define the complete replacement map matching main.py
    private $replacementMap = [
        "Speaker Issue" => [
            "Speaker Module",
            "Ear Speaker",
            "Audio IC",
            "Speaker Flex Cable",
            "Audio Codec IC"
        ],
        "Microphone Issue" => [
            "Microphone Module",
            "Audio IC",
            "Charging Flex Cable",
            "Sub Board",
            "Microphone Mesh"
        ],
        "Touch Controller Issue" => [
            "Touch Controller IC",
            "Digitizer",
            "Touch Flex Cable",
            "LCD Screen Assembly",
            "Power Management IC"
        ],
        "Display Issue" => [
            "LCD/OLED Screen Assembly",
            "Front Glass Digitizer",
            "Screen Frame",
            "Adhesive Seal Kit",
            "Screen Replacement Service"
        ],
        "Display IC Issue" => [
            "Display Driver IC",
            "Backlight IC",
            "LCD Screen",
            "Display Flex Cable",
            "GPU IC"
        ],
        "Battery Issue" => [
            "Battery",
            "Battery Connector",
            "Power IC",
            "Charging IC",
            "Charging Flex Cable"
        ],
        "Charging Port Issue" => [
            "Charging Port",
            "USB Connector",
            "Charging Flex Cable",
            "Charging IC",
            "Power IC"
        ],
        "Power IC Issue" => [
            "Power Management IC",
            "Battery Connector",
            "Charging IC",
            "Mainboard Repair"
        ],
        "Baseband Issue" => [
            "Baseband IC",
            "RF IC",
            "Antenna Module",
            "SIM IC"
        ],
        "Antenna Issue" => [
            "Antenna Cable",
            "Antenna Module",
            "RF IC",
            "Signal Booster IC"
        ],
        "Software/OS Issue" => [
            "Firmware Reinstall",
            "OS Update",
            "Factory Reset",
            "System Reflash"
        ],
        "Water Damage - Inspect All Components" => [
            "Ultrasonic Cleaning",
            "Mainboard Cleaning",
            "Connector Replacement",
            "Battery Replacement",
            "Full Diagnostic Test"
        ]
    ];

    // Map part names to categories
    private $partCategories = [
        'Speaker Module' => 'Audio',
        'Ear Speaker' => 'Audio',
        'Audio IC' => 'Audio',
        'Speaker Flex Cable' => 'Flex/Connectors',
        'Audio Codec IC' => 'Audio',
        'Microphone Module' => 'Audio',
        'Charging Flex Cable' => 'Flex/Connectors',
        'Sub Board' => 'Mainboard',
        'Microphone Mesh' => 'Accessories',
        'Touch Controller IC' => 'Display',
        'Digitizer' => 'Screen Part',
        'Touch Flex Cable' => 'Flex/Connectors',
        'LCD Screen Assembly' => 'Screen Part',
        'Power Management IC' => 'Power',
        'LCD/OLED Screen Assembly' => 'Screen Part',
        'Front Glass Digitizer' => 'Screen Part',
        'Screen Frame' => 'Screen Part',
        'Adhesive Seal Kit' => 'Accessories',
        'Screen Replacement Service' => 'Service',
        'Display Driver IC' => 'Display',
        'Backlight IC' => 'Display',
        'LCD Screen' => 'Screen Part',
        'Display Flex Cable' => 'Flex/Connectors',
        'GPU IC' => 'Mainboard',
        'Battery' => 'Battery',
        'Battery Connector' => 'Battery',
        'Power IC' => 'Power',
        'Charging IC' => 'Power',
        'Charging Port' => 'Charging',
        'USB Connector' => 'Charging',
        'Mainboard Repair' => 'Service',
        'Baseband IC' => 'Connectivity',
        'RF IC' => 'Connectivity',
        'Antenna Module' => 'Connectivity',
        'SIM IC' => 'Connectivity',
        'Antenna Cable' => 'Connectivity',
        'Signal Booster IC' => 'Connectivity',
        'Firmware Reinstall' => 'Software Service',
        'OS Update' => 'Software Service',
        'Factory Reset' => 'Software Service',
        'System Reflash' => 'Software Service',
        'Ultrasonic Cleaning' => 'Service',
        'Mainboard Cleaning' => 'Service',
        'Connector Replacement' => 'Service',
        'Battery Replacement' => 'Service',
        'Full Diagnostic Test' => 'Service'
    ];

    /**
     * Get parts by diagnosis name (for the parts selection modal)
     * This returns ALL parts from the diagnosis map, not just those in inventory
     */
    public function getByDiagnosis()
    {
        $this->request->allowMethod(['post']);
        
        $diagnosis = $this->request->getData('diagnosis');
        
        if (!$diagnosis) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Diagnosis is required'
                ]));
        }
        
        // Handle combined diagnoses (e.g., "Battery Issue + Touch Controller Issue")
        $diagnoses = array_map('trim', explode('+', $diagnosis));
        $allPartNames = [];
        
        foreach ($diagnoses as $diag) {
            if (isset($this->replacementMap[$diag])) {
                $allPartNames = array_merge($allPartNames, $this->replacementMap[$diag]);
            }
        }
        
        // Remove duplicates
        $allPartNames = array_unique($allPartNames);
        
        $result = [];
        
        foreach ($allPartNames as $partName) {
            // Try to find the part in the database
            $part = $this->fetchTable('Parts')
                ->find()
                ->where(['part_name' => $partName])
                ->first();
            
            if ($part) {
                // Part exists in inventory
                $result[] = [
                    'id' => $part->id,
                    'part_name' => $part->part_name,
                    'category' => $part->category,
                    'stock_quantity' => $part->stock_quantity,
                    'minimum_stock' => $part->minimum_stock,
                    'unit_price' => $part->unit_price,
                    'in_inventory' => true
                ];
            } else {
                // Part not in inventory yet - still show it as suggested
                $result[] = [
                    'id' => null,
                    'part_name' => $partName,
                    'category' => $this->partCategories[$partName] ?? 'General',
                    'stock_quantity' => 0,
                    'minimum_stock' => 5,
                    'unit_price' => 0.00,
                    'in_inventory' => false
                ];
            }
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'parts' => $result,
                'diagnosis' => $diagnosis
            ]));
    }

    /**
     * Get parts by names (legacy method)
     */
    public function getByNames()
    {
        $this->request->allowMethod(['post']);
        
        $partNames = $this->request->getData('part_names', []);
        
        if (empty($partNames)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'part_names is required'
                ]));
        }
        
        $parts = $this->fetchTable('Parts')
            ->find()
            ->where(['part_name IN' => $partNames])
            ->all();
        
        $result = [];
        foreach ($parts as $part) {
            $result[] = [
                'id' => $part->id,
                'part_name' => $part->part_name,
                'stock_quantity' => $part->stock_quantity
            ];
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'parts' => $result
            ]));
    }

    /**
     * Get used parts for a device
     */
    public function getUsed()
    {
        $this->request->allowMethod(['post']);
        
        $deviceId = $this->request->getData('device_id');
        
        if (!$deviceId) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Device ID is required'
                ]));
        }
        
        $usage = $this->fetchTable('RepairPartsUsage')
            ->find()
            ->contain(['Parts'])
            ->where([
                'RepairPartsUsage.device_id' => $deviceId,
                'RepairPartsUsage.returned' => 0
            ])
            ->all();
        
        $usedParts = [];
        foreach ($usage as $item) {
            $usedParts[] = [
                'usage_id' => $item->id,
                'part_id' => $item->part_id,
                'part_name' => $item->part->part_name,
                'category' => $item->part->category,
                'quantity' => $item->quantity
            ];
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'used_parts' => $usedParts
            ]));
    }

    /**
     * Deduct parts from inventory
     */
    public function deduct()
    {
        $this->request->allowMethod(['post']);
        
        $deviceId = $this->request->getData('device_id');
        $partsUsed = $this->request->getData('parts_used');
        
        if (!$deviceId || empty($partsUsed)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Device ID and parts_used are required'
                ]));
        }
        
        $partsTable = $this->fetchTable('Parts');
        $repairPartsUsageTable = $this->fetchTable('RepairPartsUsage');
        $errors = [];
        
        foreach ($partsUsed as $part) {
            $partId = $part['part_id'];
            $quantity = $part['quantity'] ?? 1;
            
            // Check if part exists
            $partRecord = $partsTable->get($partId);
            if (!$partRecord) {
                $errors[] = "Part ID {$partId} not found";
                continue;
            }
            
            // Check stock
            if ($partRecord->stock_quantity < $quantity) {
                $errors[] = "Insufficient stock for {$partRecord->part_name}";
                continue;
            }
            
            // Deduct stock
            $partRecord->stock_quantity -= $quantity;
            $partsTable->save($partRecord);
            
            // Record usage
            $usage = $repairPartsUsageTable->newEntity([
                'device_id' => $deviceId,
                'part_id' => $partId,
                'quantity' => $quantity,
                'returned' => 0
            ]);
            $repairPartsUsageTable->save($usage);
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => empty($errors),
                'errors' => $errors,
                'message' => empty($errors) ? 'Parts deducted successfully' : 'Some parts could not be deducted'
            ]));
    }

    /**
     * Return parts to inventory
     */
    public function returnParts()
    {
        $this->request->allowMethod(['post']);
        
        $deviceId = $this->request->getData('device_id');
        $partIds = $this->request->getData('part_ids');
        
        if (!$deviceId || empty($partIds)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Device ID and part_ids are required'
                ]));
        }
        
        $partsTable = $this->fetchTable('Parts');
        $repairPartsUsageTable = $this->fetchTable('RepairPartsUsage');
        
        foreach ($partIds as $usageId) {
            $usage = $repairPartsUsageTable->get($usageId);
            if ($usage && !$usage->returned) {
                // Return stock
                $part = $partsTable->get($usage->part_id);
                $part->stock_quantity += $usage->quantity;
                $partsTable->save($part);
                
                // Mark as returned
                $usage->returned = 1;
                $repairPartsUsageTable->save($usage);
            }
        }
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'message' => 'Parts returned successfully'
            ]));
    }
}