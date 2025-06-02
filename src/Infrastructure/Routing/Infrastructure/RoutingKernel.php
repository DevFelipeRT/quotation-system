<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure;

use App\Infrastructure\Routing\Application\Contracts\RoutingServiceInterface;
use App\Infrastructure\Routing\Application\Service\RoutingService;
use App\Infrastructure\Routing\Infrastructure\Contracts\ControllerFactoryInterface;
use App\Infrastructure\Routing\Infrastructure\Contracts\RouteDispatcherInterface;
use App\Infrastructure\Routing\Infrastructure\Contracts\RouteProviderInterface;
use App\Infrastructure\Routing\Infrastructure\Contracts\RouteRepositoryInterface;
use App\Infrastructure\Routing\Infrastructure\Contracts\RouteResolverInterface;
use App\Infrastructure\Routing\Infrastructure\ControllerFactory;
use App\Infrastructure\Routing\Infrastructure\DefaultRouteDispatcher;
use App\Infrastructure\Routing\Infrastructure\DefaultRouteResolver;
use App\Infrastructure\Routing\Infrastructure\InMemoryRouteRepository;
use App\Infrastructure\Routing\Infrastructure\RouteRegistrar;
use App\Shared\Container\Domain\Contracts\ContainerInterface;
use App\Shared\Discovery\Application\Contracts\ScannerFacadeInterface;

/**
 * RoutingKernel
 *
 * Centralizes the initialization, discovery, wiring, and management of all routing module dependencies.
 */
class RoutingKernel
{
    /** @var RouteProviderInterface[] */
    private array $providers = [];

    private ?RouteRepositoryInterface $repository = null;
    private ?RouteResolverInterface $resolver = null;
    private ?RouteDispatcherInterface $dispatcher = null;
    private ?RoutingService $engine = null;
    private ?ControllerFactoryInterface $controllerFactory = null;

    /** @var bool */
    private bool $booted = false;

    /**
     * Optionally allow injection of scanner and container for auto-discovery.
     */
    public function __construct(
        ?ScannerFacadeInterface $scanner = null,
        ?ContainerInterface $container = null,
        ?string $providerNamespace = null
    ) {
        // Descobre e registra automaticamente todos os route providers (se scanner/container fornecidos)
        if ($scanner && $container) {
            $providers = $this->discoverProviders($scanner, $providerNamespace);

            foreach ($providers as $fqcnValue) {
                $this->registerProviders(
                    $container->has($fqcnValue)
                        ? $container->get($fqcnValue)
                        : new $fqcnValue()
                );
            }

            $this->controllerFactory = new ControllerFactory($container);
        }
    }

    /**
     * Registers one or more route providers.
     *
     * @param RouteProviderInterface ...$providers
     * @return void
     */
    public function registerProviders(RouteProviderInterface ...$providers): void
    {
        foreach ($providers as $provider) {
            $this->providers[] = $provider;
        }
    }

    /**
     * Boots and wires all routing components.
     * Should be called after all providers are registered.
     */
    public function boot(): void
    {
        if ($this->booted) {
            throw new \LogicException('RoutingKernel has already been booted.');
        }

        // 1. Repository
        $this->repository = new InMemoryRouteRepository();

        // 2. Registrar: agrega todas as rotas dos providers e registra
        $allRoutes = [];
        foreach ($this->providers as $provider) {
            foreach ($provider->provideRoutes() as $route) {
                $allRoutes[] = $route;
            }
        }
        $registrar = new RouteRegistrar($allRoutes);
        $registrar->register($this->repository);

        // 3. Resolver
        $this->resolver = new DefaultRouteResolver($this->repository);

        // 4. Dispatcher
        $this->dispatcher = new DefaultRouteDispatcher($this->controllerFactory);

        // 5. Service
        $this->engine = new RoutingService($this->resolver, $this->dispatcher);

        $this->booted = true;
    }

    /**
     * Returns the RoutingService instance.
     */
    public function engine(): RoutingServiceInterface
    {
        $this->assertBooted();
        return $this->engine;
    }

    /**
     * Returns the repository instance.
     */
    public function repository(): RouteRepositoryInterface
    {
        $this->assertBooted();
        return $this->repository;
    }

    /**
     * Returns the resolver instance.
     */
    public function resolver(): RouteResolverInterface
    {
        $this->assertBooted();
        return $this->resolver;
    }

    /**
     * Returns the dispatcher instance.
     */
    public function dispatcher(): RouteDispatcherInterface
    {
        $this->assertBooted();
        return $this->dispatcher;
    }

    /**
     * Returns the controller factory, if available.
     */
    public function controllerFactory(): ?ControllerFactoryInterface
    {
        return $this->controllerFactory;
    }

    /**
     * Asserts the kernel is booted before returning any component.
     */
    private function assertBooted(): void
    {
        if (!$this->booted) {
            throw new \RuntimeException('RoutingKernel is not booted.');
        }
    }

    private function discoverProviders(
        ScannerFacadeInterface $scanner,
        ?string $providerNamespace = null
    ): array {
            $providerFqcnCollection = $scanner->implementing(
                RouteProviderInterface::class,
                $providerNamespace
            );
        return array_map(fn($fqcn) => new $fqcn(), $providerFqcnCollection->toArray());
    }
}
