<?php

namespace HowDoo\Auth;

use App\Repositories\ApiTokenRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderInterface;

/**
 * Class UserProvider
 *
 * @author Mirlan Jelamanov <mirlan.jelamanov@gmail.com>
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    protected $provider;

    /**
     * @var \App\Repositories\ApiTokenRepository
     */
    protected $apiTokenRepo;

    /**
     * UserProvider constructor.
     *
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param \App\Repositories\ApiTokenRepository $apiTokenRepo
     */
    public function __construct(UserProviderInterface $provider, ApiTokenRepository $apiTokenRepo)
    {
        $this->provider = $provider;
        $this->apiTokenRepo = $apiTokenRepo;
    }

    /**
     * @inheritDoc
     */
    public function retrieveById($identifier)
    {
        return $this->provider->retrieveById($identifier);
    }

    /**
     * @inheritDoc
     */
    public function retrieveByToken($identifier, $token)
    {
        return $this->provider->retrieveByToken($identifier, $token);
    }

    /**
     * @inheritDoc
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * @inheritDoc
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials['token'])) {
            return $this->provider->retrieveByCredentials($credentials);
        }

        $apiToken = $this->apiTokenRepo->findActiveByToken($credentials['token']);

        return $apiToken ? $apiToken->user : null;
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (isset($credentials['token'])) {
            return $this->apiTokenRepo->checkUserTokenExpiration($user->getAuthIdentifier(), $credentials['token']);
        }

        return $this->provider->validateCredentials($user, $credentials);
    }
}
