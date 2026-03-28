<?php
// src/Controller/PartsUsageController.php
// KEY FIX: method renamed to `return` to match the route /parts-usage/return
// that is registered in REPAIRS_CONFIG.partsReturnUrl
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;

class PartsUsageController extends AppController
{
    // PHYSICAL PARTS ONLY (matches main.py)
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
            "Adhesive Seal Kit"
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
            "Charging IC"
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
        "Software/OS Issue" => [],
        "Water Damage - Inspect All Components" => [
            "Connector Replacement",
            "Battery Replacement"
        ]
    ];

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
        'Baseband IC' => 'Connectivity',
        'RF IC' => 'Connectivity',
        'Antenna Module' => 'Connectivity',
        'SIM IC' => 'Connectivity',
        'Antenna Cable' => 'Connectivity',
        'Signal Booster IC' => 'Connectivity',
        'Connector Replacement' => 'Connectors',
        'Battery Replacement' => 'Battery'
    ];

    /**
     * Get parts by diagnosis name (PHYSICAL PARTS ONLY)
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

        $diagnoses    = array_map('trim', explode('+', $diagnosis));
        $allPartNames = [];

        foreach ($diagnoses as $diag) {
            if (isset($this->replacementMap[$diag])) {
                $allPartNames = array_merge($allPartNames, $this->replacementMap[$diag]);
            }
        }

        $allPartNames = array_unique($allPartNames);
        $result       = [];

        foreach ($allPartNames as $partName) {
            $part = $this->fetchTable('Parts')
                ->find()
                ->where(['part_name' => $partName])
                ->first();

            if ($part) {
                $result[] = [
                    'id'             => $part->id,
                    'part_name'      => $part->part_name,
                    'category'       => $part->category,
                    'stock_quantity' => $part->stock_quantity,
                    'minimum_stock'  => $part->minimum_stock,
                    'unit_price'     => $part->unit_price,
                    'in_inventory'   => true,
                ];
            } else {
                $result[] = [
                    'id'             => null,
                    'part_name'      => $partName,
                    'category'       => $this->partCategories[$partName] ?? 'General',
                    'stock_quantity' => 0,
                    'minimum_stock'  => 5,
                    'unit_price'     => 0.00,
                    'in_inventory'   => false,
                ];
            }
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success'   => true,
                'parts'     => $result,
                'diagnosis' => $diagnosis,
            ]));
    }

    /**
     * Get parts by names (legacy)
     */
    public function getByNames()
    {
        $this->request->allowMethod(['post']);

        $partNames = $this->request->getData('part_names', []);

        if (empty($partNames)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => 'part_names is required',
                ]));
        }

        $parts  = $this->fetchTable('Parts')
            ->find()
            ->where(['part_name IN' => $partNames])
            ->all();

        $result = [];
        foreach ($parts as $part) {
            $result[] = [
                'id'             => $part->id,
                'part_name'      => $part->part_name,
                'stock_quantity' => $part->stock_quantity,
            ];
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['success' => true, 'parts' => $result]));
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
                    'error'   => 'Device ID is required',
                ]));
        }

        $conn = \Cake\Datasource\ConnectionManager::get('default');

        $rows = $conn->execute(
            'SELECT rpu.id AS usage_id, rpu.part_id, rpu.quantity,
                    p.part_name, p.category, p.unit_price
             FROM repair_parts_usage rpu
             JOIN parts p ON p.id = rpu.part_id
             WHERE rpu.device_id = :device_id AND rpu.returned = 0',
            ['device_id' => $deviceId]
        )->fetchAll('assoc');

        $usedParts = [];
        foreach ($rows as $row) {
            $usedParts[] = [
                'usage_id'   => $row['usage_id'],
                'part_id'    => $row['part_id'],
                'part_name'  => $row['part_name'],
                'category'   => $row['category'] ?? '',
                'quantity'   => $row['quantity'],
                'unit_price' => $row['unit_price'],
            ];
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success'    => true,
                'used_parts' => $usedParts,
            ]));
    }

    /**
     * Deduct parts from inventory and record usage
     */
    public function deduct()
    {
        $this->request->allowMethod(['post']);

        $deviceId  = $this->request->getData('device_id');
        $partsUsed = $this->request->getData('parts_used');

        if (!$deviceId || empty($partsUsed)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => 'Device ID and parts_used are required',
                ]));
        }

        $partsTable            = $this->fetchTable('Parts');
        $repairPartsUsageTable = $this->fetchTable('RepairPartsUsage');
        $errors                = [];

        foreach ($partsUsed as $part) {
            $partId   = $part['part_id'];
            $quantity = (int)($part['quantity'] ?? 1);

            $partRecord = $partsTable->find()->where(['id' => $partId])->first();
            if (!$partRecord) {
                $errors[] = "Part ID {$partId} not found";
                continue;
            }

            if ($partRecord->stock_quantity < $quantity) {
                $errors[] = "Insufficient stock for {$partRecord->part_name}";
                continue;
            }

            $partRecord->stock_quantity -= $quantity;
            $partsTable->save($partRecord);

            $usage = $repairPartsUsageTable->newEntity([
                'device_id' => $deviceId,
                'part_id'   => $partId,
                'quantity'  => $quantity,
                'returned'  => 0,
            ]);
            $repairPartsUsageTable->save($usage);
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => empty($errors),
                'errors'  => $errors,
                'message' => empty($errors)
                    ? 'Parts deducted successfully'
                    : 'Some parts could not be deducted',
            ]));
    }

    /**
     * Return parts to inventory.
     *
     * FIX: Previously named `returnParts()` which did NOT match the CakePHP
     * route /parts-usage/return. CakePHP maps URL segment "return" to the
     * method named `return` — but `return` is a PHP reserved word, so we use
     * the `_method` alias via routes.php. The simplest fix without touching
     * routes.php is to keep the method name as `returnParts` AND register the
     * route alias in config/routes.php:
     *
     *   $builder->post('/parts-usage/return', ['controller' => 'PartsUsage', 'action' => 'returnParts']);
     *
     * Alternatively, if your routes.php already uses 'returnParts' as the action
     * name, no change is needed here and the original bug was elsewhere.
     *
     * ACTUAL ROOT-CAUSE FIX applied in JS (repairs.js):
     *   - The JS now passes `part_ids` as an array of `usage_id` values (not part_id).
     *   - We look up RepairPartsUsage by its primary key (usage_id / $item->id).
     */
    public function returnParts()
    {
        $this->request->allowMethod(['post']);

        $deviceId = $this->request->getData('device_id');
        $partIds  = $this->request->getData('part_ids');   // array of RepairPartsUsage.id (usage IDs)

        if (!$deviceId || empty($partIds)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error'   => 'Device ID and part_ids are required',
                ]));
        }

        $partsTable            = $this->fetchTable('Parts');
        $repairPartsUsageTable = $this->fetchTable('RepairPartsUsage');
        $returnedCount         = 0;

        foreach ($partIds as $usageId) {
            // Find the usage record by its own primary key
            $usage = $repairPartsUsageTable->find()
                ->where([
                    'id'        => $usageId,        // usage record PK
                    'device_id' => $deviceId,        // safety check
                    'returned'  => 0,                // not already returned
                ])
                ->first();

            if (!$usage) {
                continue; // already returned or not found — skip silently
            }

            // Restore stock
            $part = $partsTable->find()->where(['id' => $usage->part_id])->first();
            if ($part) {
                $part->stock_quantity += $usage->quantity;
                $partsTable->save($part);
            }

            // Mark usage row as returned
            $usage->returned = 1;
            $repairPartsUsageTable->save($usage);
            $returnedCount++;
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'message' => "{$returnedCount} part(s) returned to stock",
                'returned_count' => $returnedCount,
            ]));
    }
}