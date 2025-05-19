<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure;

use App\Infrastructure\Routing\Domain\Contracts\RouteProviderInterface;
use App\Infrastructure\Routing\Infrastructure\Dispatcher\DefaultRouteDispatcher;
use App\Infrastructure\Routing\Infrastructure\Matcher\DefaultRouteMatcher;
use App\Infrastructure\Routing\Infrastructure\Providers\HomeRouteProvider;
use App\Infrastructure\Routing\Infrastructure\Providers\ItemRouteProvider;
use App\Infrastructure\Routing\Infrastructure\Registration\RouteRegistrar;
use App\Infrastructure\Routing\Infrastructure\Repository\InMemoryRouteRepository;
use App\Infrastructure\Routing\Infrastructure\Resolver\DefaultRouteResolver;
use App\Infrastructure\Routing\Presentation\Http\Contracts\HttpRouteInterface;
use App\Infrastructure\Routing\Presentation\Http\RoutingEngine;

/**
 * RouterKernel
 *
 * Coordinates the complete initialization of the HTTP routing subsystem.
 * This kernel is responsible for:
 *  - Loading and instantiating all registered route providers,
 *  - Gathering, merging and registering all route definitions,
 *  - Creating and wiring the route repository, matcher, resolver, and dispatcher,
 *  - Exposing a fully prepared RoutingEngine ready for use.
 *
 * It is compatible with both static and parametrizable route providers,
 * enabling the injection of custom controller FQCNs (useful for testing/mocking).
 *
 * Example usage (production):
 *     $kernel = new RouterKernel($controllerMap);
 *     $engine = $kernel->engine();
 *
 * Example usage (testing with controller override):
 *     $controllerMap = [
 *         \Tests\Controllers\HomeTestController::class => new \Tests\Controllers\HomeTestController()
 *     ];
 *     $controllerClassMap = [
 *         HomeRouteProvider::class => \Tests\Controllers\HomeTestController::class
 *     ];
 *     $kernel = new RouterKernel($controllerMap, $controllerClassMap);
 *     $engine = $kernel->engine();
 */
final class RouterKernel
{
    /**
     * @var RoutingEngine
     */
    private RoutingEngine $engine;

    /**
     * @var RouteProviderInterface[]
     */
    private array $providers;

    /**
     * RouterKernel constructor.
     *
     * @param array<class-string, object> $controllerMap
     *        Controller instances to be used by the dispatcher.
     * @param array<class-string, class-string> $controllerClassMap
     *        [Optional] FQCNs to inject into parametrizable providers (for test/mocking).
     */
    public function __construct(
        array $controllerMap,
        array $controllerClassMap = []
    ) {
        $this->providers = $this->loadDefaultProviders($controllerClassMap);
        $this->engine = $this->initializeRoutingEngine($controllerMap);
    }

    /**
     * Returns the fully configured RoutingEngine instance.
     *
     * @return RoutingEngine
     */
    public function engine(): RoutingEngine
    {
        return $this->engine;
    }

    /**
     * Loads all default route providers in the system.
     * Delegates provider definition and instantiation to specialized methods.
     *
     * @param array<class-string, class-string> $controllerClassMap
     *        Key: Provider FQCN, Value: Controller FQCN to inject
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
     * Returns the list of default route provider FQCNs for the system.
     *
     * @return array<class-string<RouteProviderInterface>, class-string<RouteProviderInterface>>
     */
    private function getDefaultProviderDefinitions(): array
    {
        return [
            HomeRouteProvider::class => HomeRouteProvider::class,
            // ItemRouteProvider::class => ItemRouteProvider::class,
            // Add more providers here as needed
        ];
    }

    /**
     * Instantiates a provider, injecting a controllerClass if the provider supports it.
     *
     * @param class-string<RouteProviderInterface> $providerFQCN
     * @param class-string|null $controllerClass
     * @return RouteProviderInterface
     */
    private function instantiateProvider(string $providerFQCN, ?string $controllerClass = null): RouteProviderInterface
    {
        $ref = new \ReflectionClass($providerFQCN);

        if ($ref->getConstructor() && $ref->getConstructor()->getNumberOfParameters() > 0) {
            // Parametrizable provider: inject controllerClass if supplied
            return $controllerClass
                ? new $providerFQCN($controllerClass)
                : new $providerFQCN();
        }
        // Static provider: instantiate directly
        return new $providerFQCN();
    }

    /**
     * Fully builds the RoutingEngine with repository, resolver, and dispatcher.
     *
     * @param array<class-string, object> $controllerMap
     * @return RoutingEngine
     */
    private function initializeRoutingEngine(array $controllerMap): RoutingEngine
    {
        $routes = $this->collectRoutesFromProviders($this->providers);
        $repository = $this->registerRoutes($routes);
        $resolver = $this->createResolver($repository);
        $dispatcher = $this->createDispatcher($resolver, $controllerMap);

        return new RoutingEngine($resolver, $dispatcher);
    }

    /**
     * Collects all routes from every provider.
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
     * Registers the given routes in an in-memory repository.
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
     * Creates the route resolver, wired to the given repository and matcher.
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
     * Creates the dispatcher responsible for executing controller actions.
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
