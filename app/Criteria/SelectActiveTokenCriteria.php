<?php

namespace App\Criteria;

use Carbon\Carbon;
use DateTimeInterface;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class SelectActiveTokenCriteria.
 *
 * @package namespace App\Criteria;
 */
class SelectActiveTokenCriteria implements CriteriaInterface
{
    /**
     * @var string
     */
    protected $token;

    /**
     * SelectActiveTokenCriteria constructor.
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        return $model->where('token', $this->token)
            ->where('expires_at', '>', $this->getCurrentTime());
    }

    /**
     * @return \DateTimeInterface
     */
    protected function getCurrentTime(): DateTimeInterface
    {
        return Carbon::now();
    }
}
