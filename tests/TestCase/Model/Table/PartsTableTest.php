<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PartsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PartsTable Test Case
 */
class PartsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PartsTable
     */
    protected $Parts;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Parts',
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
        $config = $this->getTableLocator()->exists('Parts') ? [] : ['className' => PartsTable::class];
        $this->Parts = $this->getTableLocator()->get('Parts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Parts);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PartsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
