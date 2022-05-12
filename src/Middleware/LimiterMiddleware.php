<?php

/*
 * Author Thomas Beauchataud
 * Since 22/03/2022
 */

namespace TBCD\MessengerExtension\Middleware;

use DateTime;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use TBCD\MessengerExtension\Stamp\LimitedStamp;
use TBCD\MessengerExtension\Stamp\LimiterStamp;

/**
 * @author Thomas Beauchataud
 * @since 02/05/2022
 */
class LimiterMiddleware implements MiddlewareInterface
{

    private CacheItemPoolInterface $cache;
    private LoggerInterface $logger;

    /**
     * @param CacheItemPoolInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(CacheItemPoolInterface $cache, #[Target('messenger.logger')] LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }


    /**
     * @param Envelope $envelope
     * @param StackInterface $stack
     * @return Envelope
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $messageClass = $envelope->getMessage()::class;

        /** @var LimiterStamp $limiterStamp */
        if (!($limiterStamp = $envelope->last(LimiterStamp::class))) {
            return $stack->next()->handle($envelope, $stack);
        }

        try {
            $messageCode = str_replace('\\', '_', strtolower($messageClass));
            $item = $this->cache->getItem("tbcd.limiter_middleware.$messageCode");
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            return $stack->next()->handle($envelope, $stack);
        }

        $now = new DateTime();

        if (!$item->isHit()) {
            $envelope = $stack->next()->handle($envelope, $stack);
            $item->set($now);
            $item->expiresAfter($limiterStamp->getDateInterval());
            $this->cache->save($item);
        } else {
            $this->logger->info("Stopping the propagation of the message $messageClass to the handler due to message limitation");
        }

        $stamp = new LimitedStamp($item->get(), $now, $limiterStamp->getDateInterval());

        return $envelope->with($stamp);
    }
}