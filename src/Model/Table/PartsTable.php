<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Parts Model
 *
 * @property \App\Model\Table\RepairPartsTable&\Cake\ORM\Association\HasMany $RepairParts
 *
 * @method \App\Model\Entity\Part newEmptyEntity()
 * @method \App\Model\Entity\Part newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Part> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Part get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Part findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Part patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Part> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Part|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Part saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Part>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Part>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Part>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Part> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Part>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Part>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Part>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Part> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PartsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('parts');
        $this->setDisplayField('part_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('RepairParts', [
            'foreignKey' => 'part_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('part_name')
            ->maxLength('part_name', 150)
            ->requirePresence('part_name', 'create')
            ->notEmptyString('part_name');

        $validator
            ->scalar('category')
            ->maxLength('category', 100)
            ->allowEmptyString('category');

        $validator
            ->integer('stock_quantity')
            ->allowEmptyString('stock_quantity');

        $validator
            ->integer('minimum_stock')
            ->allowEmptyString('minimum_stock');

        $validator
            ->decimal('unit_price')
            ->allowEmptyString('unit_price');

        return $validator;
    }
}
