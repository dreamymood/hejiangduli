<?php

namespace Opening\Sms\Messages;

use Opening\Sms\Senders\SenderInterface;

abstract class BaseMessage extends \yii\base\BaseObject
{
    /**
     * SMS sign name
     *
     * @var string
     */
    public $sign;

    /**
     * Phone number
     *
     * @var string
     */
    public $phoneNumber;
    
    /**
     * SMS Sender
     *
     * @var SenderInterface
     */
    public $sender;

    abstract public function send();
}
