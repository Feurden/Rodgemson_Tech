<?php
declare(strict_types=1);

namespace App\Controller;

class RepairsController extends AppController
{
    /**
     * Index - list all devices under repair
     */
    public function index()
    {
        $devicesTable = $this->fetchTable('Devices');

        $query = $devicesTable->find()
            ->contain(['Customers'])
            ->orderBy(['Devices.created' => 'DESC']);

        $devices = $this->paginate($query);

        $this->set(compact('devices'));
    }

    /**
     * View a single repair job
     */
    public function view($id = null)
    {
        $devicesTable = $this->fetchTable('Devices');

        $device = $devicesTable->get($id, contain: ['Customers', 'RepairParts']);

        $this->set(compact('device'));
    }

    /**
     * Add a new repair job
     */
    public function add()
    {
        $devicesTable = $this->fetchTable('Devices');

        $device = $devicesTable->newEmptyEntity();

        if ($this->request->is('post')) {
            $device = $devicesTable->patchEntity($device, $this->request->getData());
            if ($devicesTable->save($device)) {
                $this->Flash->success('Repair job has been created successfully.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not create repair job. Please try again.');
        }

        $customersTable = $this->fetchTable('Customers');
        $customers = $customersTable->find('list', limit: 200)->all();

        $this->set(compact('device', 'customers'));
    }

    /**
     * Edit / update a repair job
     */
    public function edit($id = null)
    {
        $devicesTable = $this->fetchTable('Devices');

        $device = $devicesTable->get($id, contain: []);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $device = $devicesTable->patchEntity($device, $this->request->getData());
            if ($devicesTable->save($device)) {
                $this->Flash->success('Repair job has been updated successfully.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not update repair job. Please try again.');
        }

        $customersTable = $this->fetchTable('Customers');
        $customers = $customersTable->find('list', limit: 200)->all();

        $this->set(compact('device', 'customers'));
    }

    /**
     * Delete a repair job
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $devicesTable = $this->fetchTable('Devices');
        $device = $devicesTable->get($id);

        if ($devicesTable->delete($device)) {
            $this->Flash->success('Repair job has been deleted.');
        } else {
            $this->Flash->error('Could not delete repair job. Please try again.');
        }

        return $this->redirect(['action' => 'index']);
    }
}