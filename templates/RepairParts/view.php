<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RepairPart $repairPart
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Repair Part'), ['action' => 'edit', $repairPart->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Repair Part'), ['action' => 'delete', $repairPart->id], ['confirm' => __('Are you sure you want to delete # {0}?', $repairPart->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Repair Parts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Repair Part'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="repairParts view content">
            <h3><?= h($repairPart->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Device') ?></th>
                    <td><?= $repairPart->hasValue('device') ? $this->Html->link($repairPart->device->id, ['controller' => 'Devices', 'action' => 'view', $repairPart->device->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Part') ?></th>
                    <td><?= $repairPart->hasValue('part') ? $this->Html->link($repairPart->part->part_name, ['controller' => 'Parts', 'action' => 'view', $repairPart->part->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($repairPart->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Quantity Used') ?></th>
                    <td><?= $repairPart->quantity_used === null ? '' : $this->Number->format($repairPart->quantity_used) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>