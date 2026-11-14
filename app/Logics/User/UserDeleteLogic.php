<?php

namespace App\Logics\User;

use App\Data\User\UserDeleteData;
use App\Models\User;
use AxoloteSource\Logics\Enums\Http;
use AxoloteSource\Logics\Logics\DeleteLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class UserDeleteLogic extends DeleteLogic
{
    public Model|User $model;

    public UserDeleteData|Data $input;

    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function run(UserDeleteData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function before(): bool
    {
        if ($this->model->id === auth()->id()) {
            return $this->error(__('You cannot delete your own user.'), status: Http::UnprocessableEntity);
        }

        if ($this->model->isRoot() && ! auth()->user()?->isRoot()) {
            return $this->error(__('You may not delete the root user.'), status: Http::Forbidden);
        }

        $this->model->forceFill([
            'deleted_email' => $this->model->email,
            'email' => 'deleted+'.$this->model->id.'@deleted.local',
        ])->save();

        return true;
    }
}
