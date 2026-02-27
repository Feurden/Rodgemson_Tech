<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Customer Entity
 *
 * @property int $id
 * @property string $full_name
 * @property string|null $customer_info
 * @property string|null $contact_no
 * @property string|null $phone_model
 * @property string|null $phone_issue
 * @property string|null $diagnostic
 * @property string|null $suggested_part_replacement
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Device[] $devices
 */
class Customer extends Entity
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
        'full_name' => true,
        'customer_info' => true,
        'contact_no' => true,
        'phone_model' => true,
        'phone_issue' => true,
        'diagnostic' => true,
        'suggested_part_replacement' => true,
        'created' => true,
        'modified' => true,
        'devices' => true,
    ];
}
