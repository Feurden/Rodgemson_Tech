<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Device Entity
 *
 * @property int $id
 * @property int $customer_id
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $imei
 * @property string|null $issue_description
 * @property string|null $status
 * @property string|null $priority_level
 * @property \Cake\I18n\DateTime|null $date_received
 * @property \Cake\I18n\DateTime|null $date_released
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Customer $customer
 * @property \App\Model\Entity\RepairPart[] $repair_parts
 */
class Device extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'customer_id' => true,
        'brand' => true,
        'model' => true,
        'imei' => true,
        'issue_description' => true,
        'status' => true,
        'priority_level' => true,
        'date_received' => true,
        'date_released' => true,
        'created' => true,
        'modified' => true,
        'customer' => true,
        'repair_parts' => true,
    ];
}
