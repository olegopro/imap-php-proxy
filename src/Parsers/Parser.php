<?php

namespace olegopro\ImapConnector\Parsers;

use olegopro\ImapConnector\Builders\MailBuilder;
use olegopro\ImapConnector\Builders\MailBuilderInterface;
use \ZBateson\MailMimeParser\MailMimeParser;

class Parser extends MailMimeParser implements ParserInterface
{
    /**
     * @var null|MailBuilderInterface|MailBuilder;
     */
    protected $mailBuilder;

    /**
     * @param MailBuilderInterface|null $mailBuilder
     */
    public function __construct(MailBuilderInterface $mailBuilder = null)
    {
        parent::__construct();
        $this->mailBuilder = $mailBuilder;
    }

    public function parseMailStringFromImapResponse($response, $commandHash)
    {
        $mailString = preg_replace("/\)(\n|\r)+$commandHash.*/", "", $response);
        $mailString = preg_replace("/.*FETCH.*\n/", "", $mailString);
        return $mailString;
    }

    /**
     * @param $response
     * @return false|int
     */
    public function parseAppendResult($response)
    {
        preg_match("/\[.* .* (.*?)\]/", $response, $matches);
        if(!empty($matches[1]) && ((integer) $matches[1]) > 0) return (integer) $matches[1];
        return false;
    }

    /**
     * @param $response
     * @return int|false
     */
    public function parseLastUid($response)
    {
        preg_match("/\(UID (.*?)\)/", $response, $matches);
        return !empty($matches[1]) ?  (integer) $matches[1] : false;
    }

    /**
     * @param $response
     * @return array|false
     */
    public function parseFolderList($response)
    {
        preg_match_all("/\"\|\"(.*?)\n/", $response, $matches);
        if(!empty($matches[1]) && is_array($matches[1])){
            return array_map(function ($folder){
                return trim($folder);
            }, $matches[1]);
        }
        return false;
    }

    /**
     * @param $response
     * @return array|false
     */
    public function parseUidsGreaterThan($response)
    {
        preg_match("/SEARCH (.*?)\n/", $response, $matches);
        if(!empty($matches[1]))
            return array_map(
                function ($uid){
                    return (integer) $uid;
                },
                explode(" ", $matches[1])
            );
        return false;
    }

    public function parseMessagesCount($response)
    {
        preg_match("/MESSAGES (.*?)\)/", $response, $matches);
        return !empty($matches[1]) ?  (integer) $matches[1] : false;
    }

    public function parseUnseenCount($response)
    {
        preg_match("/UNSEEN (.*?)\)/", $response, $matches);
        return !empty($matches[1]) ?  (integer) $matches[1] : false;
    }

    public function parseMovedUid($response)
    {
        preg_match("/\s(\d*)\]/", $response, $matches);
        return !empty($matches[1]) ?  (integer) $matches[1] : false;
    }

    public function getMailObject($mailString)
    {
        $mailObject = $this->parse($mailString);
        if($this->mailBuilder) {
            return $this->mailBuilder->buildMail($mailObject);
        }
        return $mailObject;
    }
}