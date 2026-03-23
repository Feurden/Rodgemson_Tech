<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class PartsUsageController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * Get parts by names (from AI suggestion), with live stock quantities
     */
    public function getByNames(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $data        = $this->request->getData();
            $partNames   = $data['part_names'] ?? [];

            if (empty($partNames)) {
                return $this->jsonResponse(['success' => true, 'parts' => []]);
            }

            $partsTable = $this->getTableLocator()->get('Parts');
            $parts = $partsTable->find()
                ->where(['part_name IN' => $partNames])
                ->toArray();

            $result = array_map(fn($p) => [
                'id'             => $p->id,
                'part_name'      => $p->part_name,
                'category'       => $p->category,
                'stock_quantity' => $p->stock_quantity,
                'unit_price'     => $p->unit_price,
            ], $parts);

            return $this->jsonResponse(['success' => true, 'parts' => $result]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Deduct parts from stock when technician starts repair
     */
    public function deduct(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $data      = $this->request->getData();
            $deviceId  = $data['device_id']  ?? null;
            $partsUsed = $data['parts_used'] ?? []; // [{part_id, quantity}]

            if (!$deviceId || empty($partsUsed)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Missing device_id or parts_used'], 400);
            }

            $partsTable      = $this->getTableLocator()->get('Parts');
            $usageTable      = $this->getTableLocator()->get('RepairPartsUsage');

            foreach ($partsUsed as $used) {
                $partId  = $used['part_id']  ?? null;
                $qty     = (int)($used['quantity'] ?? 1);

                if (!$partId || $qty <= 0) continue;

                $part = $partsTable->get($partId);

                if ($part->stock_quantity < $qty) {
                    return $this->jsonResponse([
                        'success' => false,
                        'error'   => "Not enough stock for: {$part->part_name} (available: {$part->stock_quantity})"
                    ], 400);
                }

                // Deduct stock
                $part->stock_quantity -= $qty;
                $partsTable->save($part);

                // Log usage
                $usage = $usageTable->newEntity([
                    'device_id' => $deviceId,
                    'part_id'   => $partId,
                    'quantity'  => $qty,
                    'returned'  => false,
                ]);
                $usageTable->save($usage);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Parts deducted']);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Return unused parts back to stock
     */
    public function returnParts(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $data     = $this->request->getData();
            $deviceId = $data['device_id']  ?? null;
            $partIds  = $data['part_ids']   ?? []; // array of usage IDs to return

            if (!$deviceId || empty($partIds)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Missing device_id or part_ids'], 400);
            }

            $partsTable = $this->getTableLocator()->get('Parts');
            $usageTable = $this->getTableLocator()->get('RepairPartsUsage');

            foreach ($partIds as $usageId) {
                $usage = $usageTable->get($usageId);

                if ($usage->returned) continue; // already returned

                // Return to stock
                $part = $partsTable->get($usage->part_id);
                $part->stock_quantity += $usage->quantity;
                $partsTable->save($part);

                // Mark as returned
                $usage->returned = true;
                $usageTable->save($usage);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Parts returned to stock']);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get parts already used for a specific device/job
     */
    public function getUsed(): Response
    {
        $this->request->allowMethod(['get', 'post']);

        try {
            $data     = $this->request->getData();
            $deviceId = $data['device_id'] ?? null;

            if (!$deviceId) {
                return $this->jsonResponse(['success' => false, 'error' => 'Missing device_id'], 400);
            }

            $usageTable = $this->getTableLocator()->get('RepairPartsUsage');
            $usages = $usageTable->find()
                ->contain(['Parts'])
                ->where([
                    'RepairPartsUsage.device_id' => $deviceId,
                    'RepairPartsUsage.returned'  => false,
                ])
                ->toArray();

            $result = array_map(fn($u) => [
                'usage_id'  => $u->id,
                'part_id'   => $u->part_id,
                'part_name' => $u->part->part_name ?? '—',
                'category'  => $u->part->category  ?? '—',
                'quantity'  => $u->quantity,
            ], $usages);

            return $this->jsonResponse(['success' => true, 'used_parts' => $result]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function jsonResponse(array $data, int $status = 200): Response
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($data));
    }

    /**
 * Get parts by diagnosis label (uses replacement map logic)
 */
    public function getByDiagnosis(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $data      = $this->request->getData();
            $diagnosis = trim((string)($data['diagnosis'] ?? ''));

            if (!$diagnosis) {
                return $this->jsonResponse(['success' => true, 'parts' => []]);
            }

            // Replacement map — mirrors your Python REPLACEMENT_MAP exactly
            $replacementMap = [
                'Mainboard Issue'        => ['Mainboard/Motherboard', 'Power IC', 'CPU', 'RAM', 'Baseband IC'],
                'Charging Port Issue'    => ['Charging Port', 'USB Connector', 'Flex Cable'],
                'Charging IC Issue'      => ['Charging IC Chip', 'Power Management IC', 'Battery Connector'],
                'Battery Issue'          => ['Battery', 'Battery Connector', 'Thermal Sensor IC'],
                'SIM IC Issue'           => ['SIM Card Slot', 'SIM IC Chip', 'Baseband IC'],
                'Touch Controller Issue' => ['Touch Controller IC', 'Digitizer', 'Power Management IC', 'Thermal Sensor IC'],
                'Display IC Issue'       => ['Display Driver IC', 'Backlight IC', 'Connector Flex'],
                'Speaker Issue'          => ['Speaker Module', 'Audio IC', 'Connector Flex'],
                'Microphone Issue'       => ['Microphone Module', 'Audio IC', 'Connector Flex'],
                'Antenna Issue'          => ['Antenna Module', 'RF IC', 'Baseband IC'],
                'Baseband Issue'         => ['Baseband IC', 'RF IC', 'Antenna Module'],
                'Power IC Issue'         => ['Power IC', 'Battery Connector', 'Mainboard/Motherboard'],
                'Software/OS Issue'      => ['Reflash Firmware', 'Update OS', 'Mainboard Check'],
            ];

            $suggestedNames = $replacementMap[$diagnosis] ?? [];

            if (empty($suggestedNames)) {
                return $this->jsonResponse(['success' => true, 'parts' => [], 'note' => 'No map entry for this diagnosis']);
            }

            // Fetch matched parts from DB
            $partsTable    = $this->getTableLocator()->get('Parts');
            $inventoryParts = $partsTable->find()
                ->where(['part_name IN' => $suggestedNames])
                ->toArray();

            // Index by name for quick lookup
            $inventoryMap = [];
            foreach ($inventoryParts as $p) {
                $inventoryMap[$p->part_name] = $p;
            }

            // Build result preserving map order, marking availability
            $result = [];
            foreach ($suggestedNames as $name) {
                if (isset($inventoryMap[$name])) {
                    $p        = $inventoryMap[$name];
                    $result[] = [
                        'id'             => $p->id,
                        'part_name'      => $p->part_name,
                        'category'       => $p->category,
                        'stock_quantity' => $p->stock_quantity,
                        'unit_price'     => $p->unit_price,
                        'in_inventory'   => true,
                    ];
                } else {
                    $result[] = [
                        'id'           => null,
                        'part_name'    => $name,
                        'category'     => '—',
                        'stock_quantity' => 0,
                        'unit_price'   => 0,
                        'in_inventory' => false,
                    ];
                }
            }

            return $this->jsonResponse(['success' => true, 'parts' => $result]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}