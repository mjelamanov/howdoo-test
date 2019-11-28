<?php

namespace App\Repositories;

use App\ApiToken;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface ApiTokenRepository.
 *
 * @package namespace App\Repositories;
 */
interface ApiTokenRepository extends RepositoryInterface
{
    /**
     * @param string $token
     *
     * @return \App\ApiToken|null
     */
    public function findActiveByToken(string $token): ?ApiToken;

    /**
     * @param int|string $userId
     * @param string $token
     *
     * @return bool
     */
    public function checkUserTokenExpiration($userId, string $token): bool;
}
