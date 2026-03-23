<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class PartsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * Add a new part to inventory
     */
    public function add(): Response
    {
        $this->request->allowMethod(['post']);
        
        try {
            $partsTable = $this->getTableLocator()->get('Parts');
            $data = $this->request->getData();

            // Log data for debugging
            error_log('Parts add data: ' . json_encode($data));

            $part = $partsTable->newEntity([
                'part_name' => trim((string)($data['part_name'] ?? '')),
                'category' => trim((string)($data['category'] ?? 'Uncategorized')),
                'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
                'minimum_stock' => (int)($data['minimum_stock'] ?? 5),
                'unit_price' => (float)($data['unit_price'] ?? 0.00),
            ]);

            error_log('Part entity created: ' . json_encode($part->toArray()));

            if ($partsTable->save($part)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode(['success' => true, 'id' => $part->id, 'message' => 'Part added']));
            } else {
                $errors = $part->getErrors();
                error_log('Part save failed with errors: ' . json_encode($errors));
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'error' => 'Failed to save part', 'errors' => $errors]));
            }
        } catch (\Exception $e) {
            error_log('Parts add exception: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]));
        }
    }

    /**
     * Restock a part (increase quantity)
     */
    public function restock(): Response
    {
        $this->request->allowMethod(['post']);
        
        try {
            $partsTable = $this->getTableLocator()->get('Parts');
            $data = $this->request->getData();
            $partId = $data['part_id'] ?? null;
            $qtyAdded = (int)($data['quantity_added'] ?? 0);

            if (!$partId || $qtyAdded <= 0) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'error' => 'Part ID and quantity are required']));
            }

            error_log("Restocking part $partId with quantity $qtyAdded");

            $part = $partsTable->get($partId);
            $part->stock_quantity += $qtyAdded;

            if ($partsTable->save($part)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true, 
                        'message' => 'Stock updated',
                        'quantity' => $part->stock_quantity
                    ]));
            } else {
                $errors = $part->getErrors();
                error_log('Part restock failed: ' . json_encode($errors));
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'error' => 'Failed to update stock', 'errors' => $errors]));
            }
        } catch (\Exception $e) {
            error_log('Part restock exception: ' . $e->getMessage());
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }
}