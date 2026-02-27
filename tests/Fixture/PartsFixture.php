<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PartsFixture
 */
class PartsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'part_name' => 'Lorem ipsum dolor sit amet',
                'category' => 'Lorem ipsum dolor sit amet',
                'stock_quantity' => 1,
                'minimum_stock' => 1,
                'unit_price' => 1.5,
                'created' => '2026-02-27 00:27:57',
                'modified' => '2026-02-27 00:27:57',
            ],
        ];
        parent::init();
    }
}
