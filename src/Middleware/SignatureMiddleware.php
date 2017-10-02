<?php

/**
 * This file is part of the MessageX PHP SDK package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MessageX\Middleware;

use MessageX\Credentials\Credentials;
use MessageX\Hmac;
use Psr\Http\Message\RequestInterface;

/**
 * Middleware used to sign all requests.
 *
 * Class SignatureMiddleware
 * @package MessageX\Middlerware
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
class SignatureMiddleware
{
    /**
     * @param callable $credentialsProvider
     * @return callable
     */
    public static function sign(callable $credentialsProvider)
    {
        return function (callable $handler) use ($credentialsProvider) {
            return function (RequestInterface $request, array $options) use ($handler, $credentialsProvider) {
                return ($credentialsProvider)()->then(
                    function (Credentials $credentials) use ($handler, $request, $options) {
                        $request = Hmac::sign($request, $credentials);

                        return $handler($request, $options);
                    }
                );
            };
        };
    }
}
