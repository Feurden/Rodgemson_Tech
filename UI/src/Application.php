<?php
declare(strict_types=1);

namespace App;

use App\Middleware\HostHeaderMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventManagerInterface;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

class Application extends BaseApplication
{
    public function bootstrap(): void
    {
        parent::bootstrap();

        FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(false));
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))
            ->add(new HostHeaderMiddleware())
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware())

            // CSRF is skipped for JSON API routes (/devices/*, /parts/*, /ai/*, /parts-usage/*)
            // because those endpoints are protected by session auth instead.
            // CSRF only applies to browser form submissions (login, signup, profile).
            ->add((new CsrfProtectionMiddleware(['httponly' => true]))
                ->skipCheckCallback(function (\Psr\Http\Message\ServerRequestInterface $request) {
                    $path = $request->getUri()->getPath();
                    return (bool) preg_match('#^/(devices|parts|parts-usage|ai)/#', $path);
                })
            );

        return $middlewareQueue;
    }

    public function services(ContainerInterface $container): void
    {
    }

    public function events(EventManagerInterface $eventManager): EventManagerInterface
    {
        return $eventManager;
    }
}