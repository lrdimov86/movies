<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\MoviesComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Component\MoviesComponent Test Case
 */
class MoviesComponentTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Controller\Component\MoviesComponent
     */
    protected $Movies;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->Movies = new MoviesComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Movies);

        parent::tearDown();
    }
}
