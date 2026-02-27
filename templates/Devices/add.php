<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Device $device
 * @var \Cake\Collection\CollectionInterface|string[] $customers
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Devices'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="devices form content">
            <?= $this->Form->create($device) ?>
            <fieldset>
                <legend><?= __('Add Device') ?></legend>
                <?php
                    echo $this->Form->control('customer_id', ['options' => $customers]);
                    echo $this->Form->control('brand');
                    echo $this->Form->control('model');
                    echo $this->Form->control('imei');
                    echo $this->Form->control('issue_description');
                    echo $this->Form->control('status');
                    echo $this->Form->control('priority_level');
                    echo $this->Form->control('date_received', ['empty' => true]);
                    echo $this->Form->control('date_released', ['empty' => true]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
