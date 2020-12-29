<?php

namespace NotificationChannels\Vodafone;

use Illuminate\Notifications\Notification;
use NotificationChannels\Vodafone\Exceptions\CouldNotSendNotification;

class VodafoneChannel
{
    /** $var VodafoneClient */
    private $client;

    public function __construct(VodafoneClient $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        /* Confirm the toVodafone method exists before continuing */
        if (!method_exists($notification, 'toVodafone')) {
            throw CouldNotSendNotification::methodDoesNotExist();
        }

        $message = $notification->toVodafone($notifiable, $notification);

        /* Check notification uses correct class for this API */
        if (!$message instanceof VodafoneMessage) {
            throw CouldNotSendNotification::incorrectMessageClass();
        }

        $this->client->send(
            $message->from,
            $notifiable->routeNotificationFor('vodafone'),
            $message->content
        );
    }
}
