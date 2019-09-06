<?php

namespace Ekyna\Component\Commerce\Tests\Common\Generator;

use Ekyna\Bundle\ProductBundle\Entity\ProductReference;
use Ekyna\Bundle\ProductBundle\Repository\ProductReferenceRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Generator\Gtin13Generator;
use Ekyna\Component\Commerce\Common\Generator\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class Gtin13GeneratorTest
 * @package Ekyna\Component\Commerce\Tests\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Gtin13GeneratorTest extends TestCase
{
    /** @var string */
    private static $path;

    /** @var ProductReferenceRepositoryInterface|MockObject */
    private $repository;

    /** @var Gtin13Generator */
    private $generator;

    public static function setUpBeforeClass(): void
    {
        self::$path = sys_get_temp_dir() . '/storage';
    }

    protected function setUp(): void
    {
        if (is_file(self::$path)) {
            unlink(self::$path);
        }

        $this->repository = $this->createMock(ProductReferenceRepositoryInterface::class);
        $this->repository->method('createNew')->willReturn(new ProductReference());

        $this->generator = new Gtin13Generator(self::$path);
        $this->generator->setManufacturerCode('3760222');
    }

    protected function tearDown(): void
    {
        $this->generator = null;
        $this->repository = null;
    }

    public function test_generator(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('read')->willReturn('97141');
        $this->generator->setStorage($storage);

        $product = new \stdClass();

        // (TODO) Empty file
        $this->assertEquals('3760222971423', $this->generator->generate($product));

        $storage = $this->createMock(StorageInterface::class);
        $storage->method('read')->willReturn('97142');
        $this->generator->setStorage($storage);

        // File has previous number
        $this->assertEquals('3760222971430', $this->generator->generate($product));
    }
}