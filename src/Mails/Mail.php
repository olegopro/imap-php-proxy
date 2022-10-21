<?php

namespace ImapConnector\Mails;

class Mail implements MailInterface
{
    protected $fromAddress;
    protected $toAddress;
    protected $date;
    protected $subject;
    protected $textBody;
    protected $htmlBody;
    protected $files = [];

    public function getFromAddress()
    {
        return $this->fromAddress;
    }

    public function getToAddress()
    {
        return $this->toAddress;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getTextBody()
    {
        return $this->textBody;
    }

    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setFromAddress($fromAddress)
    {
        $this->fromAddress = $fromAddress;
    }

    public function setToAddress($toAddress)
    {
        $this->toAddress = $toAddress;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setTextBody($textBody)
    {
        $this->textBody = $textBody;
    }

    public function setHtmlBody($htmlBody)
    {
        $this->htmlBody = $htmlBody;
    }

    public function addFile(array $file)
    {
        if(!empty($file['name']) && !empty($file['content']))
            $this->files[] = $file;
    }
}