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

            error_log('Parts add data: ' . json_encode($data));

            $part = $partsTable->newEntity([
                'part_name'      => trim((string)($data['part_name'] ?? '')),
                'category'       => trim((string)($data['category'] ?? 'Uncategorized')),
                'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
                'minimum_stock'  => (int)($data['minimum_stock'] ?? 5),
                'unit_price'     => (float)($data['unit_price'] ?? 0.00),
            ]);

            if ($partsTable->save($part)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode(['success' => true, 'id' => $part->id, 'message' => 'Part added']));
            } else {
                $errors = $part->getErrors();
                error_log('Part save failed: ' . json_encode($errors));
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'error' => 'Failed to save part', 'errors' => $errors]));
            }
        } catch (\Exception $e) {
            error_log('Parts add exception: ' . $e->getMessage());
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
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
            $data       = $this->request->getData();
            $partId     = $data['part_id'] ?? null;
            $qtyAdded   = (int)($data['quantity_added'] ?? 0);

            if (!$partId || $qtyAdded <= 0) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'error' => 'Part ID and quantity are required']));
            }

            $part = $partsTable->get($partId);
            $part->stock_quantity += $qtyAdded;

            if ($partsTable->save($part)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success'  => true,
                        'message'  => 'Stock updated',
                        'quantity' => $part->stock_quantity
                    ]));
            } else {
                $errors = $part->getErrors();
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

    /**
     * Submit a part request / order note for a missing part needed by a customer.
     * Saves into the `orders` table with status = 'Pending'.
     */
    public function requestOrder(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $ordersTable = $this->getTableLocator()->get('orders');
            $data        = $this->request->getData();

            $partName     = trim((string)($data['part_name']     ?? ''));
            $quantity     = (int)($data['quantity']              ?? 1);
            $customerName = trim((string)($data['customer_name'] ?? ''));
            $phoneModel   = trim((string)($data['phone_model']   ?? ''));
            $notes        = trim((string)($data['notes']         ?? ''));

            // Validate required fields
            if (!$partName || !$customerName || !$phoneModel || !$notes) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error'   => 'part_name, customer_name, phone_model, and notes are required.'
                    ]));
            }

            if ($quantity < 1) {
                $quantity = 1;
            }

            error_log('Part request data: ' . json_encode($data));

            $order = $ordersTable->newEntity([
                'part_name'     => $partName,
                'quantity'      => $quantity,
                'customer_name' => $customerName,
                'phone_model'   => $phoneModel,
                'notes'         => $notes,
                'status'        => 'Pending',
            ]);

            if ($ordersTable->save($order)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'id'      => $order->id,
                        'message' => 'Part request submitted successfully.'
                    ]));
            } else {
                $errors = $order->getErrors();
                error_log('Part request save failed: ' . json_encode($errors));
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error'   => 'Failed to save part request.',
                        'errors'  => $errors
                    ]));
            }
        } catch (\Exception $e) {
            error_log('Part requestOrder exception: ' . $e->getMessage());
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }
    public function updateStatus()
    {
        $this->request->allowMethod(['post']);

        $orders = $this->getTableLocator()->get('Orders');
        $id = $this->request->getData('id');
        $status = $this->request->getData('status');

        $order = $orders->get($id);
        $order->status = $status;

        if ($orders->save($order)) {
            return $this->redirect($this->referer());
        }
    }
    public function delete()
    {
        $this->request->allowMethod(['post']);

        $orders = $this->getTableLocator()->get('Orders');
        $id = $this->request->getData('id');

        $order = $orders->get($id);

        if ($orders->delete($order)) {
            return $this->redirect($this->referer());
        }
    }
    public function deleteOrder()
{
    $this->request->allowMethod(['post']);

    $data = $this->request->getData();
    $id = $data['id'] ?? null;

    if ($id) {
        $conn = ConnectionManager::get('default');

        $conn->execute("DELETE FROM orders WHERE id = :id", ['id' => $id]);

        $this->set([
            'success' => true,
            '_serialize' => ['success']
        ]);
    } else {
        $this->set([
            'success' => false,
            '_serialize' => ['success']
        ]);
    }
}
}