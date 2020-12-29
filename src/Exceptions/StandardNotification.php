<?php

namespace NotificationChannels\Vodafone\Exceptions;

class StandardNotification extends \Exception
{
    public static function serviceUnknownResponse()
    {
        return new static('Unknown response coming from the Vodafone API.');
    }

    public static function serviceRespondedWithAnError($response)
    {
        return new static('Code: '.$response->code.' - '.$response->reason);
    }
}
