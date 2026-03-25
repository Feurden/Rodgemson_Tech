<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class DevicesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * Add a new device/repair job
     */
    public function add(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $devicesTable   = $this->getTableLocator()->get('Devices');
            $customersTable = $this->getTableLocator()->get('Customers');
            $data           = $this->request->getData();

            error_log('Device add data: ' . json_encode($data));

            // Create customer record
            $customer = $customersTable->newEntity([
                'full_name'                  => trim((string)($data['customer_name'] ?? 'Unknown Customer')),
                'contact_no'                 => trim((string)($data['contact_no']    ?? '')),
                'phone_model'                => ($data['brand'] ?? 'Unknown') . ' ' . ($data['model'] ?? 'Unknown'),
                'phone_issue'                => trim((string)($data['issue_description'] ?? '')),
                'diagnostic'                 => trim((string)($data['diagnostic']                  ?? '')),
                'suggested_part_replacement' => trim((string)($data['suggested_part_replacement']  ?? '')),
                'notes'                      => '',
            ]);

            if (!$customersTable->save($customer)) {
                error_log('Customer save failed: ' . json_encode($customer->getErrors()));
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'error' => 'Failed to create customer record']));
            }

            $device = $devicesTable->newEntity([
                'customer_id'       => $customer->id,
                'brand'             => trim((string)($data['brand']              ?? 'Unknown')),
                'model'             => trim((string)($data['model']              ?? 'Unknown')),
                'issue_description' => trim((string)($data['issue_description'] ?? '')),
                'technician'        => trim((string)($data['technician']         ?? 'Unassigned')),
                'status'            => $data['status']         ?? 'Pending',
                'priority_level'    => $data['priority_level'] ?? 'Medium',
            ]);

            error_log('Device entity: ' . json_encode($device->toArray()));

            if ($devicesTable->save($device)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode(['success' => true, 'id' => $device->id, 'message' => 'Device added']));
            } else {
                $errors = $device->getErrors();
                error_log('Device save failed: ' . json_encode($errors));
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'error' => 'Failed to save device', 'errors' => $errors]));
            }
        } catch (\Exception $e) {
            error_log('Device add exception: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Update an existing device/repair job
     */
    public function update(): Response
    {
        $this->request->allowMethod(['patch', 'post']);

        try {
            $devicesTable = $this->getTableLocator()->get('Devices');
            $data         = $this->request->getData();
            $deviceId     = $data['id'] ?? null;

            if (!$deviceId) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'error' => 'Device ID is required']));
            }

            $device = $devicesTable->get($deviceId);

            if (isset($data['status']))           $device->status            = $data['status'];
            if (isset($data['technician']))        $device->technician        = $data['technician'];
            if (isset($data['issue_description'])) $device->issue_description = $data['issue_description'];
            if (isset($data['date_released'])) {
                $device->date_released = $data['date_released']
                    ? new \DateTime($data['date_released']) : null;
            }

            if (!$devicesTable->save($device)) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error'   => 'Failed to update device',
                        'errors'  => $device->getErrors(),
                    ]));
            }

            // Update customer record fields
            // 'notes'           → customers.notes          (separate notes column)
            // 'diagnostic'      → customers.diagnostic
            // 'suggested_parts' → customers.suggested_part_replacement
            //
            // NOTE: customers.phone_issue stores the ORIGINAL issue description
            // reported by the customer and is never overwritten by technician notes.
            $needsCustomerUpdate = isset($data['diagnostic'])
                || isset($data['suggested_parts'])
                || isset($data['notes'])
                || isset($data['issue_description']);

            if ($needsCustomerUpdate) {
                $customersTable = $this->getTableLocator()->get('Customers');
                $customer       = $customersTable->get($device->customer_id);

                if (isset($data['diagnostic']))
                    $customer->diagnostic = $data['diagnostic'];

                if (isset($data['suggested_parts']))
                    $customer->suggested_part_replacement = $data['suggested_parts'];

                // Notes go to their own column — never overwrite phone_issue
                if (isset($data['notes']))
                    $customer->notes = $data['notes'];

                // Update the original issue description on the customer record too
                if (isset($data['issue_description']))
                    $customer->phone_issue = $data['issue_description'];

                if (!$customersTable->save($customer)) {
                    error_log('Customer update failed: ' . json_encode($customer->getErrors()));
                }
            }

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'message' => 'Device updated']));

        } catch (\Exception $e) {
            error_log('Device update exception: ' . $e->getMessage());
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }
}