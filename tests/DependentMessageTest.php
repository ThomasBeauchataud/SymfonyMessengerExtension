<?php

/*
 * Author Thomas Beauchataud
 * Since 02/05/2022
 */

namespace TBCD\Tests\MessengerExtension;

use DateTime;
use TBCD\MessengerExtension\Message\DependentMessage;

class DependentMessageTest implements DependentMessage
{

    /**
     * @inheritDoc
     */
    public function getMessageDependencies(): array
    {
        return [new DateTime()];
    }
}