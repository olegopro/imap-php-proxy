<?php

namespace olegopro\ImapConnector\Proxies;

class Socks5Proxy extends AbstractProxy
{
    public function connect($timeOut = 10)
    {
        $this->timeOut = $timeOut;
        $stream = $this->createStream($this->host, $this->port);
        if(is_resource($stream)) {
            fwrite($stream, pack("C3", 0x05, 0x01, 0x00));
            if (fread($stream, 2048) != pack("C2", 0x05, 0x00)) {
                $this->responseContainer->setLastErrorText('Wrong socks version');
                return false;
            }
            return $stream;
        }
    }

    public function connectToImap($host, $port)
    {
        if(!$this->stream) return false;
        fwrite( $this->stream, pack( "C5", 0x05, 0x01, 0x00, 0x03, strlen( $host ) ) . $host . pack( "n", $port ) );
        $server_buffer = fread( $this->stream, 10 );

        if(strlen($server_buffer) < 3 || ord( $server_buffer[0] ) != 5 || ord( $server_buffer[1] ) != 0
            || ord( $server_buffer[2] ) != 0){
            $this->responseContainer->setLastErrorText('Unable to connect to imap.');
            return false;
        }

        if(!@stream_socket_enable_crypto( $this->stream, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT )){
            $this->responseContainer->setLastErrorText('Unable to enable crypto.');
            return false;
        }

        return $this->stream;
    }
}