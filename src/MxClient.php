<?php

/**
 * This file is part of the MessageX PHP SDK package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MessageX;

use Doctrine\Common\Annotations\AnnotationRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use MessageX\Exception\Service\InvalidServiceCall;
use MessageX\Middleware\SignatureMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class MxClient
 *
 * Base client class for all service specific clients.
 * Handles all of the communication to the API.
 *
 * @package MessageX
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
abstract class MxClient
{
    /**
     * Provides credentials for API from various sources.
     *
     * @var callable Callable to return client credentials
     */
    private $credentialsProvider;

    /**
     * Configuration for the client. Contains API descriptor with definitions
     * of all endpoints.
     *
     * @var array Configuration with client provided and default values.
     */
    private $config;

    /**
     * Instance of http client used to communicate with the API.
     * @var Client
     */
    private $client;

    /**
     * Instance of JMS Serializer, used to serialize input and deserialize response
     * into objects when necessary.
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * MxClient constructor.
     * @param array $args User specified arguments.
     * @throws Exception\InvalidDescriptor
     */
    public function __construct(array $args = [])
    {
        /*
         * If no service is provided in the arguments, resolve service name based on client
         * class name.
         */
        if (! isset($args['service'])) {
            $args['service'] = Functions::descriptor(
                $this->getServiceDescriptorPath()
            );
        }

        AnnotationRegistry::registerLoader('class_exists');

        $handlerStack               = HandlerStack::create();
        $this->config               = (new Arguments())->resolve($args);
        $this->credentialsProvider  = $this->config['credentials'];
        $this->serializer           = SerializerBuilder::create()
            ->setPropertyNamingStrategy(
                new SerializedNameAnnotationStrategy(
                    new IdenticalPropertyNamingStrategy()
                ))
            ->build();

        $this->addSignatureMiddleware($handlerStack, $this->credentialsProvider);

        $this->client = new Client(
            [
                'handler'   => $handlerStack,
                'base_uri'  => $this->config['service']['host']
            ]
        );
    }

    /**
     * Methods that represent action on the API are not directly implemented. This method is used
     * to capture all of those calls. Loads service definition from descriptor file and sends request
     * to defined endpoint. Performs serialization of input and deserialization of response from API.
     *
     * @param string $name Name of the called method.
     * @param array $arguments Arguments passed to the method.
     * @return ResponseInterface|PromiseInterface For async calls returns ResponseInterface otherwise PromiseInterface.
     * @throws InvalidServiceCall
     * @throws Throwable
     */
    final public function __call($name, $arguments)
    {
        $name    = ucfirst($name);
        $async   = 'Async' === substr($name, -5);
        $name    = $async? substr($name, 0, strlen($name) - 5) : $name;
        $version = $this->config['service']['version'];

        if (! array_key_exists($name, $this->config['service']['endpoints'])) {
            throw new InvalidServiceCall($name);
        }

        $definition = $this->config['service']['endpoints'][$name];

        $body = $this->serializer
            ->serialize($arguments[0], 'json');

        $promise = new Promise(
            function () use ($definition, $body, &$promise, $version) {
                /**
                 * @var PromiseInterface $promise
                 */
                $this->sendAsync(
                    new Request(
                        $definition['method'],
                        "{$version}{$definition['requestUri']}",
                        ['Content-Type' => 'application/json'],
                        $body))
                    ->then(
                        function (ResponseInterface $response) use ($definition, $promise) {
                            if (! array_key_exists($response->getStatusCode(), $definition['response'])) {
                                return $response;
                            }

                            $mapping = $definition['response'][$response->getStatusCode()];

                            return $promise->resolve($this->serializer->deserialize(
                                $response->getBody()->getContents(),
                                $mapping['type'],
                                'json'));
                        })->wait();
            }
        );

        return $async
            ? $promise
            : $promise->wait();
    }

    /**
     * Performs synchronous call to the API.
     *
     * @param RequestInterface $request
     * @return ResponseInterface Response from API.
     * @deprecated
     */
    public function send(RequestInterface $request)
    {
        return $this->client
            ->send($request);
    }

    /**
     * Performs asynchronous call to the API.
     *
     * @param RequestInterface $request
     * @return PromiseInterface Promise that will be completed upon response from API.
     */
    public function sendAsync(RequestInterface $request)
    {
        return $this->client
            ->sendAsync($request);
    }

    /**
     * Adds signing middleware to Guzzle's HandlerStack in order to sign all of the
     * requests using HMAC.
     *
     * @param HandlerStack $handlerStack Instance of HandlerStack
     * @param callable $credentialsProvider Credential provider provides credentials used in request signing.
     */
    private function addSignatureMiddleware(HandlerStack $handlerStack, callable $credentialsProvider)
    {
        $handlerStack->push(SignatureMiddleware::sign($credentialsProvider));
    }

    /**
     * @return string
     */
    abstract protected function getServiceDescriptorPath();
}
