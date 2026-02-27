<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RepairPart Entity
 *
 * @property int $id
 * @property int $device_id
 * @property int $part_id
 * @property int|null $quantity_used
 *
 * @property \App\Model\Entity\Device $device
 * @property \App\Model\Entity\Part $part
 */
class RepairPart extends Entity
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
        'device_id' => true,
        'part_id' => true,
        'quantity_used' => true,
        'device' => true,
        'part' => true,
    ];
}