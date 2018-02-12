<?php
/**
 * Configuration Manager Test
 *
 * @package Slab
 * @subpackage Tests
 * @author Eric
 */
namespace Slab\Tests\Configuration;

class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test simple reading and overriding
     */
    public function testManager()
    {
        $_SERVER['SERVER_NAME'] = 'test-server.com';
        $configuration = new \Slab\Configuration\Configuration();
        $configuration->setCascadingSearchDirectories([
            __DIR__ . '/data/site1',
            __DIR__ . '/data/site2'
        ]);

        $manager = new \Slab\Configuration\Manager($configuration);

        $this->assertEquals(2, $manager->one);
        $this->assertEquals('my-site', $manager->site);
        $this->assertEquals('blargh', $manager->debug);
        $this->assertEquals(false, $manager->cleborgh);
        $this->assertEquals('geezer', $manager->helmet->value->option);
        $this->assertEquals(['chorizo', 'andouille', 'italian'], $manager->sausage);
    }
}