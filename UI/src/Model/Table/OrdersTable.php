<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class OrdersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('orders');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('part_name')
            ->maxLength('part_name', 150)
            ->requirePresence('part_name', 'create');

        $validator
            ->scalar('customer_name')
            ->maxLength('customer_name', 150)
            ->requirePresence('customer_name', 'create');

        $validator
            ->scalar('phone_model')
            ->maxLength('phone_model', 100)
            ->requirePresence('phone_model', 'create');

        $validator
            ->integer('quantity');

        return $validator;
    }
}