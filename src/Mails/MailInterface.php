<?php

namespace ImapConnector\Mails;

use Interceptor\Interfaces\AccountInterface;

/**
 * @property string $from_name
 * @property AccountInterface $account
 */

interface MailInterface
{
    public function getFromAddress();

    public function getToAddress();

    public function getDate();

    public function getSubject();

    public function getTextBody();

    public function getHtmlBody();

    public function setFromAddress($fromAddress);

    public function setToAddress($toAddress);

    public function setDate($date);

    public function setSubject($subject);

    public function setTextBody($textBody);

    public function setHtmlBody($htmlBody);
}