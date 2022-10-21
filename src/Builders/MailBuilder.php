<?php

namespace olegopro\ImapConnector\Builders;

use olegopro\ImapConnector\Mails\Mail;
use olegopro\ImapConnector\Mails\MailInterface;
use \ZBateson\MailMimeParser\Message;

class MailBuilder implements MailBuilderInterface
{
    /**
     * @var Mail
     */
    protected $mailClass;

    /**
     * @param $mailClass
     * @throws \Exception
     */
    public function __construct($mailClass)
    {
        if(empty(class_implements($mailClass)[MailInterface::class])){
            throw new \Exception('mailClass must implements MailInterface');
        }
        $this->mailClass = $mailClass;
    }

    public function buildMail(Message $message)
    {
        /** @var Mail $mail */
        $mail = new $this->mailClass;
        $mail->setSubject($message->getHeaderValue('Subject'));
        $mail->setDate($message->getHeaderValue('Date'));

        if($message->getHeader('from'))
            $from = $message->getHeader('from')->getValue();
        else
            $from = '';
        if($message->getHeader('to'))
            $to = $message->getHeader('to')->getValue();
        else
            $to = '';

        $mail->setFromAddress($from);
        $mail->setToAddress($to);
        $mail->setTextBody($message->getTextContent());
        $mail->setHtmlBody($message->getHtmlContent());

        $mimePartObjects = $message->getAllAttachmentParts();
        /** @var \ZBateson\MailMimeParser\Message\MimePart $mimePartObject */
        foreach ($mimePartObjects as $mimePartObject) {
            $fileName = $mimePartObject->getHeaderParameter('content-type', 'name');
            $mail->addFile(['name' => $fileName, 'content' => $mimePartObject->getContent()]);

//            file_put_contents("./" . $fileName, $mimePartObject->getContent());
//            var_dump(stream_get_contents($mimePartObject->getContentResourceHandle()));
//            file_put_contents("./FILES.txt", print_r($mimePartObject->getHeaders(), true)); die();
        }

        return $mail;
    }

}