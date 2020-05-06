<?php

namespace Ekyna\Bundle\ProductBundle\Exception;

/**
 * Class UnexpectedTypeException
 * @package Ekyna\Bundle\ProductBundle\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnexpectedTypeException extends \UnexpectedValueException implements ProductExceptionInterface
{
    /**
     * Constructor.
     *
     * @param mixed           $value
     * @param string|string[] $types
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($value, $types, $code = 0, \Throwable $previous = null)
    {
        $types = (array)$types;

        if (1 === $length = count($types)) {
            $types = $types[0];
        } else {
            $types = implode(', ', array_slice($types, 0, $length - 2)) . ' or ' . $types[$length - 1];
        }

        parent::__construct(sprintf("Expected %s, got %s", $types, gettype($value)), $code, $previous);
    }
}
