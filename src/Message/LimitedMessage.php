<?php

/*
 * Author Thomas Beauchataud
 * Since 22/03/2022
 */

namespace TBCD\MessengerExtension\Message;

use DateInterval;

/**
 * @author Thomas Beauchataud
 * @since 02/05/2022
 */
interface LimitedMessage
{

    /**
     * @return DateInterval|null
     */
    public function getInterval(): ?DateInterval;

}