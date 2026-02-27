<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Customer $customer
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Customer'), ['action' => 'edit', $customer->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Customer'), ['action' => 'delete', $customer->id], ['confirm' => __('Are you sure you want to delete # {0}?', $customer->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Customers'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Customer'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="customers view content">
            <h3><?= h($customer->full_name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Full Name') ?></th>
                    <td><?= h($customer->full_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Contact No') ?></th>
                    <td><?= h($customer->contact_no) ?></td>
                </tr>
                <tr>
                    <th><?= __('Phone Model') ?></th>
                    <td><?= h($customer->phone_model) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($customer->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($customer->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($customer->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Customer Info') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($customer->customer_info)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Phone Issue') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($customer->phone_issue)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Diagnostic') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($customer->diagnostic)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Suggested Part Replacement') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($customer->suggested_part_replacement)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Devices') ?></h4>
                <?php if (!empty($customer->devices)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Brand') ?></th>
                            <th><?= __('Model') ?></th>
                            <th><?= __('Imei') ?></th>
                            <th><?= __('Issue Description') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Priority Level') ?></th>
                            <th><?= __('Date Received') ?></th>
                            <th><?= __('Date Released') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($customer->devices as $device) : ?>
                        <tr>
                            <td><?= h($device->id) ?></td>
                            <td><?= h($device->brand) ?></td>
                            <td><?= h($device->model) ?></td>
                            <td><?= h($device->imei) ?></td>
                            <td><?= h($device->issue_description) ?></td>
                            <td><?= h($device->status) ?></td>
                            <td><?= h($device->priority_level) ?></td>
                            <td><?= h($device->date_received) ?></td>
                            <td><?= h($device->date_released) ?></td>
                            <td><?= h($device->created) ?></td>
                            <td><?= h($device->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Devices', 'action' => 'view', $device->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Devices', 'action' => 'edit', $device->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Devices', 'action' => 'delete', $device->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $device->id),
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