<?php


namespace App\EventSubscriber\Monzo;

use Amelia\Monzo\Exceptions;
use App\Monzo\Authenticator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class InvalidTokenException implements EventSubscriberInterface
{
    /** @var Authenticator */
    private $monzoAuth;
    /** @var Security */
    private $security;

    public function __construct(Authenticator $monzoAuth, Security $security)
    {
        $this->monzoAuth = $monzoAuth;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['processException', 10],
            ],
        ];
    }

    public function processException(ExceptionEvent $event)
    {
        $e = $event->getThrowable();
        if (!($e instanceof Exceptions\InvalidTokenException)) {
            return;
        }
        $this->monzoAuth->disconnectMonzo(
            $this->security->getUser()
        );
        $event->setResponse(new RedirectResponse('/'));
        $event->stopPropagation();
    }
}