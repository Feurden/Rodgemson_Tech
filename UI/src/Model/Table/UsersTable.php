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
        // --- Username ---
        $validator
            ->notEmptyString('username', 'Username is required.')
            ->minLength('username', 3, 'Username must be at least 3 characters.')
            ->maxLength('username', 50, 'Username cannot exceed 50 characters.')
            ->alphaNumeric('username', 'Username may only contain letters and numbers.');

        // --- Password ---
        $validator
            ->notEmptyString('password', 'Password is required.')
            ->minLength('password', 8, 'Password must be at least 8 characters.');

        // --- Full Name ---
        $validator
            ->allowEmptyString('full_name')
            ->maxLength('full_name', 100, 'Full name cannot exceed 100 characters.');

        // --- Email ---
        $validator
            ->allowEmptyString('email')
            ->email('email', false, 'Please enter a valid email address.')
            ->maxLength('email', 150, 'Email cannot exceed 150 characters.');

        // --- Specialty ---
        $validator
            ->allowEmptyString('specialty')
            ->maxLength('specialty', 150, 'Specialty cannot exceed 150 characters.');

        // --- Avatar (initials, max 4 chars e.g. "JD") ---
        $validator
            ->allowEmptyString('avatar')
            ->maxLength('avatar', 4, 'Avatar must be 4 characters or fewer.');

        // --- Role ---
        $validator
            ->allowEmptyString('role')
            ->inList('role', ['admin', 'technician'], 'Role must be either admin or technician.');

        return $validator;
    }
}