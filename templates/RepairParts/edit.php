<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RepairPart $repairPart
 * @var string[]|\Cake\Collection\CollectionInterface $devices
 * @var string[]|\Cake\Collection\CollectionInterface $parts
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $repairPart->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $repairPart->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Repair Parts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="repairParts form content">
            <?= $this->Form->create($repairPart) ?>
            <fieldset>
                <legend><?= __('Edit Repair Part') ?></legend>
                <?php
                    echo $this->Form->control('device_id', ['options' => $devices]);
                    echo $this->Form->control('part_id', ['options' => $parts]);
                    echo $this->Form->control('quantity_used');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
