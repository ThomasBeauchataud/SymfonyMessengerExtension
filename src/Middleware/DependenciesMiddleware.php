<?php

/*
 * Author Thomas Beauchataud
 * Since 18/03/2022
 */

namespace TBCD\MessengerExtension\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use TBCD\MessengerExtension\Message\DependentMessage;
use TBCD\MessengerExtension\Stamp\DependenciesStamp;
use TBCD\MessengerExtension\Stamp\DependencyStamp;

/**
 * @author Thomas Beauchataud
 * @since 02/05/2022
 */
class DependenciesMiddleware implements MiddlewareInterface
{

    private MessageBusInterface $bus;
    private LoggerInterface $logger;

    /**
     * @param MessageBusInterface $bus
     * @param LoggerInterface $logger
     */
    public function __construct(MessageBusInterface $bus, #[Target('messenger.logger')] LoggerInterface $logger)
    {
        $this->bus = $bus;
        $this->logger = $logger;
    }


    /**
     * @param Envelope $envelope
     * @param StackInterface $stack
     * @return Envelope
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (!$envelope->last(DependencyStamp::class)) {

            /** @var DependentMessage $message */
            if (($message = $envelope->getMessage()) instanceof DependentMessage) {
                foreach ($message->getMessageDependencies() as $messageDependency) {
                    $this->logger->info(sprintf('Dispatching the message %s as a dependency of the message %s', $messageDependency::class, $message::class));
                    $this->bus->dispatch($messageDependency, [new DependencyStamp()]);
                }

                return $stack->next()->handle($envelope->with(new DependenciesStamp()), $stack);
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}