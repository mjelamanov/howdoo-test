<?php

namespace App\Repositories;

use App\ApiToken;
use App\Criteria\SelectActiveTokenCriteria;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class ApiTokenRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class ApiTokenRepositoryEloquent extends BaseRepository implements ApiTokenRepository
{
    /**
     * @inheritDoc
     */
    public function findActiveByToken(string $token): ?ApiToken
    {
        return $this->pushCriteria(new SelectActiveTokenCriteria($token))->first();
    }

    /**
     * @inheritDoc
     */
    public function checkUserTokenExpiration($userId, string $token): bool
    {
        return $this->pushCriteria(new SelectActiveTokenCriteria($token))
            ->scopeQuery(function (ApiToken $model) use ($userId) {
                return $model->where($model->user()->getForeignKeyName(), $userId);
            })->exists();
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ApiToken::class;
    }

    /**
     * Boot up the repository, pushing criteria
     *
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     *
     * @return void
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
