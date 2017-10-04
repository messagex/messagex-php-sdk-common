<?php

/**
 * This file is part of the MessageX PHP SDK package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MessageX\Credentials;

use GuzzleHttp\Promise;

/**
 * Class CredentialProvider
 * @package MessageX\Credentials
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
class CredentialProvider
{
    /**
     * @param Credentials $credentials
     * @return callable
     */
    public static function fromCredentials(Credentials $credentials)
    {
        $promise = Promise\promise_for($credentials);

        return function () use ($promise) {
            return $promise;
        };
    }
}
