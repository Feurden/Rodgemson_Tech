<?php
// src/Model/Table/ServicesTable.php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ServicesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        $this->setTable('services');
        $this->setDisplayField('service_name');
        $this->setPrimaryKey('id');
        
        $this->addBehavior('Timestamp');
        
        $this->hasMany('RepairServicesUsage', [
            'foreignKey' => 'service_id',
        ]);
    }
    
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('service_name')
            ->maxLength('service_name', 150);
            
        $validator
            ->decimal('price')
            ->greaterThanOrEqual('price', 0);
            
        return $validator;
    }
}