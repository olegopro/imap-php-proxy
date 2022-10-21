<?php

namespace ImapConnector;

use Horde\Socket\ImapClient;
use ImapConnector\Mails\Mail;
use ImapConnector\Parsers\ParserInterface;
use ImapConnector\Parsers\Parser;
use ImapConnector\Containers\ResponseContainerInterface;
use ImapConnector\Containers\ResponseContainer;

class Commander
{
    /**
     * @var resource
     */
    protected $stream;

    /**
     * @var string
     */
    protected $commandHash = 'frfgbvcsdfsrefwertfd_';

    /**
     * @var int
     */
    protected $commandCounter = 1;

    /**
     * @var int
     */
    protected $timeOut = 60;

    /**
     * @var ParserInterface|Parser
     */
    protected $imapParser;

    /**
     * @var ResponseContainerInterface|ResponseContainer
     */
    protected $responseContainer;

    public function __construct($stream, ParserInterface $imapParser, ResponseContainerInterface $responseContainer)
    {
        $this->stream = $stream;
        $this->imapParser = $imapParser;
        $this->responseContainer = $responseContainer;
    }

    /**
     * @param string $email
     * @param string $password
     * @return false|string
     */
    public function login($email, $password)
    {
        return $this->sendCommand("LOGIN {$email} {$password}");
    }

    /**
     * @param $mailbox
     * @return false|string
     */
    public function selectMailbox($mailbox)
    {
        return $this->sendCommand("SELECT {$mailbox}");
    }

    /**
     * @param int $thanUid
     * @return array|false
     */
    public function searchWhereUidGreaterThan($thanUid)
    {
        $response = $this->sendCommand("UID SEARCH UID {$thanUid}:*");
        $uids = $this->imapParser->parseUidsGreaterThan($response);
        foreach ($uids as $index => &$uid)
            if((int) $uid <= (int) $thanUid) unset($uids[$index]);
        return $uids;
    }

    public function getMailStringByUid($uid)
    {
        if($response = $this->sendCommand("UID FETCH $uid BODY.PEEK[]")){
            return $this->imapParser->parseMailStringFromImapResponse($response, $this->commandHash);
        }
    }

    /**
     * @param int $uid
     * @return null|\ZBateson\MailMimeParser\Message|Mail
     */
    public function fetchByUid($uid)
    {
        if($mailString = $this->getMailStringByUid($uid)){
            return $this->imapParser->getMailObject($mailString);
        }
        return null;
    }

    public function fetchUidsOnDate($date)
    {
        $response = $this->sendCommand("UID SEARCH ON {$date}");
        return $this->imapParser->parseUidsGreaterThan($response);
    }

    public function markAsDeleted($uid)
    {
        return $this->sendCommand("UID STORE {$uid} +FLAGS (\Deleted)");
    }

    public function moveToFolder($mailUid, $folder)
    {
        $response = $this->sendCommand("UID MOVE {$mailUid} {$folder}");
        return $this->imapParser->parseMovedUid($response);
    }

    public function expunge()
    {
        return $this->sendCommand("EXPUNGE");
    }

    /**
     * @param $mailString
     * @param string $folder
     * @return false|int
     */
    public function append($mailString, $folder = "INBOX")
    {
        if($this->sendCommand("APPEND {$folder} {".strlen($mailString)."}", "+")){
            $response = $this->sendCommand($mailString, '', false);
            return $this->imapParser->parseAppendResult($response);
        }
        return false;
    }

    /**
     * @return false|int
     */
    public function fetchLastUid(){
        $response = $this->sendCommand("FETCH * (UID)");
        return $this->imapParser->parseLastUid($response);
    }

    /**
     * @return array|false
     */
    public function getFoldersList()
    {
        $response = $this->sendCommand('LIST "" *');
        return $response;

//        return $this->imapParser->parseFolderList($response);
    }

    public function getMessagesCount($mailbox)
    {
        $response = $this->sendCommand("STATUS {$mailbox} (MESSAGES)");
        return (integer) $this->imapParser->parseMessagesCount($response);
    }

    public function getUnseenCount($mailbox)
    {
        $response = $this->sendCommand("STATUS {$mailbox} (UNSEEN)");
        return (integer) $this->imapParser->parseUnseenCount($response);
    }

    public function closeMailbox()
    {
        $this->sendCommand("CLOSE");
    }

    /**
     * @param string $command
     * @param string $successPattern
     * @param bool $withCounter
     * @return false|string
     */
    protected function sendCommand($command, $successPattern = '', $withCounter = true)
    {
        $counter = $withCounter ? "{$this->commandHash}{$this->commandCounter} " : "";
        $successPattern = !$successPattern ? $counter . 'OK' : $successPattern;

        fwrite($this->stream, "{$counter}{$command}\r\n");
        $this->commandCounter++;

        $previousLine = '';
        $buf = '';
        $time = time();

        while ((time() - $time) < $this->timeOut) {
            $newLine = fread($this->stream, 4096);
            if(!strlen($newLine)) continue;
            $buf .= $newLine;

            file_put_contents("/LOG2.txt", $newLine, FILE_APPEND);


            if (strripos($previousLine.$newLine, $successPattern) !== FALSE){
                $this->responseContainer->setLastResponseText($buf);
                return $buf;
            }
            if (strripos($previousLine.$newLine, $this->commandHash . ($this->commandCounter - 1) . ' NO') !== FALSE
                || strripos($previousLine.$newLine, $this->commandHash . ($this->commandCounter - 1) . ' BAD') !== FALSE){
                $this->responseContainer->setLastErrorText($buf);
                return false;
            }
            $previousLine = $newLine;
        }

        var_dump(" Time out");

        $this->responseContainer->setLastErrorText("{$command} {$counter} Time out");
        return false;
    }
}