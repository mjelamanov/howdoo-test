<?php

namespace App\Criteria;

use App\Document;
use App\Enums\DocumentStatus;
use App\User;
use Illuminate\Database\Query\Builder;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class SelectUserDocumentsCriteria.
 *
 * @package namespace App\Criteria;
 */
class SelectUserDocumentsCriteria implements CriteriaInterface
{
    /**
     * @var \App\User
     */
    private $user;

    /**
     * SelectUserDocumentsCriteria constructor.
     *
     * @param \App\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Apply criteria in query repository
     *
     * @param \Illuminate\Database\Query\Builder              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $query = $model->where('status', DocumentStatus::PUBLISHED())
            ->where($this->user->getForeignKey(), '<>', $this->user->getKey());

        return $model->where($this->user->getForeignKey(), $this->user->getKey())->union($query);
    }
}
