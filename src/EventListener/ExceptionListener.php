<?php

namespace App\EventListener;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as BaseExceptionListener;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ExceptionListener extends BaseExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        // Normalize exceptions only for routes managed by API Platform
        if ('html' === $request->getRequestFormat('') ||
            (!$request->attributes->has('_api_resource_class') &&
                !$request->attributes->has('_api_respond') &&
                !$request->attributes->has('_graphql'))) {
            return;
        }

        $exception = $event->getException();

        if ($exception instanceof InvalidArgumentException &&
            $exception->getPrevious() instanceof ItemNotFoundException) {
            $violations = new ConstraintViolationList(
                [
                    new ConstraintViolation(
                        $exception->getMessage(),
                        null,
                        [],
                        '',
                        '',
                        ''
                    )
                ]
            );

            $e = new ValidationException($violations);
            $event->setException($e);

            return;
        }
    }
}
