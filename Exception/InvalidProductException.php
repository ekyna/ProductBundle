<?php

namespace Ekyna\Bundle\ProductBundle\Exception;

use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;

/**
 * Class InvalidProductException
 * @package Ekyna\Bundle\ProductBundle\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvalidProductException extends \Exception implements CommerceExceptionInterface
{

}
