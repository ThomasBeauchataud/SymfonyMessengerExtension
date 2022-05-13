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
        $messageCode = str_replace('\\', '_', strtolower($messageClass));
        $cacheKey = "tbcd.limiter_middleware.$messageCode";

        try {

            $item = $this->cache->getItem($cacheKey);

            if (!$envelope->last(LimiterStamp::class)) {
                $envelope = $stack->next()->handle($envelope, $stack);
                $item->set(new DateTime());
                $this->cache->save($item);
                return $envelope;
            }

            /** @var LimiterStamp $limiterStamp */
            $limiterStamp = $envelope->last(LimiterStamp::class);

            if (!$item->isHit()) {
                $envelope = $stack->next()->handle($envelope, $stack);
                $now = new DateTime();
                $item->set($now);
                $this->cache->save($item);
                $limiterStamp = new LimitedStamp($now, $now, $limiterStamp->getDateInterval());
                return $envelope->with($limiterStamp);
            }

            /** @var DateTime $lastExecution */
            $lastExecution = $item->get();
            if ($lastExecution->add($limiterStamp->getDateInterval()) > ($now = new DateTime())) {
                $this->logger->info("Stopping the propagation of the message $messageClass to the handler due to message limitation");
                $limiterStamp = new LimitedStamp($lastExecution, $now, $limiterStamp->getDateInterval());
            } else {
                $envelope = $stack->next()->handle($envelope, $stack);
                $now = new DateTime();
                $item->set($now);
                $this->cache->save($item);
                $limiterStamp = new LimitedStamp($now, $now, $limiterStamp->getDateInterval());
            }
            return $envelope->with($limiterStamp);

        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            return $stack->next()->handle($envelope, $stack);
        }
    }
}