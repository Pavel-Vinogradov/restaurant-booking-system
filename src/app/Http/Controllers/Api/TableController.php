<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\Request\Restaurant\StoreTableRequest;
use App\Core\Request\Restaurant\TableIndexRequest;
use App\Core\Request\Restaurant\UpdateTableRequest;
use App\Core\Response\ApiResponse;
use App\Domain\Restaurant\DTOs\CreateTableDTO;
use App\Domain\Restaurant\DTOs\UpdateTableDTO;
use App\Domain\Restaurant\Resources\TableResource;
use App\Domain\Restaurant\Services\TableService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use Tizix\DataTransferObject\Exceptions\UnknownProperties;
use Tizix\DataTransferObject\Exceptions\ValidationException;

class TableController extends Controller
{
    public function __construct(
        private readonly TableService $tableService,
    ) {}

    /**
     * Список столов ресторана
     *
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function index(TableIndexRequest $request, int $restaurantId): JsonResponse
    {
        // Пагинация только если явно запрошена
        $pagination = $request->filled('page') || $request->filled('per_page')
            ? $request->toDTO()
            : null;

        /** @phpstan-ignore-next-line */
        $result = $this->tableService->getRestaurantTables($restaurantId, $pagination);

        // Фильтрация по доступности
        if ($request->boolean('available_only')) {
            $tables = $this->tableService->getAvailableTables($restaurantId);

            return ApiResponse::success(TableResource::collection($tables));
        }

        if ($request->filled('min_capacity')) {
            $minCapacity = $request->integer('min_capacity');
            $maxCapacity = $request->integer('max_capacity');
            $tables = $this->tableService->getTablesByCapacity($restaurantId, $minCapacity, $maxCapacity ?: null);

            return ApiResponse::success(TableResource::collection($tables));
        }

        // Если пришла пагинация — возвращаем пагинированный ответ
        if ($result instanceof LengthAwarePaginator) {
            return ApiResponse::success([
                'items' => TableResource::collection($result->items()),
                'pagination' => [
                    'current_page' => $result->currentPage(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                ],
            ]);
        }

        return ApiResponse::success(TableResource::collection($result));
    }

    /**
     * Создать новый стол
     *
     * @throws UnknownProperties
     * @throws ValidationException
     * @throws Throwable
     */
    public function store(StoreTableRequest $request): JsonResponse
    {
        $dto = new CreateTableDTO($request->validated());

        try {
            $table = $this->tableService->createTable($dto);

            return ApiResponse::success(
                new TableResource($table),
                'Стол успешно создан.',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * Просмотр стола
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $table = $this->tableService->findById($id);

        if ($table === null) {
            return ApiResponse::notFound('Стол не найден.');
        }

        $this->authorize('view', $table->restaurant);

        return ApiResponse::success(new TableResource($table));
    }

    /**
     * Обновить стол
     *
     * @throws UnknownProperties
     * @throws ValidationException
     * @throws Throwable
     */
    public function update(UpdateTableRequest $request, int $id): JsonResponse
    {
        $dto = new UpdateTableDTO($request->validated());

        try {
            $table = $this->tableService->updateTable($id, $dto);

            if ($table === null) {
                return ApiResponse::notFound('Стол не найден.');
            }

            return ApiResponse::success(
                new TableResource($table),
                'Стол успешно обновлён.'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * Удалить стол
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $table = $this->tableService->findById($id);

        if ($table === null) {
            return ApiResponse::notFound('Стол не найден.');
        }

        $this->authorize('view', $table->restaurant);

        $this->tableService->delete($id);

        return ApiResponse::success(
            message: 'Стол успешно удалён.'
        );
    }
}
