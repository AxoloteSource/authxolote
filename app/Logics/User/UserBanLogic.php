<?php

namespace App\Logics\User;

use App\Data\User\UserBanData;
use App\Http\Resources\User\UserShowResource;
use App\Models\User;
use AxoloteSource\Logics\Enums\Http;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class UserBanLogic extends Logic
{
    use OnlyWithAction;

    public UserBanData|Data $input;

    private User $user;

    public function run(UserBanData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function initializer(): bool
    {
        $user = User::query()
            ->with(['role', 'userType'])
            ->find($this->input->id);

        if (is_null($user)) {
            return $this->error(message: 'Not Found', status: Http::NotFound);
        }

        if ($user->id === auth()->id()) {
            return $this->error(__('You cannot ban your own user.'), status: Http::UnprocessableEntity);
        }

        $this->user = $user;

        return true;
    }

    protected function action(): Logic
    {
        $this->user->forceFill([
            'banned_at' => $this->user->isBanned() ? null : now(),
        ])->save();

        return $this;
    }

    protected function withResource(): UserShowResource
    {
        return new UserShowResource($this->user->load(['role', 'userType']));
    }
}
