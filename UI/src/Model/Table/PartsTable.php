<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class PartsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        $this->setTable('parts');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        
        $this->hasMany('RepairParts', [
            'foreignKey' => 'part_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('part_name')
            ->maxLength('part_name', 150)
            ->requirePresence('part_name', 'create');

        $validator
            ->scalar('category')
            ->maxLength('category', 100);

        $validator
            ->integer('stock_quantity');

        $validator
            ->integer('minimum_stock');

        $validator
            ->decimal('unit_price');

        return $validator;
    }
}
?>
