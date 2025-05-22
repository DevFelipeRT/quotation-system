<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure;

use App\Infrastructure\Routing\Domain\Contracts\RouteProviderInterface;
use App\Infrastructure\Routing\Domain\Events\{
    RouteMatchedEvent,
    RouteNotFoundEvent,
    BeforeRouteDispatchEvent,
    AfterRouteDispatchEvent,
    RouteDispatchFailedEvent,
    RouteResolvedEvent
};
use App\Infrastructure\Routing\Infrastructure\Dispatcher\DefaultRouteDispatcher;
use App\Infrastructure\Routing\Infrastructure\Matcher\DefaultRouteMatcher;
use App\Infrastructure\Routing\Infrastructure\Registration\RouteRegistrar;
use App\Infrastructure\Routing\Infrastructure\Repository\InMemoryRouteRepository;
use App\Infrastructure\Routing\Infrastructure\Resolver\DefaultRouteResolver;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\RouteRequestInterface;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;
use App\Infrastructure\Routing\Infrastructure\Exceptions\RouteNotFoundException;
use App\Kernel\Discovery\DiscoveryKernel;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use App\Shared\Container\AppContainerInterface;
use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use Throwable;

final class RouterKernel
{
    private AppContainerInterface $container;
    private DiscoveryScanner $scanner;
    private EventDispatcherInterface $eventDispatcher;

    private DefaultRouteResolver $resolver;
    private DefaultRouteDispatcher $dispatcher;
    
    public function __construct(
        AppContainerInterface $container,
        DiscoveryKernel $scannerKernel,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->container = $container;
        $this->scanner = $scannerKernel->scanner();
        $this->eventDispatcher = $eventDispatcher;
        [$this->resolver, $this->dispatcher] = $this->initializeInfrastructure();
    }

    public function dispatch(RouteRequestInterface $request)
    {
        $route = null;
        $controllerAction = null;

        try {
            $route = $this->resolver->resolve($request);
            
            if (!$route instanceof HttpRouteInterface) {
                throw new \UnexpectedValueException('Resolved route is invalid or null.');
            }

            $this->emitRouteResolved($request, $route);
            $this->emitRouteMatched($request, $route);

            $controllerAction = $route->controllerAction();

            $this->emitBeforeRouteDispatch($request, $route, $controllerAction);

            $result = $this->dispatcher->dispatch($request);

            $this->emitAfterRouteDispatch($request, $route, $controllerAction, $result);

            return $result;
        } catch (RouteNotFoundException $e) {
            $this->emitRouteNotFound($request, $e->getMessage());
            throw $e;
        } catch (Throwable $e) {
            $this->emitRouteDispatchFailed($request, $e, $route, $controllerAction);
            throw $e;
        }
    }

    private function emitRouteMatched(RouteRequestInterface $request, HttpRouteInterface $route): void
    {
        $this->eventDispatcher?->dispatch(new RouteMatchedEvent($request, $route));
    }

    private function emitRouteNotFound(RouteRequestInterface $request, ?string $message = null): void
    {
        $this->eventDispatcher?->dispatch(new RouteNotFoundEvent($request, $message));
    }

    private function emitBeforeRouteDispatch(
        RouteRequestInterface $request,
        HttpRouteInterface $route,
        ControllerAction $controllerAction
    ): void {
        $this->eventDispatcher?->dispatch(new BeforeRouteDispatchEvent($request, $route, $controllerAction));
    }

    private function emitAfterRouteDispatch(
        RouteRequestInterface $request,
        HttpRouteInterface $route,
        ControllerAction $controllerAction,
        mixed $result
    ): void {
        $this->eventDispatcher?->dispatch(new AfterRouteDispatchEvent($request, $route, $controllerAction, $result));
    }

    private function emitRouteDispatchFailed(
        RouteRequestInterface $request,
        Throwable $exception,
        ?HttpRouteInterface $route,
        ?ControllerAction $controllerAction
    ): void {
        $this->eventDispatcher?->dispatch(
            new RouteDispatchFailedEvent($request, $exception, $route, $controllerAction)
        );
    }

    private function emitRouteResolved(
        RouteRequestInterface $request,
        HttpRouteInterface $route,
        ?string $resolutionType = 'standard'
    ): void {
        $this->eventDispatcher?->dispatch(new RouteResolvedEvent($request, $route, $resolutionType));
    }

    private function initializeInfrastructure(): array
    {
        $controllerMap = $this->discoverControllerInstances();
        $routes = $this->collectRoutesFromProviders($this->loadDefaultProviders());
        $repository = $this->registerRoutes($routes);
        $resolver = $this->createResolver($repository);
        $dispatcher = $this->createDispatcher($resolver, $controllerMap);
        return [$resolver, $dispatcher];
    }

    private function loadDefaultProviders(): array
    {
        $providers = array_map(
            fn (string $fqcn) => $this->container->get($fqcn),
            $this->scanner->discoverImplementing(
                RouteProviderInterface::class,
                'App\\Infrastructure\\Routing\\Infrastructure\\Providers'
            )
        );
        return $providers;
    }

    private function discoverControllerInstances(): array
    {
        $controllerFQCNs = $this->scanner->discoverExtending(
            'App\\Presentation\\Http\\Controllers\\BaseController',
            'App\\Presentation\\Http\\Controllers'
        );

        $map = [];
        foreach ($controllerFQCNs as $fqcn) {
            $map[$fqcn] = $this->container->get($fqcn);
        }

        return $map;
    }

    private function collectRoutesFromProviders(array $providers): array
    {
        $routes = [];
        foreach ($providers as $provider) {
            $routes = array_merge($routes, $provider->provideRoutes());
        }
        return $routes;
    }

    private function registerRoutes(array $routes): InMemoryRouteRepository
    {
        $repository = new InMemoryRouteRepository();
        $registrar = new RouteRegistrar($routes);
        $registrar->register($repository);
        return $repository;
    }

    private function createResolver(InMemoryRouteRepository $repository): DefaultRouteResolver
    {
        $matcher = new DefaultRouteMatcher();
        return new DefaultRouteResolver($repository, $matcher);
    }

    private function createDispatcher(DefaultRouteResolver $resolver, array $controllerMap): DefaultRouteDispatcher
    {
        return new DefaultRouteDispatcher($resolver, $controllerMap);
    }
}
