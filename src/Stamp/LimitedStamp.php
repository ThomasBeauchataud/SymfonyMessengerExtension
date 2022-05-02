<?php

/*
 * Author Thomas Beauchataud
 * Since 02/05/2022
 */

namespace TBCD\MessengerExtension\Stamp;

use DateInterval;
use DateTimeInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * The LimiterMiddleware success stamp
 *
 * @author Thomas Beauchataud
 * @since 02/05/2022
 */
class LimitedStamp implements StampInterface
{

    /**
     * @var DateTimeInterface
     */
    private DateTimeInterface $lastExecutionTimestamp;

    /**
     * @var DateTimeInterface
     */
    private DateTimeInterface $currentExecutionTimestamp;

    /**
     * @var DateInterval
     */
    private DateInterval $submittedExecutionInterval;

    /**
     * @param DateTimeInterface $lastExecutionTimestamp
     * @param DateTimeInterface $currentExecutionTimestamp
     * @param DateInterval $submittedExecutionInterval
     */
    public function __construct(DateTimeInterface $lastExecutionTimestamp, DateTimeInterface $currentExecutionTimestamp, DateInterval $submittedExecutionInterval)
    {
        $this->lastExecutionTimestamp = $lastExecutionTimestamp;
        $this->currentExecutionTimestamp = $currentExecutionTimestamp;
        $this->submittedExecutionInterval = $submittedExecutionInterval;
    }

    /**
     * @return DateTimeInterface
     */
    public function getLastExecutionTimestamp(): DateTimeInterface
    {
        return $this->lastExecutionTimestamp;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCurrentExecutionTimestamp(): DateTimeInterface
    {
        return $this->currentExecutionTimestamp;
    }

    /**
     * @return DateInterval
     */
    public function getSubmittedExecutionInterval(): DateInterval
    {
        return $this->submittedExecutionInterval;
    }

    /**
     * @return DateTimeInterface
     */
    public function getNextAvailableExecutionTimestamp(): DateTimeInterface
    {
        return $this->lastExecutionTimestamp->add($this->submittedExecutionInterval);
    }

    /**
     * @return bool
     */
    public function hasBeenLimited(): bool
    {
        return $this->lastExecutionTimestamp !== $this->currentExecutionTimestamp;
    }
}