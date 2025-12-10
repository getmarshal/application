<?php

declare(strict_types=1);

namespace Marshal\Application\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Marshal\Server\Platform\PlatformInterface;
use Mezzio\Helper\UrlHelperInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FinalResponseMiddleware implements MiddlewareInterface
{
    public function __construct(private UrlHelperInterface $urlHelper)
    {
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $platform = $request->getAttribute(PlatformInterface::class);
        \assert($platform instanceof PlatformInterface);

        if ($request->getUri()->getPath() === "/") {
            return new RedirectResponse($this->urlHelper->generate(
                "auth::login"
            ));
        }

        return $platform->formatResponse($request, status: StatusCodeInterface::STATUS_NOT_FOUND);
    }
}
