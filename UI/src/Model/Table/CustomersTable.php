<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CustomersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        $this->setTable('customers');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        
        $this->hasMany('Devices', [
            'foreignKey' => 'customer_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('full_name')
            ->maxLength('full_name', 150)
            ->requirePresence('full_name', 'create');

        $validator
            ->scalar('contact_no')
            ->maxLength('contact_no', 20);

        $validator
            ->scalar('phone_model')
            ->maxLength('phone_model', 100);

        $validator
            ->scalar('phone_issue')
            ->allowEmptyString('phone_issue');

        return $validator;
    }
}
?>
