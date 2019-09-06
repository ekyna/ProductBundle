<?php

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Common\Generator\AbstractGenerator;

/**
 * Class Gtin13Generator
 * @package Ekyna\Bundle\ProductBundle\Service\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Gtin13Generator extends AbstractGenerator implements GtinGeneratorInterface
{
    /**
     * @var string
     */
    private $manufacturerCode;


    /**
     * Constructor.
     *
     * @param string $path
     * @param bool   $debug
     */
    public function __construct(string $path, bool $debug = false)
    {
        parent::__construct($path, 13, '', $debug);
    }

    /**
     * @inheritDoc
     */
    public function setManufacturerCode(string $code): void
    {
        $this->manufacturerCode = $code;
    }

    /**
     * Generates the product gtin 13 code.
     *
     * @param object $product
     *
     * @return string
     */
    public function generate(object $product): string
    {
        if (empty($this->manufacturerCode)) {
            throw new RuntimeException("Manufacturer code is not configured");
        }

        $productCode = $this->storage->read();

        $productCode = $this->increment($productCode);

        $gtin = $this->build($productCode);

        $this->storage->write($productCode);

        return $gtin;
    }

    /**
     * Increments the product code.
     *
     * @param string $code
     *
     * @return string
     */
    protected function increment(string $code): string
    {
        $code = intval($code);

        if ($this->debug && 9999 > $code) {
            $code = 9999;
        }

        return str_pad($code + 1, 12 - strlen($this->manufacturerCode), '0', STR_PAD_LEFT);
    }

    /**
     * Builds the gtin 13 code by adding control digit.
     *
     * @param string $productCode
     *
     * @return string
     */
    protected function build(string $productCode): string
    {
        $gtin = $this->manufacturerCode . $productCode;

        if (12 !== strlen($gtin)) {
            throw new UnexpectedValueException("Expected 12 length code");
        }

        $even = false;
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$gtin[$i];
            $sum += $even ? ($digit * 3) : $digit;
            $even = !$even;
        }

        if (0 < $digit = $sum % 10) {
            $digit = 10 - $digit;
        }

        return $gtin . $digit;
    }
}
