<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Device $device
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Device'), ['action' => 'edit', $device->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Device'), ['action' => 'delete', $device->id], ['confirm' => __('Are you sure you want to delete # {0}?', $device->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Devices'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Device'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="devices view content">
            <h3><?= h($device->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Customer') ?></th>
                    <td><?= $device->hasValue('customer') ? $this->Html->link($device->customer->full_name, ['controller' => 'Customers', 'action' => 'view', $device->customer->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Brand') ?></th>
                    <td><?= h($device->brand) ?></td>
                </tr>
                <tr>
                    <th><?= __('Model') ?></th>
                    <td><?= h($device->model) ?></td>
                </tr>
                <tr>
                    <th><?= __('Imei') ?></th>
                    <td><?= h($device->imei) ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= h($device->status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Priority Level') ?></th>
                    <td><?= h($device->priority_level) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($device->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Date Received') ?></th>
                    <td><?= h($device->date_received) ?></td>
                </tr>
                <tr>
                    <th><?= __('Date Released') ?></th>
                    <td><?= h($device->date_released) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($device->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($device->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Issue Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($device->issue_description)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Repair Parts') ?></h4>
                <?php if (!empty($device->repair_parts)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Part Id') ?></th>
                            <th><?= __('Quantity Used') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($device->repair_parts as $repairPart) : ?>
                        <tr>
                            <td><?= h($repairPart->id) ?></td>
                            <td><?= h($repairPart->part_id) ?></td>
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