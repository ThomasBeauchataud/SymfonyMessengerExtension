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
        $message = $envelope->getMessage();

        if (!$message instanceof LimitedMessage || !$message->getInterval()) {
            return $stack->next()->handle($envelope, $stack);
        }

        try {
            $messageCode = str_replace('\\', '_', strtolower($message::class));
            $item = $this->cache->getItem("tbcd.limiter_middleware.$messageCode");
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            return $stack->next()->handle($envelope, $stack);
        }

        $now = new DateTime();

        if (!$item->isHit()) {
            $envelope = $stack->next()->handle($envelope, $stack);
            $item->set($now);
            $item->expiresAfter($message->getInterval());
            $this->cache->save($item);
            $stamp = new LimitedStamp($now, $now, $message->getInterval());
        } else {
            $this->logger->info(sprintf("Stopping the propagation of the message %s to the handler due to message limitation", $message::class));
            $stamp = new LimitedStamp($item->get(), $now, $message->getInterval());
        }

        return $envelope->with($stamp);
    }
}