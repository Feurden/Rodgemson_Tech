<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Device> $devices
 */
?>
<div class="devices index content">
    <?= $this->Html->link(__('New Device'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Devices') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('customer_id') ?></th>
                    <th><?= $this->Paginator->sort('brand') ?></th>
                    <th><?= $this->Paginator->sort('model') ?></th>
                    <th><?= $this->Paginator->sort('imei') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th><?= $this->Paginator->sort('priority_level') ?></th>
                    <th><?= $this->Paginator->sort('date_received') ?></th>
                    <th><?= $this->Paginator->sort('date_released') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devices as $device): ?>
                <tr>
                    <td><?= $this->Number->format($device->id) ?></td>
                    <td><?= $device->hasValue('customer') ? $this->Html->link($device->customer->full_name, ['controller' => 'Customers', 'action' => 'view', $device->customer->id]) : '' ?></td>
                    <td><?= h($device->brand) ?></td>
                    <td><?= h($device->model) ?></td>
                    <td><?= h($device->imei) ?></td>
                    <td><?= h($device->status) ?></td>
                    <td><?= h($device->priority_level) ?></td>
                    <td><?= h($device->date_received) ?></td>
                    <td><?= h($device->date_released) ?></td>
                    <td><?= h($device->created) ?></td>
                    <td><?= h($device->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $device->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $device->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $device->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $device->id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>