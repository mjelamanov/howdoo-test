<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ApiTokenRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param \App\Repositories\UserRepository $userRepository
     * @param \App\Repositories\ApiTokenRepository $apiTokenRepository
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(
        Request $request,
        ConnectionInterface $connection,
        UserRepository $userRepository,
        ApiTokenRepository $apiTokenRepository
    ) {
        $this->validate($request, [
            'email' => 'bail|required|exists:users',
        ]);

        $email = $request->input('email');

        $connection->beginTransaction();

        /** @var \App\User $user */
        $user = $userRepository->scopeQuery(function (Model $model) use ($email) {
            return $model->where('email', $email);
        })->first();

        $apiToken = $apiTokenRepository->create([
            $user->getForeignKey() => $user->getKey(),
            'token' => Str::random(),
            'expires_at' => Carbon::now()->addMinutes(config('auth.api_token_expire')),
        ]);

        $connection->commit();

        return new JsonResponse([
            'email' => $request->input('email'),
            'token' => $apiToken->token,
            'until' => $apiToken->expires_at->getTimestamp(),
        ]);
    }
}
