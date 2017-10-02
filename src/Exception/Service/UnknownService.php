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
 * Class UnknownService
 * @package MessageX\Exception
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
class UnknownService extends MxException
{
    /**
     * BadServiceName constructor.
     * @param string $service
     */
    public function __construct($service)
    {
        parent::__construct(
            sprintf("Service %s is not found", $service)
        );
    }
}
