<?php

/**
 * This file is part of the MessageX PHP SDK package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MessageX\Credentials;

/**
 * Client credentials.
 *
 * Class Credentials
 * @package MessageX\Credentials
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
class Credentials
{
    /**
     * @var string Api key part of credentials.
     */
    private $key;

    /**
     * @var string Secret part of the credentials.
     */
    private $secret;

    /**
     * Credentials constructor.
     * @param string $key Api key part of credentials.
     * @param string $secret Secret part of the credentials.
     */
    public function __construct($key, $secret)
    {
        $this->key      = trim($key);
        $this->secret   = trim($secret);
    }

    /**
     * @return string Api key part of credentials.
     */
    public function getAccessKey()
    {
        return $this->key;
    }

    /**
     * @return string Secret part of the credentials.
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'key'     => $this->key,
            'secret'  => $this->secret
        ];
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode($this->toArray());
    }
}
