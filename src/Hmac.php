<?php

/**
 * This file is part of the MessageX PHP SDK package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MessageX;

use MessageX\Credentials\Credentials;
use Psr\Http\Message\RequestInterface;

/**
 * Used for signing of the requests.
 *
 * Class Hmac
 * @package MessageX
 * @author Silvio Marijic <silvio.marijic@smsglobal.com>
 */
class Hmac
{
    /**
     *
     */
    const ALGORITHM_SHA1 = 'hmac-sha1';

    /**
     *
     */
    const ALGORITHM_SHA256 = 'hmac-sha256';

    /**
     *
     */
    const ALGORITHM_SHA512 = 'hmac-sha512';

    /**
     * Signs the request with the credentials.
     *
     * @param RequestInterface $request
     * @param Credentials $credentials
     * @return RequestInterface
     */
    public static function sign(RequestInterface $request, Credentials $credentials)
    {
        /**
         * @var RequestInterface $request
         */

        $signatureHeaders   = [];
        $base64Md5          = '';
        $contentLength      = '';

        if ($request->getBody()->getSize() !== 0) {
            $base64Md5 = Hmac::md5HashBase64(
                $request->getBody()
                    ->getContents()
            );

            $contentLength = strlen((string)$request->getBody());
            $signatureHeaders = array_merge(
                $signatureHeaders,
                [
                    'content-type'      => $request->getHeader('content-type'),
                    'content-md5'       => $base64Md5,
                    'content-length'    => $contentLength
                ]);
        }

        $parsedUrl = parse_url($request->getUri());
        $targetUrl = $parsedUrl["path"];
        if (!empty($parsedUrl["query"])) {
            $targetUrl = $targetUrl . "?" . $parsedUrl["query"];
        }

        $requestLine = $request->getMethod() . " " . $targetUrl . " HTTP/1.1";

        $signatureHeaders['date'] = $date = gmdate("D, d M Y H:i:s", time()) . " GMT";
        $signatureHeaders['request-line'] = $requestLine;

        $headersString      = Hmac::getHeadersString($signatureHeaders);
        $signatureString    = Hmac::getSignatureString($signatureHeaders);
        $signatureHash      = Hmac::sha1HashBase64($signatureString, $credentials->getSecret());
        $authHeader         = sprintf(
            'hmac username="%s", algorithm="%s", headers="%s", signature="%s"',
            $credentials->getAccessKey(),
            Hmac::ALGORITHM_SHA256,
            $headersString,
            $signatureHash
        );

        $request = $request->withHeader('Authorization', $authHeader)
            ->withHeader('Date', $date);

        if ($request->getBody()->getSize()) {
            return $request->withHeader('Content-Type', $request->getHeader('content-type'))
                ->withHeader('Content-MD5', $base64Md5)
                ->withHeader('Content-Length', $contentLength);
        }

        return $request;
    }

    /**
     * Base64 of the md5 hashed content of the request.
     *
     * @param string $data Content of the request.
     * @return string Base64 hash.
     */
    public static function md5HashBase64($data)
    {
        return base64_encode(md5($data, true));
    }

    /**
     * Base64 of signature hashed with HMAC using SHA1.
     *
     * @param string $signature String composed of specific headers.
     * @param string $secret Secret part of credentials.
     * @return string Base64 string.
     */
    public static function sha1HashBase64($signature, $secret)
    {
        return base64_encode(
            hash_hmac(Hmac::ALGORITHM_SHA256, $signature, $secret, true)
        );
    }

    /**
     * Builds signature string used for signing of the request.
     *
     * @param array $signatureHeaders Specific headers for signing request.
     * @return string String composed of signature headers.
     */
    public static function getSignatureString(array $signatureHeaders)
    {
        $signature = '';

        foreach ($signatureHeaders as $header => $value) {
            if ($signature !== '') {
                $signature .= "\n";
            }

            if (is_array($value)) {
                $value = $value[0];
            }

            $signature = mb_strtolower($header) === "request-line"
                ? "{$signature}{$value}"
                : $signature . mb_strtolower($header) . ": {$value}";
        }

        return $signature;
    }

    /**
     * @param array $signatureHeaders
     * @return string
     */
    public static function getHeadersString(array $signatureHeaders)
    {
        $headers = '';

        foreach ($signatureHeaders as $header => $value) {
            if ($headers !== '') {
                $headers .= ' ';
            }

            $headers .= $header;
        }

        return $headers;
    }
}
