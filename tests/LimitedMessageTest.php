<?php

/*
 * Author Thomas Beauchataud
 * Since 02/05/2022
 */

namespace TBCD\Tests\MessengerExtension;

use DateInterval;
use TBCD\MessengerExtension\Message\LimitedMessage;

class LimitedMessageTest implements LimitedMessage
{

    /**
     * @inheritDoc
     */
    public function getInterval(): ?DateInterval
    {
        return new DateInterval('PT1H');
    }
}