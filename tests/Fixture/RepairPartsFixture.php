<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RepairPartsFixture
 */
class RepairPartsFixture extends TestFixture
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
                'device_id' => 1,
                'part_id' => 1,
                'quantity_used' => 1,
            ],
        ];
        parent::init();
    }
}
