<?php

namespace ImapConnector;

use ImapConnector\Containers\ResponseContainerInterface;
use ImapConnector\Proxies\ProxyInterface;
use ImapConnector\Containers\ResponseContainer;

class Connector
{
    /**
     * @var string
     */
    protected $imapHost;

    /**
     * @var string
     */
    protected $imapPort;

    /**
     * @var resource|null
     */
    protected $stream;

    /**
     * @var int
     */
    protected $timeOut;

    /**
     * @var resource
     */
    protected $context;

    /**
     * @var ResponseContainerInterface|ResponseContainer
     */
    protected $responseContainer;

    /**
     * @var ProxyInterface
     */
    protected $proxy;

    public function __construct(ResponseContainerInterface $responseContainer, $context = null,  $timeOut = 15)
    {
        if(!$context)
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
        $this->responseContainer = $responseContainer;
        $this->context = $context;
        $this->timeOut = $timeOut;
    }

    public function __destruct()
    {
        if(is_resource($this->stream)) fclose($this->stream);
    }

    /**
     * @param ProxyInterface $proxy
     * @return bool|resource
     */
    public function connectToProxy(ProxyInterface $proxy)
    {
        $this->proxy = $proxy;
        if(is_resource($this->stream)) fclose($this->stream);

        $this->stream = $this->proxy->connect($this->timeOut);

        return $this->stream;
    }

    /**
     * @param string $host
     * @param integer $port
     * @return bool
     */
    public function connectToImap($host, $port)
    {
        $this->imapHost = $host;
        $this->imapPort = $port;

        if($this->proxy)
            $this->stream = $this->proxy->connectToImap($host, $port);
        else
            $this->stream = $this->createStream($this->imapHost, $this->imapPort);
            // if(is_resource($this->stream))
            // stream_set_blocking($this->stream, false);
        return $this->stream;
    }

    /**
     * @return null|resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    public function closeConnection()
    {
        if(is_resource($this->stream)){
            fclose($this->stream);
            $this->stream = null;
        }
    }

    /**
     * @param string $host
     * @param integer $port
     * @return bool
     */
    protected function createStream($host, $port)
    {
        //@todo proxy authentication
        if($this->stream = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, $this->timeOut, STREAM_CLIENT_CONNECT, $this->context)) {
        // stream_set_timeout($this->stream, $this->timeOut);
            return $this->stream;
        }
        $this->responseContainer->setLastErrorText("Failed connection to imap. Without proxy. " . $errstr);
        return false;
    }
}