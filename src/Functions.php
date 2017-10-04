<?php

/**
 * This file is part of the MessageX PHP SDK package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MessageX;

use MessageX\Exception\InvalidDescriptor;

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
     * @param string $path Path to service descriptor file.
     * @return array Service(s) descriptor.
     * @throws InvalidDescriptor
     */
    public static function descriptor($path)
    {
        static $descriptor = [];

        if (empty($descriptor)) {
            $descriptor = json_decode(
                file_get_contents($path),
                true
            );
        }

        if (null === $descriptor) {
            throw new InvalidDescriptor(
                sprintf('Descriptor at %s is invalid', $path)
            );
        }

        return $descriptor['service'];
    }
}
