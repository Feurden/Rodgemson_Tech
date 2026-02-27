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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $part->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $part->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Parts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="parts form content">
            <?= $this->Form->create($part) ?>
            <fieldset>
                <legend><?= __('Edit Part') ?></legend>
                <?php
                    echo $this->Form->control('part_name');
                    echo $this->Form->control('category');
                    echo $this->Form->control('stock_quantity');
                    echo $this->Form->control('minimum_stock');
                    echo $this->Form->control('unit_price');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
