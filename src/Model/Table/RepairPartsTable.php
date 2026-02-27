<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RepairParts Model
 *
 * @property \App\Model\Table\DevicesTable&\Cake\ORM\Association\BelongsTo $Devices
 * @property \App\Model\Table\PartsTable&\Cake\ORM\Association\BelongsTo $Parts
 *
 * @method \App\Model\Entity\RepairPart newEmptyEntity()
 * @method \App\Model\Entity\RepairPart newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\RepairPart> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RepairPart get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RepairPart findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\RepairPart patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\RepairPart> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RepairPart|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\RepairPart saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\RepairPart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RepairPart>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RepairPart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RepairPart> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RepairPart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RepairPart>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RepairPart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RepairPart> deleteManyOrFail(iterable $entities, array $options = [])
 */
class RepairPartsTable extends Table
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

        $this->setTable('repair_parts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Devices', [
            'foreignKey' => 'device_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Parts', [
            'foreignKey' => 'part_id',
            'joinType' => 'INNER',
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
            ->integer('device_id')
            ->notEmptyString('device_id');

        $validator
            ->integer('part_id')
            ->notEmptyString('part_id');

        $validator
            ->integer('quantity_used')
            ->allowEmptyString('quantity_used');

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
        $rules->add($rules->existsIn(['device_id'], 'Devices'), ['errorField' => 'device_id']);
        $rules->add($rules->existsIn(['part_id'], 'Parts'), ['errorField' => 'part_id']);

        return $rules;
    }
}
