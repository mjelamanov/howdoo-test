<?php

use App\ApiToken;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class ApiTokensTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param \App\Repositories\UserRepository $repository
     *
     * @return void
     */
    public function run(UserRepository $repository)
    {
        $users = $repository->scopeQuery(function (Model $model) {
            return $model->inRandomOrder()->take(5);
        })->all();

        /** @var \App\User $user */
        foreach ($users as $user) {
            factory(ApiToken::class, rand(1, 3))->create([
                $user->getForeignKey() => $user->getKey(),
            ]);
        }
    }
}
