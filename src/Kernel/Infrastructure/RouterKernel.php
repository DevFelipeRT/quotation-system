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
use App\Infrastructure\Routing\Infrastructure\Providers\HomeRouteProvider;
use App\Infrastructure\Routing\Infrastructure\Registration\RouteRegistrar;
use App\Infrastructure\Routing\Infrastructure\Repository\InMemoryRouteRepository;
use App\Infrastructure\Routing\Infrastructure\Resolver\DefaultRouteResolver;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\RouteRequestInterface;
use App\Infrastructure\Routing\Presentation\Http\RoutingEngine;
use App\Infrastructure\Routing\Domain\ValueObjects\ControllerAction;
use App\Infrastructure\Routing\Infrastructure\Exceptions\RouteNotFoundException;
use App\Shared\Event\Contracts\EventDispatcherInterface;
use Throwable;

/**
 * RouterKernel
 *
 * Orchestrates the full routing lifecycle:
 * - Registers route providers and routes,
 * - Initializes repository, resolver, dispatcher,
 * - Provides a simple dispatch API for external use,
 * - Emits all routing-related domain events in a centralized and explicit manner.
 *
 * Inspired by the SessionKernel pattern for clarity, extensibility and SRP.
 */
final class RouterKernel
{
    /**
     * @var DefaultRouteResolver
     */
    private DefaultRouteResolver $resolver;

    /**
     * @var DefaultRouteDispatcher
     */
    private DefaultRouteDispatcher $dispatcher;

    /**
     * @var EventDispatcherInterface|null
     */
    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * RouterKernel constructor.
     *
     * @param array<class-string, object> $controllerMap
     * @param array<class-string, class-string> $controllerClassMap
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        array $controllerMap,
        array $controllerClassMap = [],
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        [$this->resolver, $this->dispatcher] = $this->initializeInfrastructure($controllerMap, $controllerClassMap);
    }

    /**
     * Dispatches the given RouteRequest, handling all routing events.
     *
     * @param RouteRequestInterface $request
     * @return mixed Controller result/response
     *
     * @throws Throwable Propagates any exception not handled internally.
     */
    public function dispatch(RouteRequestInterface $request)
    {
        $route = null;
        $controllerAction = null;

        try {
            $route = $this->resolver->resolve($request);
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

    // ====================== EVENT EMITTERS ======================

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
        $result
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

    // ====================== INITIALIZATION HELPERS ======================

    /**
     * Initializes routing infrastructure: repository, resolver, dispatcher.
     *
     * @param array<class-string, object> $controllerMap
     * @param array<class-string, class-string> $controllerClassMap
     * @return array{DefaultRouteResolver, DefaultRouteDispatcher}
     */
    private function initializeInfrastructure(
        array $controllerMap,
        array $controllerClassMap
    ): array {
        $routes = $this->collectRoutesFromProviders($this->loadDefaultProviders($controllerClassMap));
        $repository = $this->registerRoutes($routes);
        $resolver = $this->createResolver($repository);
        $dispatcher = $this->createDispatcher($resolver, $controllerMap);
        return [$resolver, $dispatcher];
    }

    /**
     * Loads all default route providers in the system.
     *
     * @param array<class-string, class-string> $controllerClassMap
     * @return RouteProviderInterface[]
     */
    private function loadDefaultProviders(array $controllerClassMap = []): array
    {
        $providers = [];
        $providerDefs = $this->getDefaultProviderDefinitions();
        foreach ($providerDefs as $providerFQCN => $mapKey) {
            $controllerClass = $controllerClassMap[$mapKey] ?? null;
            $providers[] = $this->instantiateProvider($providerFQCN, $controllerClass);
        }
        return $providers;
    }

    /**
     * Returns the list of default route providers.
     *
     * @return array<class-string<RouteProviderInterface>, class-string<RouteProviderInterface>>
     */
    private function getDefaultProviderDefinitions(): array
    {
        return [
            HomeRouteProvider::class => HomeRouteProvider::class,
            // ItemRouteProvider::class => ItemRouteProvider::class,
        ];
    }

    /**
     * Instantiates a provider, optionally injecting controller FQCN.
     *
     * @param class-string<RouteProviderInterface> $providerFQCN
     * @param class-string|null $controllerClass
     * @return RouteProviderInterface
     */
    private function instantiateProvider(string $providerFQCN, ?string $controllerClass = null): RouteProviderInterface
    {
        $ref = new \ReflectionClass($providerFQCN);
        if ($ref->getConstructor() && $ref->getConstructor()->getNumberOfParameters() > 0) {
            return $controllerClass
                ? new $providerFQCN($controllerClass)
                : new $providerFQCN();
        }
        return new $providerFQCN();
    }

    /**
     * Aggregates all routes from the registered providers.
     *
     * @param RouteProviderInterface[] $providers
     * @return HttpRouteInterface[]
     */
    private function collectRoutesFromProviders(array $providers): array
    {
        $routes = [];
        foreach ($providers as $provider) {
            $routes = array_merge($routes, $provider->provideRoutes());
        }
        return $routes;
    }

    /**
     * Registers all routes into the in-memory repository.
     *
     * @param HttpRouteInterface[] $routes
     * @return InMemoryRouteRepository
     */
    private function registerRoutes(array $routes): InMemoryRouteRepository
    {
        $repository = new InMemoryRouteRepository();
        $registrar = new RouteRegistrar($routes);
        $registrar->register($repository);
        return $repository;
    }

    /**
     * Instantiates the route resolver.
     *
     * @param InMemoryRouteRepository $repository
     * @return DefaultRouteResolver
     */
    private function createResolver(InMemoryRouteRepository $repository): DefaultRouteResolver
    {
        $matcher = new DefaultRouteMatcher();
        return new DefaultRouteResolver($repository, $matcher);
    }

    /**
     * Instantiates the route dispatcher.
     *
     * @param DefaultRouteResolver $resolver
     * @param array<class-string, object> $controllerMap
     * @return DefaultRouteDispatcher
     */
    private function createDispatcher(DefaultRouteResolver $resolver, array $controllerMap): DefaultRouteDispatcher
    {
        return new DefaultRouteDispatcher($resolver, $controllerMap);
    }
}
