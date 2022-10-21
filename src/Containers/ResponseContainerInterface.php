<?php

namespace olegopro\ImapConnector\Containers;

interface ResponseContainerInterface
{
    public function getLastErrorText();

    public function getLastResponseText();

    public function setLastResponseText($lastResponseText);

    public function setLastErrorText($lastErrorText);
}