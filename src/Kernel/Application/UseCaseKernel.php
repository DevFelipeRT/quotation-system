<?php

declare(strict_types=1);

namespace App\Kernel\Application;

use App\Domains\Item\Application\UseCases\CreateUseCase;
use App\Domains\Item\Application\UseCases\DeleteUseCase;
use App\Domains\Item\Application\UseCases\ListUseCase;
use App\Domains\Item\Application\UseCases\UpdateUseCase;
use App\Domains\Item\Infrastructure\Persistence\PdoItemRepository;
use App\Infrastructure\Database\Domain\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * UseCaseKernel
 *
 * Application-level kernel responsible for instantiating use cases related
 * to the Item aggregate. Repositories are wired with proper infrastructure
 * dependencies, ensuring clean separation between application and data layers.
 */
final class UseCaseKernel
{
    private readonly CreateUseCase $createUseCase;
    private readonly ListUseCase $listUseCase;
    private readonly UpdateUseCase $updateUseCase;
    private readonly DeleteUseCase $deleteUseCase;

    /**
     * Initializes use cases with a shared repository instance.
     *
     * @param DatabaseConnectionInterface $connection A connection strategy abstraction
     * @param LoggerInterface             $logger     Logger for persistence diagnostics
     */
    public function __construct(DatabaseConnectionInterface $connection, LoggerInterface $logger)
    {
        $repository = new PdoItemRepository($connection, $logger);

        $this->createUseCase = new CreateUseCase($repository);
        $this->listUseCase   = new ListUseCase($repository);
        $this->updateUseCase = new UpdateUseCase($repository);
        $this->deleteUseCase = new DeleteUseCase($repository);
    }

    /**
     * Provides the use case for creating items.
     */
    public function create(): CreateUseCase
    {
        return $this->createUseCase;
    }

    /**
     * Provides the use case for listing items.
     */
    public function list(): ListUseCase
    {
        return $this->listUseCase;
    }

    /**
     * Provides the use case for updating items.
     */
    public function update(): UpdateUseCase
    {
        return $this->updateUseCase;
    }

    /**
     * Provides the use case for deleting items.
     */
    public function delete(): DeleteUseCase
    {
        return $this->deleteUseCase;
    }
}
