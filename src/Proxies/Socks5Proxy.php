<?php

namespace ImapConnector\Proxies;

class Socks5Proxy extends AbstractProxy {

    public function connect($timeOut = 10) {
        $this->timeOut = $timeOut;
        $stream = $this->createStream($this->host, $this->port);

        if (is_resource($stream)) {

            fwrite($stream, pack("C3", 0x05, 0x01, 0x02));
            if (fread($stream, 512) !== pack("C2", 0x05, 0x02)) {
                $this->responseContainer->setLastErrorText('Socks server does not support password authentication mode');

                return false;
            }


            fwrite($this->stream, pack('C2', 0x01, strlen($this->proxy_user)) . $this->proxy_user . pack('C1', strlen($this->proxy_pass)) . $this->proxy_pass);
            if ((fread($this->stream, 512)) !== (pack('C2', 0x01, 0x00))) {
                $this->responseContainer->setLastErrorText('Wrong username or password');

                return false;
            }

            return $stream;
        }
    }

    public function connectToImap($host, $port) {

        if (!$this->stream) return false;

        fwrite($this->stream, pack("C5", 0x05, 0x01, 0x00, 0x03, strlen($host)) . $host . pack("n", $port));
        $server_buffer = fread($this->stream, 10);

        if (
            strlen($server_buffer) < 3
            || ord($server_buffer[0]) != 5
            || ord($server_buffer[1]) != 0
            || ord($server_buffer[2]) != 0
        ) {
            $this->responseContainer->setLastErrorText('Unable to connect to imap.');
            return false;
        }

        if (!@stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT)) {
            $this->responseContainer->setLastErrorText('Unable to enable crypto.');
            return false;
        }

        return $this->stream;
    }
}
