<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\RepairPart> $repairParts
 */
?>
<div class="repairParts index content">
    <?= $this->Html->link(__('New Repair Part'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Repair Parts') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('device_id') ?></th>
                    <th><?= $this->Paginator->sort('part_id') ?></th>
                    <th><?= $this->Paginator->sort('quantity_used') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($repairParts as $repairPart): ?>
                <tr>
                    <td><?= $this->Number->format($repairPart->id) ?></td>
                    <td><?= $repairPart->hasValue('device') ? $this->Html->link($repairPart->device->id, ['controller' => 'Devices', 'action' => 'view', $repairPart->device->id]) : '' ?></td>
                    <td><?= $repairPart->hasValue('part') ? $this->Html->link($repairPart->part->part_name, ['controller' => 'Parts', 'action' => 'view', $repairPart->part->id]) : '' ?></td>
                    <td><?= $repairPart->quantity_used === null ? '' : $this->Number->format($repairPart->quantity_used) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $repairPart->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $repairPart->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $repairPart->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $repairPart->id),
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