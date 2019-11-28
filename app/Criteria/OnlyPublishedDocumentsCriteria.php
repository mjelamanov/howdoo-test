<?php

namespace App\Criteria;

use App\Enums\DocumentStatus;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class OnlyPublishedDocumentsCriteria.
 *
 * @package namespace App\Criteria;
 */
class OnlyPublishedDocumentsCriteria implements CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        return $model->where('status', DocumentStatus::PUBLISHED());
    }
}
