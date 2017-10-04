<?php

/**
 * This file is part of the MessageX PHP SDK package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MessageX;

use MessageX\Credentials\CredentialProvider;
use MessageX\Credentials\Credentials;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Validation of the client provided configuration.
 *
 * Class ArgsResolver
 * @package MessageX
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
class Arguments
{
    /**
     * @var OptionsResolver Instance of OptionsResolver component.
     */
    private $resolver;

    /**
     * ClientResolver constructor.
     */
    public function __construct()
    {
        /*
         * Create resolver for the arguments passed to client. Add type checks.
         * Add normalizers for options when needed. In case of credentials, they
         * can be provided in multiple ways to the client, but we need to normalize
         * that to callable for internal use.
         */
        $this->resolver = new OptionsResolver();
        $this->resolver
            ->setDefined(['credentials', 'service'])
            ->setAllowedTypes('service', 'array')
            ->setAllowedTypes('credentials', ['callable', 'array', 'object', 'bool'])
            ->setNormalizer(
                'credentials',
                function (Options $options, $credentials) {
                    if (is_callable($credentials)) {
                        return $credentials;
                    }

                    if ($credentials instanceof Credentials) {
                        return CredentialProvider::fromCredentials($credentials);
                    }

                    if (is_array($credentials) && isset($credentials['key']) && isset($credentials['secret'])) {
                        return CredentialProvider::fromCredentials(
                            new Credentials(
                                $credentials['key'],
                                $credentials['secret']
                            )
                        );
                    }

                    if ($credentials === false) {
                        return CredentialProvider::fromCredentials(
                            new Credentials('', '')
                        );
                    }
                }
            );
    }

    /**
     * @param array $args Client provided configuration.
     * @return array Resolved configuration after validation and normalization.
     */
    public function resolve(array $args)
    {
        return $this->resolver
            ->resolve($args);
    }
}
