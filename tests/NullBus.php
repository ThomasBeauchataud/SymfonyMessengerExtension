<?php

/*
 * Author Thomas Beauchataud
 * Since 02/05/2022
 */

namespace TBCD\Tests\MessengerExtension;

use DateInterval;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;
use TBCD\MessengerExtension\Message\LimitedMessage;

class NullBus implements MessageBusInterface
{

    /**
     * @param object $message
     * @param array $stamps
     * @return Envelope
     */
    public function dispatch(object $message, array $stamps = []): Envelope
    {
        return new Envelope($message);
    }
}