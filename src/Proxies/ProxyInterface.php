<?php

namespace ImapConnector\Proxies;

interface ProxyInterface
{
    /**
     * @param int $timeOut
     * @return resource|false
     */
    public function connect($timeOut);

    /**
     * @param $host
     * @param $port
     * @return resource|false
     */
    public function connectToImap($host, $port);
}