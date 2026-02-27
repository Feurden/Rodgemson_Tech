<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Devices Model
 *
 * @property \App\Model\Table\CustomersTable&\Cake\ORM\Association\BelongsTo $Customers
 * @property \App\Model\Table\RepairPartsTable&\Cake\ORM\Association\HasMany $RepairParts
 *
 * @method \App\Model\Entity\Device newEmptyEntity()
 * @method \App\Model\Entity\Device newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Device> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Device get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Device findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Device patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Device> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Device|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Device saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Device>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Device>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Device>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Device> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Device>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Device>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Device>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Device> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DevicesTable extends Table
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

        $this->setTable('devices');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('RepairParts', [
            'foreignKey' => 'device_id',
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
            ->integer('customer_id')
            ->notEmptyString('customer_id');

        $validator
            ->scalar('brand')
            ->maxLength('brand', 100)
            ->allowEmptyString('brand');

        $validator
            ->scalar('model')
            ->maxLength('model', 100)
            ->allowEmptyString('model');

        $validator
            ->scalar('imei')
            ->maxLength('imei', 50)
            ->allowEmptyString('imei');

        $validator
            ->scalar('issue_description')
            ->allowEmptyString('issue_description');

        $validator
            ->scalar('status')
            ->allowEmptyString('status');

        $validator
            ->scalar('priority_level')
            ->allowEmptyString('priority_level');

        $validator
            ->dateTime('date_received')
            ->allowEmptyDateTime('date_received');

        $validator
            ->dateTime('date_released')
            ->allowEmptyDateTime('date_released');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['customer_id'], 'Customers'), ['errorField' => 'customer_id']);

        return $rules;
    }
}
