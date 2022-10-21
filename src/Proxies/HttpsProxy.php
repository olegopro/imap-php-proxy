<?php

namespace ImapConnector\Proxies;

class HttpsProxy extends AbstractProxy
{
    public function connect($timeOut = 10)
    {
        $this->timeOut = $timeOut;
        return $this->createStream($this->host, $this->port);
    }

    public function connectToImap($host, $port)
    {
        fwrite($this->stream, "CONNECT {$host}:{$port} HTTP/1.1\r\n");
        fwrite($this->stream, "Host: {$host}:{$port}\r\n");
        fwrite($this->stream, "Proxy-Connection: Keep-Alive\r\n\r\n");

        $buf = '';
        $connectSuccess = false;
        $time = time();

        while ((time() - $time) < $this->timeOut) {
            $buf .= fgets($this->stream, 2048);
            if (strripos($buf,'200 conn') !== FALSE) {
                $connectSuccess = true;
                if (@stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return $this->stream;
                }
            }
        }
        if(!$connectSuccess){
            $this->responseContainer->setLastErrorText('Unable to connect to imap.');
            return false;
        }
        $this->responseContainer->setLastErrorText('Unable to enable crypting.');
        return false;
    }
}