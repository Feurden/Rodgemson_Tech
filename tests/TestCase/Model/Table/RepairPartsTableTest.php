<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RepairPartsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RepairPartsTable Test Case
 */
class RepairPartsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RepairPartsTable
     */
    protected $RepairParts;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.RepairParts',
        'app.Devices',
        'app.Parts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('RepairParts') ? [] : ['className' => RepairPartsTable::class];
        $this->RepairParts = $this->getTableLocator()->get('RepairParts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->RepairParts);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\RepairPartsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\RepairPartsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
