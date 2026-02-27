<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Part> $parts
 */
?>
<div class="parts index content">
    <?= $this->Html->link(__('New Part'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Parts') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('part_name') ?></th>
                    <th><?= $this->Paginator->sort('category') ?></th>
                    <th><?= $this->Paginator->sort('stock_quantity') ?></th>
                    <th><?= $this->Paginator->sort('minimum_stock') ?></th>
                    <th><?= $this->Paginator->sort('unit_price') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parts as $part): ?>
                <tr>
                    <td><?= $this->Number->format($part->id) ?></td>
                    <td><?= h($part->part_name) ?></td>
                    <td><?= h($part->category) ?></td>
                    <td><?= $part->stock_quantity === null ? '' : $this->Number->format($part->stock_quantity) ?></td>
                    <td><?= $part->minimum_stock === null ? '' : $this->Number->format($part->minimum_stock) ?></td>
                    <td><?= $part->unit_price === null ? '' : $this->Number->format($part->unit_price) ?></td>
                    <td><?= h($part->created) ?></td>
                    <td><?= h($part->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $part->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $part->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $part->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $part->id),
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