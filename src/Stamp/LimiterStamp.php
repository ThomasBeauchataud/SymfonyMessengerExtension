<?php

/*
 * Author Thomas Beauchataud
 * Since 12/05/2022
 */

namespace TBCD\MessengerExtension\Stamp;

use DateInterval;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @author Thomas Beauchataud
 * @since 12/05/2022
 */
class LimiterStamp implements StampInterface
{

    /**
     * @var DateInterval
     */
    private DateInterval $dateInterval;

    /**
     * @param DateInterval $dateInterval
     */
    public function __construct(DateInterval $dateInterval)
    {
        $this->dateInterval = $dateInterval;
    }


    /**
     * @return DateInterval
     */
    public function getDateInterval(): DateInterval
    {
        return $this->dateInterval;
    }
}