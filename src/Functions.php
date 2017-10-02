<?php

/**
 * This file is part of the MessageX PHP SDK package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MessageX;

use MessageX\Exception\Service\InvalidServiceName;
use MessageX\Exception\Service\UnknownService;

/**
 * Helper functions.
 *
 * Class Functions
 * @package MessageX
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
class Functions
{
    /**
     * Loads information about services from descriptor file.
     *
     * @param string $path
     * @param null $service Service name or null for all services.
     * @return array Service(s) descriptor.
     * @throws InvalidServiceName
     * @throws UnknownService
     */
    public static function descriptor($path, $service = null)
    {
        static $descriptor = [];

        if (empty($descriptor)) {
            $descriptor = json_decode(
                file_get_contents($path),
                true
            );
        }

        if (! is_string($service)) {
            throw new InvalidServiceName($service);
        }

        if (null !== $service) {
            if (! array_key_exists($service, $descriptor)) {
                throw new UnknownService($service);
            }

            return $descriptor[$service];
        }

        return $descriptor;
    }
}
