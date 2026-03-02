<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class DevicesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        $this->setTable('devices');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        
        // Relationship
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'LEFT',
        ]);
        
        $this->hasMany('RepairParts', [
            'foreignKey' => 'device_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('brand')
            ->maxLength('brand', 100);

        $validator
            ->scalar('model')
            ->maxLength('model', 100);

        $validator
            ->scalar('imei')
            ->maxLength('imei', 50);

        $validator
            ->scalar('issue_description')
            ->allowEmptyString('issue_description');

        $validator
            ->scalar('status')
            ->inList('status', ['Pending', 'In Progress', 'Waiting Parts', 'Completed', 'Released']);

        $validator
            ->scalar('priority_level')
            ->inList('priority_level', ['Low', 'Medium', 'High']);

        return $validator;
    }

    public function buildRules(\Cake\ORM\RulesChecker $rules): \Cake\ORM\RulesChecker
    {
        $rules->add($rules->existsIn('customer_id', 'Customers'), ['errorField' => 'customer_id']);
        return $rules;
    }
}
?>
