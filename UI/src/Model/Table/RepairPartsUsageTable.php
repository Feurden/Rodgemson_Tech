<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class RepairPartsUsageTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('repair_parts_usage');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Belongs to a device
        $this->belongsTo('Devices', [
            'foreignKey' => 'device_id',
        ]);

        // Belongs to a part
        $this->belongsTo('Parts', [
            'foreignKey' => 'part_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('device_id')
            ->requirePresence('device_id', 'create')
            ->notEmptyString('device_id');

        $validator
            ->integer('part_id')
            ->requirePresence('part_id', 'create')
            ->notEmptyString('part_id');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmptyString('quantity');

        $validator
            ->boolean('returned')
            ->notEmptyString('returned');

        return $validator;
    }
}