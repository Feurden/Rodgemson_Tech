<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Part $part
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Part'), ['action' => 'edit', $part->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Part'), ['action' => 'delete', $part->id], ['confirm' => __('Are you sure you want to delete # {0}?', $part->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Parts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Part'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="parts view content">
            <h3><?= h($part->part_name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Part Name') ?></th>
                    <td><?= h($part->part_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Category') ?></th>
                    <td><?= h($part->category) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($part->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Stock Quantity') ?></th>
                    <td><?= $part->stock_quantity === null ? '' : $this->Number->format($part->stock_quantity) ?></td>
                </tr>
                <tr>
                    <th><?= __('Minimum Stock') ?></th>
                    <td><?= $part->minimum_stock === null ? '' : $this->Number->format($part->minimum_stock) ?></td>
                </tr>
                <tr>
                    <th><?= __('Unit Price') ?></th>
                    <td><?= $part->unit_price === null ? '' : $this->Number->format($part->unit_price) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($part->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($part->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Repair Parts') ?></h4>
                <?php if (!empty($part->repair_parts)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Device Id') ?></th>
                            <th><?= __('Quantity Used') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($part->repair_parts as $repairPart) : ?>
                        <tr>
                            <td><?= h($repairPart->id) ?></td>
                            <td><?= h($repairPart->device_id) ?></td>
                            <td><?= h($repairPart->quantity_used) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'RepairParts', 'action' => 'view', $repairPart->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'RepairParts', 'action' => 'edit', $repairPart->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'RepairParts', 'action' => 'delete', $repairPart->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $repairPart->id),
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>