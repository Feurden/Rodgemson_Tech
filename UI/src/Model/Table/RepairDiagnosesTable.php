<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class RepairDiagnosesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('repair_diagnoses');
        $this->setPrimaryKey('id');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('job_id')
            ->maxLength('job_id', 50)
            ->requirePresence('job_id', 'create');

        $validator
            ->scalar('device')
            ->maxLength('device', 100);

        $validator
            ->scalar('ai_diagnosis')
            ->maxLength('ai_diagnosis', 255);

        $validator
            ->boolean('diagnosis_correct');

        return $validator;
    }
}
?>
