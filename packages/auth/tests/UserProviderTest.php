<?php

namespace HowDoo\Auth;

use App\Repositories\ApiTokenRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderInterface;
use PHPUnit\Framework\TestCase;

class UserProviderTest extends TestCase
{
    /**
     * @return void
     */
    public function testRetrieveById(): void
    {
        $provider = $this->prophesize(UserProviderInterface::class);
        $repository = $this->prophesize(ApiTokenRepository::class);
        $identifier = 1;

        $userProvider = new UserProvider($provider->reveal(), $repository->reveal());
        $userProvider->retrieveById($identifier);

        $provider->retrieveById($identifier)->shouldHaveBeenCalled();
    }

    /**
     * @return void
     */
    public function testProxyValidateCredentials(): void
    {
        $provider = $this->prophesize(UserProviderInterface::class);
        $repository = $this->prophesize(ApiTokenRepository::class);
        $user = $this->prophesize(Authenticatable::class);

        $userProvider = new UserProvider($provider->reveal(), $repository->reveal());
        $userProvider->validateCredentials($user->reveal(), []);

        $provider->validateCredentials($user, [])->shouldHaveBeenCalled();
    }

    /**
     * @return void
     */
    public function testValidateCredentials(): void
    {
        $provider = $this->prophesize(UserProviderInterface::class);
        $repository = $this->prophesize(ApiTokenRepository::class);
        $user = $this->prophesize(Authenticatable::class);
        $userId = 10;
        $token = 'token';

        $user->getAuthIdentifier()->willReturn($userId);
        $repository->checkUserTokenExpiration($userId, $token)->willReturn(false);

        $userProvider = new UserProvider($provider->reveal(), $repository->reveal());
        $validated = $userProvider->validateCredentials($user->reveal(), compact('token'));

        $repository->checkUserTokenExpiration($userId, $token)->shouldHaveBeenCalled();
        $this->assertFalse($validated);
    }

    /**
     * @return void
     */
    public function testProxyRetrieveByCredentials(): void
    {
        $provider = $this->prophesize(UserProviderInterface::class);
        $repository = $this->prophesize(ApiTokenRepository::class);

        $userProvider = new UserProvider($provider->reveal(), $repository->reveal());
        $userProvider->retrieveByCredentials([]);

        $provider->retrieveByCredentials([])->shouldHaveBeenCalled();
    }

    /**
     * @return void
     */
    public function testRetrieveByCredentials(): void
    {
        $provider = $this->prophesize(UserProviderInterface::class);
        $repository = $this->prophesize(ApiTokenRepository::class);
        $token = 'token';

        $userProvider = new UserProvider($provider->reveal(), $repository->reveal());
        $userProvider->retrieveByCredentials(compact('token'));

        $repository->findActiveByToken($token)->shouldHaveBeenCalled();
    }

    /**
     * @return void
     */
    public function testRetrieveByToken(): void
    {
        $provider = $this->prophesize(UserProviderInterface::class);
        $repository = $this->prophesize(ApiTokenRepository::class);
        $identifier = 1;
        $token = 'token';

        $userProvider = new UserProvider($provider->reveal(), $repository->reveal());
        $userProvider->retrieveByToken($identifier, $token);

        $provider->retrieveByToken($identifier, $token)->shouldHaveBeenCalled();
    }

    /**
     * @return void
     */
    public function testUpdateRememberToken(): void
    {
        $provider = $this->prophesize(UserProviderInterface::class);
        $repository = $this->prophesize(ApiTokenRepository::class);
        $user = $this->prophesize(Authenticatable::class);
        $token = 'token';

        $userProvider = new UserProvider($provider->reveal(), $repository->reveal());
        $userProvider->updateRememberToken($user->reveal(), $token);

        $provider->updateRememberToken($user, $token)->shouldHaveBeenCalled();
    }
}
