<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * OrdersFixture
 */
class OrdersFixture extends TestFixture
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
                'quantity' => 1,
                'status' => 'Lorem ipsum dolor sit amet',
                'created' => '2026-02-27 00:27:42',
                'modified' => '2026-02-27 00:27:42',
            ],
        ];
        parent::init();
    }
}
