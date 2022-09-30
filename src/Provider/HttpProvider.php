<?php

namespace Ian\PayNow\Provider;

use GuzzleHttp\{Psr7\Request, Client, ClientInterface};
use Ian\PayNow\Support\Utils;
use Ian\PayNow\Exception\HttpProviderException;

class HttpProvider
{
    /**
     * HTTP Client Handler
     *
     * @var ClientInterface.
     */
    protected $httpClient;

    /**
     * Server Url
     *
     * @var string
    */
    protected $url;

    /**
     * Bot Api Token
     *
     * @var string
    */
    protected $token;

    /**
     * Timeout
     *
     * @var int
     */
    protected $timeout = 5000;

    /**
     * Get custom headers
     *
     * @var array
    */
    protected $headers = [];

    /**
     * Code message
     *
     * @var array
    */
    public $codes = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    /**
     * 初始化
     *
     * @param string $url
     * 
     * @return void
     * 
     * @throws \Ian\PayNow\Exception\HttpProviderException
     */
    public function __construct($url)
    {
        if (!Utils::isValidUrl($url)) {
            throw new HttpProviderException('Invalid URL provided to HttpProvider');
        }
        
        $this->url = $url;
        $this->headers = $headers;
        
        $this->httpClient = new Client([
            'timeout' =>  $this->timeout
        ]);
    }

    /**
     * 請求 xml
     *
     * @param $url
     * @param array $payload
     * @param string $method
     * 
     * @return string
     */
    public function requestXml($method, $data)
    {
        $request = new Request(strtoupper($method), $this->url, [
            'Content-Type' => 'application/soap+xml;charset=utf-8',
            'Content-Length' => strlen($data)
        ], $data);
        $response = $this->httpClient->send($request);

        // xml parse
        return @\DOMDocument::loadXML($response->getBody()->getContents())->textContent;
    }
}