<?php

declare(strict_types=1);

namespace App\Shared\Container\Infrastructure;

use App\Shared\Container\Domain\Contracts\ContainerInterface;
use App\Shared\Container\Domain\Contracts\ServiceProviderInterface;
use App\Shared\Container\Infrastructure\Bindings\BindingType;
use App\Shared\Container\Infrastructure\Scope\SingletonScope;
use App\Shared\Container\Infrastructure\Scope\TransientScope;

class ContainerBuilder
{
    /**
     * @var array<string, array{callable, bool}> [$id => [$factory, $singleton]]
     */
    protected array $bindings = [];

    /**
     * @var ServiceProviderInterface[]
     */
    protected array $providers = [];

    /**
     * @var array<string, object>
     */
    protected array $scopes = [];

    public function __construct()
    {
        $this->scopes = [
            BindingType::SINGLETON->value => new SingletonScope(),
            BindingType::TRANSIENT->value => new TransientScope(),
        ];
    }

    /**
     * Registra binding. Defina escopos antes de registrar bindings!
     */
    public function bind(string $id, callable $factory, bool $singleton = false): self
    {
        $this->bindings[$id] = [$factory, $singleton];
        return $this;
    }

    public function singleton(string $id, callable $factory): self
    {
        return $this->bind($id, $factory, true);
    }

    public function addProvider(ServiceProviderInterface $provider): self
    {
        $this->providers[] = $provider;
        return $this;
    }

    /**
     * Define/override scope. ATENÇÃO: limpa TODOS os bindings já registrados!
     */
    public function addScope(BindingType $type, object $scope): self
    {
        $this->scopes[$type->value] = $scope;
        // Ao alterar escopo, para garantir consistência, limpa todos os bindings
        $this->bindings = [];
        return $this;
    }

    /**
     * Gera o Container, registrando todos os bindings e providers.
     */
    public function build(): Container
    {
        $container = new Container(null, null);

        // Registra escopos customizados (se houver)
        foreach ($this->scopes as $key => $scope) {
            $container->setScope(BindingType::from($key), $scope);
        }

        // Registra bindings explicitamente
        foreach ($this->bindings as $id => [$factory, $singleton]) {
            if ($singleton) {
                $container->singleton($id, $factory);
            } else {
                $container->bind($id, $factory, false);
            }
        }

        // Registra providers
        foreach ($this->providers as $provider) {
            $container->registerProvider($provider);
        }

        return $container;
    }
}
