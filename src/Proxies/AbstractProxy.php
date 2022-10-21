<?php

namespace ImapConnector\Proxies;

use ImapConnector\Containers\ResponseContainerInterface;
use ImapConnector\Containers\ResponseContainer;

abstract class AbstractProxy implements ProxyInterface {
    protected $host;
    protected $port;
    protected $proxy_user;
    protected $proxy_pass;

    protected $timeOut;
    protected $context;

    /**
     * @var ResponseContainerInterface|ResponseContainer
     */
    protected $responseContainer;

    protected $stream;


    public function __construct(ResponseContainerInterface $responseContainer, $host, $port, $proxy_user, $proxy_pass, $context = null) {
        $this->responseContainer = $responseContainer;
        $this->host = $host;
        $this->port = $port;

        $this->proxy_user = $proxy_user;
        $this->proxy_pass = $proxy_pass;

        $this->proxyUserPass = base64_encode("{$proxy_user}:{$proxy_pass}");

        if (!$context)
            $this->context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
    }

    protected function createStream($host, $port) {
        //@todo proxy authentication
        if ($this->stream = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $this->timeOut, STREAM_CLIENT_CONNECT, $this->context)) {
            stream_set_timeout($this->stream, $this->timeOut);

            return $this->stream;
        }

        $this->responseContainer->setLastErrorText("Failed connecting to proxy. " . $errstr);

        return false;
    }
}
