<?php
// src/Model/Table/RepairServicesUsageTable.php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class RepairServicesUsageTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        $this->setTable('repair_services_usage');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        
        $this->addBehavior('Timestamp');
        
        $this->belongsTo('Devices', [
            'foreignKey' => 'device_id',
        ]);
        
        $this->belongsTo('Services', [
            'foreignKey' => 'service_id',
        ]);
    }
}