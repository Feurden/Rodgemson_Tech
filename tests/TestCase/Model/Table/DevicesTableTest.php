<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DevicesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DevicesTable Test Case
 */
class DevicesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DevicesTable
     */
    protected $Devices;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Devices',
        'app.Customers',
        'app.RepairParts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Devices') ? [] : ['className' => DevicesTable::class];
        $this->Devices = $this->getTableLocator()->get('Devices', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Devices);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\DevicesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\DevicesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
