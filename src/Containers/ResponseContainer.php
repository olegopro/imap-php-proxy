<?php

namespace ImapConnector\Containers;

class ResponseContainer implements ResponseContainerInterface
{
    /**
     * @var string
     */
    protected $lastErrorText;

    /**
     * @var string
     */
    protected $lastResponseText;

    /**
     * @var ResponseContainer
     */
    private static $instance;

    /**
     * @return ResponseContainer
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @return string
     */
    public function getLastErrorText()
    {
        return $this->lastErrorText;
    }

    /**
     * @return string
     */
    public function getLastResponseText()
    {
        return $this->lastResponseText;
    }

    /**
     * @param $lastResponseText
     */
    public function setLastResponseText($lastResponseText)
    {
        $this->lastResponseText = $lastResponseText;
    }

    /**
     * @param $lastErrorText
     */
    public function setLastErrorText($lastErrorText)
    {
        $this->lastErrorText = $lastErrorText;
    }

    private function __construct() {}
}