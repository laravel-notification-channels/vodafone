<?php

namespace NotificationChannels\Vodafone;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NotificationChannels\Vodafone\Exceptions\CouldNotReceiveNotification;
use NotificationChannels\Vodafone\Exceptions\CouldNotSendNotification;
use NotificationChannels\Vodafone\Exceptions\StandardNotification;

class VodafoneClient
{
    /**
     * @var string Vodafone's send API endpoint
     */
    protected string $sendEndpoint = 'https://www.smsertech.com/apisend';

    /**
     * @var string Vodafone's status API endpoint
     */
    protected string $statusEndpoint = 'https://www.smsertech.com/apistatus';

    /**
     * @var string Vodafone's receive API endpoint
     */
    protected string $receiveEndpoint = 'https://www.smsertech.com/apiget';

    /** @var string Vodafone SMS username */
    protected $username;

    /** @var string Vodafone SMS password */
    protected $password;

    /**
     * VodafoneClient constructor.
     *
     * @param $username
     * @param $password
     */
    public function __construct()
    {
        $this->username = config('services.vodafone.username');
        $this->password = config('services.vodafone.password');

        return $this;
    }

    /**
     *  VodafoneClient send method.
     *
     * @param $from
     * @param $to
     * @param $message
     *
     * @throws CouldNotSendNotification
     * @throws StandardNotification
     * @throws GuzzleException
     *
     * @return mixed Vodafone API result
     */
    public function send($from, $to, $message)
    {
        $client = new Client();
        $res = $client->post($this->sendEndpoint, [
            'form_params' => [
                'username'  => $this->username,
                'password'  => $this->password,
                'to'        => $to,
                'message'   => $message,
                'from'      => $from,
                'format'    => 'json',
                'flash'     => 0,
            ],
        ]);

        if (!$res) {
            throw CouldNotSendNotification::serviceUnknownResponse();
        }

        $body = $this->parseResponse($res);

        if ($body->status === 'ERROR') {
            throw StandardNotification::serviceRespondedWithAnError($body);
        }

        return $body;
    }

    /**
     * VodafoneClient receive method.
     *
     * @throws CouldNotReceiveNotification
     * @throws StandardNotification
     * @throws GuzzleException
     *
     * @return mixed Vodafone API result
     */
    public function receive()
    {
        $client = new Client();
        $res = $client->post($this->receiveEndpoint, [
            'form_params' => [
                'username'  => $this->username,
                'password'  => $this->password,
                'format'    => 'json',
            ],
        ]);

        if (!$res) {
            throw CouldNotReceiveNotification::serviceUnknownResponse();
        }

        $body = $this->parseResponse($res);

        if ($body->status === 'ERROR') {
            if($body->code === 201) {
                throw CouldNotReceiveNotification::noNewMessages();
            }

            throw StandardNotification::serviceRespondedWithAnError($body);
        }

        return $body;
    }

    /**
     * @throws CouldNotReceiveNotification
     * @throws GuzzleException
     * @throws StandardNotification
     *
     * @return mixed|null
     */
    public static function getUnread()
    {
        return (new self())->receive();
    }

    /**
     * @param $res
     *
     * @return mixed|null
     */
    public function parseResponse($res)
    {
        return json_decode($res->getBody()->getContents())[0] ?? null;
    }
}
