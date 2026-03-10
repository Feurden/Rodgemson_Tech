<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setPrimaryKey('id');
        $this->setDisplayField('username');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('username', 'Username is required.')
            ->minLength('username', 3, 'Username must be at least 3 characters.')
            ->maxLength('username', 50, 'Username cannot exceed 50 characters.')
            ->alphaNumeric('username', 'Username may only contain letters and numbers.');

        $validator
            ->notEmptyString('password', 'Password is required.')
            ->minLength('password', 8, 'Password must be at least 8 characters.');

        return $validator;
    }
}
