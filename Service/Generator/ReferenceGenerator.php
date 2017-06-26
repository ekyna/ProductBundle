<?php

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class ReferenceGenerator
 * @package Ekyna\Bundle\ProductBundle\Service\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ReferenceGenerator implements ReferenceGeneratorInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var int
     */
    protected $length;


    /**
     * Constructor.
     *
     * @param string $filePath The reference file path
     * @param string $prefix   The reference prefix
     * @param int    $length   The total reference length
     */
    public function __construct($filePath, $prefix = 'ym', $length = 10)
    {
        $this->filePath = $filePath;
        $this->prefix = $prefix;
        $this->length = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ProductInterface $product)
    {
        if (null !== $product->getReference()) {
            return $this;
        }

        $reference = $this->readReference();

        $reference = $this->generateReference($reference);

        $this->writeReference($reference);

        $product->setReference($reference);

        return $this;
    }

    /**
     * Generates the reference.
     *
     * @param string $reference
     *
     * @return string
     */
    protected function generateReference($reference)
    {
        $datePrefix = (new \DateTime())->format($this->prefix);

        if (0 !== strpos($reference, $datePrefix)) {
            $reference = 0;
        } else {
            $reference = intval(substr($reference, strlen($datePrefix)));
        }

        return $datePrefix . str_pad($reference + 1, $this->length - strlen($datePrefix), '0', STR_PAD_LEFT);
    }

    /**
     * Reads the previous reference.
     *
     * @return bool|string
     */
    private function readReference()
    {
        // Open
        if (false === $this->handle = fopen($this->filePath, 'c+')) {
            throw new RuntimeException("Failed to open file {$this->filePath}.");
        }
        // Exclusive lock
        if (!flock($this->handle, LOCK_EX)) {
            throw new RuntimeException("Failed to lock file {$this->filePath}.");
        }

        return fread($this->handle, $this->length);
    }

    /**
     * Writes the previous reference.
     *
     * @param string $reference
     */
    private function writeReference($reference)
    {
        // Truncate
        if (!ftruncate($this->handle, 0)) {
            throw new RuntimeException("Failed to truncate file {$this->filePath}.");
        }
        // Reset
        if (0 > fseek($this->handle, 0)) {
            throw new RuntimeException("Failed to move pointer at the beginning of the file {$this->filePath}.");
        }
        // Write
        if (!fwrite($this->handle, $reference)) {
            throw new RuntimeException("Failed to write file {$this->filePath}.");
        }
        // Flush
        if (!fflush($this->handle)) {
            throw new RuntimeException("Failed to flush file {$this->filePath}.");
        }
        // Unlock
        if (!flock($this->handle, LOCK_UN)) {
            throw new RuntimeException("Failed to unlock file {$this->filePath}.");
        }
        // Close
        fclose($this->handle);
    }
}
