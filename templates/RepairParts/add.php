<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\RepairPart $repairPart
 * @var \Cake\Collection\CollectionInterface|string[] $devices
 * @var \Cake\Collection\CollectionInterface|string[] $parts
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Repair Parts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="repairParts form content">
            <?= $this->Form->create($repairPart) ?>
            <fieldset>
                <legend><?= __('Add Repair Part') ?></legend>
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
