<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\DTO\LoginDTO;
use App\Core\DTO\RegisterDTO;
use App\Core\Request\Auth\LoginRequest;
use App\Core\Request\Auth\RegisterRequest;
use App\Core\Response\ApiResponse;
use App\Domain\User\Resources\UserResource;
use App\Domain\User\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tizix\DataTransferObject\Exceptions\UnknownProperties;
use Tizix\DataTransferObject\Exceptions\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    /**
     * Авторизация пользователя и получение токена доступа.
     *
     * @throws UnknownProperties
     * @throws ValidationException
     * @throws \Exception
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = new LoginDTO($request->validated());

        try {
            $result = $this->authService->login($dto);
        } catch (\Exception $e) {
            if ($e->getCode() === 403) {
                return ApiResponse::forbidden($e->getMessage());
            }
            throw $e;
        }

        return ApiResponse::success([
            'token' => $result['token'],
            'user' => new UserResource($result['user']),
        ]);
    }

    /**
     * Регистрация нового пользователя и получение токена доступа.
     *
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = new RegisterDTO($request->validated());

        $result = $this->authService->register($dto);

        return ApiResponse::success([
            'token' => $result['token'],
            'user' => new UserResource($result['user']),
        ], 'User registered successfully.', 201);
    }

    /**
     * Получить текущего авторизованного пользователя.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    /**
     * Отозвать текущий токен доступа.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(message: 'Logged out successfully.');
    }
}
