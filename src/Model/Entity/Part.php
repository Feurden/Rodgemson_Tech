<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Part Entity
 *
 * @property int $id
 * @property string $part_name
 * @property string|null $category
 * @property int|null $stock_quantity
 * @property int|null $minimum_stock
 * @property string|null $unit_price
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\RepairPart[] $repair_parts
 */
class Part extends Entity
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
        'part_name' => true,
        'category' => true,
        'stock_quantity' => true,
        'minimum_stock' => true,
        'unit_price' => true,
        'created' => true,
        'modified' => true,
        'repair_parts' => true,
    ];
}
