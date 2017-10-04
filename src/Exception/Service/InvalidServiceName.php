<?php

/**
 * This file is part of the MessageX PHP SDK package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MessageX\Exception\Service;

use MessageX\Exception\MxException;

/**
 * Class BadServiceName
 * @package MessageX\Exception
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
class InvalidServiceName extends MxException
{
    /**
     * BadServiceName constructor.
     * @param string $service
     */
    public function __construct($service)
    {
        parent::__construct(
            sprintf("Service must be of type string, %s given", gettype($service))
        );
    }
}
