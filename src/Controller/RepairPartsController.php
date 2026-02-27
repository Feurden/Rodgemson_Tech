<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * RepairParts Controller
 *
 * @property \App\Model\Table\RepairPartsTable $RepairParts
 */
class RepairPartsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->RepairParts->find()
            ->contain(['Devices', 'Parts']);
        $repairParts = $this->paginate($query);

        $this->set(compact('repairParts'));
    }

    /**
     * View method
     *
     * @param string|null $id Repair Part id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $repairPart = $this->RepairParts->get($id, contain: ['Devices', 'Parts']);
        $this->set(compact('repairPart'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $repairPart = $this->RepairParts->newEmptyEntity();
        if ($this->request->is('post')) {
            $repairPart = $this->RepairParts->patchEntity($repairPart, $this->request->getData());
            if ($this->RepairParts->save($repairPart)) {
                $this->Flash->success(__('The repair part has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The repair part could not be saved. Please, try again.'));
        }
        $devices = $this->RepairParts->Devices->find('list', limit: 200)->all();
        $parts = $this->RepairParts->Parts->find('list', limit: 200)->all();
        $this->set(compact('repairPart', 'devices', 'parts'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Repair Part id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $repairPart = $this->RepairParts->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $repairPart = $this->RepairParts->patchEntity($repairPart, $this->request->getData());
            if ($this->RepairParts->save($repairPart)) {
                $this->Flash->success(__('The repair part has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The repair part could not be saved. Please, try again.'));
        }
        $devices = $this->RepairParts->Devices->find('list', limit: 200)->all();
        $parts = $this->RepairParts->Parts->find('list', limit: 200)->all();
        $this->set(compact('repairPart', 'devices', 'parts'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Repair Part id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $repairPart = $this->RepairParts->get($id);
        if ($this->RepairParts->delete($repairPart)) {
            $this->Flash->success(__('The repair part has been deleted.'));
        } else {
            $this->Flash->error(__('The repair part could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
