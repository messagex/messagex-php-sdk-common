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
 * Class InvalidCall
 * @package MessageX\Exception
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
class InvalidServiceCall extends MxException
{
    /**
     * InvalidCall constructor.
     * @param string $call
     */
    public function __construct($call)
    {
        parent::__construct(
            sprintf('Requested endpoint %s is not provided by MessageX PHP SDK', lcfirst($call))
        );
    }
}
