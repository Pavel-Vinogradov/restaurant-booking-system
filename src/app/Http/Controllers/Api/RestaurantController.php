<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\Request\Restaurant\RestaurantIndexRequest;
use App\Core\Request\Restaurant\StoreRestaurantRequest;
use App\Core\Request\Restaurant\UpdateRestaurantRequest;
use App\Core\Response\ApiResponse;
use App\Domain\Restaurant\DTOs\CreateRestaurantDTO;
use App\Domain\Restaurant\DTOs\UpdateRestaurantDTO;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Restaurant\Resources\RestaurantResource;
use App\Domain\Restaurant\Services\RestaurantService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use Tizix\DataTransferObject\Exceptions\UnknownProperties;
use Tizix\DataTransferObject\Exceptions\ValidationException;

class RestaurantController extends Controller
{
    public function __construct(
        private readonly RestaurantService $restaurantService,
    ) {}

    /**
     * Список ресторанов текущего пользователя
     *
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function index(RestaurantIndexRequest $request): JsonResponse
    {
        $pagination = $request->toDTO();

        /** @var LengthAwarePaginator<int, Restaurant> $result */
        $result = $this->restaurantService->getUserRestaurants(
            $request->user()->id,
            $pagination
        );

        return ApiResponse::success([
            'items' => RestaurantResource::collection($result->items()),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
        ]);
    }

    /**
     * Создать новый ресторан
     *
     * @throws UnknownProperties
     * @throws ValidationException
     * @throws Throwable
     */
    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        $dto = new CreateRestaurantDTO($request->validated());

        $restaurant = $this->restaurantService->createWithOwner(
            $dto,
            $request->user()->id
        );

        return ApiResponse::success(
            new RestaurantResource($restaurant),
            'Ресторан успешно создан.',
            201
        );
    }

    /**
     * Просмотр ресторана
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $restaurantId = (int) $id;

        $restaurant = $this->restaurantService->findByIdWithRelations($restaurantId);

        if ($restaurant === null) {
            return ApiResponse::notFound('Ресторан не найден.');
        }

        $this->authorize('view', $restaurant);

        return ApiResponse::success(
            new RestaurantResource($restaurant)
        );
    }

    /**
     * Обновить ресторан
     *
     * @throws UnknownProperties
     * @throws ValidationException
     * @throws Throwable
     */
    public function update(UpdateRestaurantRequest $request, string $id): JsonResponse
    {
        $restaurantId = (int) $id;

        if (! $this->restaurantService->exists($restaurantId)) {
            return ApiResponse::notFound('Ресторан не найден.');
        }

        $dto = new UpdateRestaurantDTO($request->validated());

        $restaurant = $this->restaurantService->updateWithHours($restaurantId, $dto);

        return ApiResponse::success(
            new RestaurantResource($restaurant),
            'Ресторан успешно обновлён.'
        );
    }

    /**
     * Удалить ресторан
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $restaurantId = (int) $id;

        $restaurant = $this->restaurantService->findById($restaurantId);

        if ($restaurant === null) {
            return ApiResponse::notFound('Ресторан не найден.');
        }

        $this->authorize('delete', $restaurant);

        $this->restaurantService->delete($restaurantId);

        return ApiResponse::success(
            message: 'Ресторан успешно удалён.'
        );
    }
}
