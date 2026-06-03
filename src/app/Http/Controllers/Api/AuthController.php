<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\DTO\ChangePasswordDTO;
use App\Core\DTO\ForgotPasswordDTO;
use App\Core\DTO\LoginDTO;
use App\Core\DTO\RegisterDTO;
use App\Core\DTO\ResetPasswordDTO;
use App\Core\DTO\SendPhoneCodeDTO;
use App\Core\DTO\UpdateProfileDTO;
use App\Core\DTO\VerifyPhoneDTO;
use App\Core\Request\Auth\ChangePasswordRequest;
use App\Core\Request\Auth\ForgotPasswordRequest;
use App\Core\Request\Auth\LoginRequest;
use App\Core\Request\Auth\RegisterRequest;
use App\Core\Request\Auth\ResetPasswordRequest;
use App\Core\Request\Auth\SendPhoneCodeRequest;
use App\Core\Request\Auth\UpdateProfileRequest;
use App\Core\Request\Auth\VerifyPhoneRequest;
use App\Core\Response\ApiResponse;
use App\Domain\User\Exceptions\BlockedUserException;
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
    ) {}

    /**
     * Авторизация пользователя и получение токена доступа.
     *
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = new LoginDTO($request->validated());

        try {
            $result = $this->authService->login($dto);
        } catch (BlockedUserException $e) {
            return ApiResponse::forbidden($e->getMessage());
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
    public function user(Request $request): JsonResponse
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

    /**
     * Отправить ссылку для сброса пароля.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $dto = new ForgotPasswordDTO($request->validated());

        $this->authService->forgotPassword($dto);

        return ApiResponse::success(message: 'Если email существует, инструкции отправлены.');
    }

    /**
     * Сбросить пароль по токену.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $dto = new ResetPasswordDTO($request->validated());

        $this->authService->resetPassword($dto);

        return ApiResponse::success(message: 'Пароль успешно изменён.');
    }

    /**
     * Обновить профиль пользователя.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $dto = new UpdateProfileDTO($request->validated());

        $user = $this->authService->updateProfile($request->user(), $dto);

        return ApiResponse::success(new UserResource($user), 'Профиль обновлён.');
    }

    /**
     * Сменить пароль.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $dto = new ChangePasswordDTO($request->validated());

        $this->authService->changePassword($request->user(), $dto);

        return ApiResponse::success(message: 'Пароль изменён.');
    }

    /**
     * Отправить SMS-код для верификации телефона.
     */
    public function sendPhoneCode(SendPhoneCodeRequest $request): JsonResponse
    {
        $dto = new SendPhoneCodeDTO($request->validated());

        $code = $this->authService->sendPhoneCode($dto);

        return ApiResponse::success([
            'code' => $code,
        ], 'Код отправлен. В production уберите code из ответа.');
    }

    /**
     * Подтвердить телефон по коду.
     */
    public function verifyPhone(VerifyPhoneRequest $request): JsonResponse
    {
        $dto = new VerifyPhoneDTO($request->validated());

        $this->authService->verifyPhone($request->user(), $dto);

        return ApiResponse::success(message: 'Телефон подтверждён.');
    }
}
