<?php

namespace App\Logics\Auth;

use App\Http\Resources\Auth\MeResource;
use AxoloteSource\Logics\Data\EmptyData;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Http\JsonResponse;

class MeLogic extends Logic
{
    use OnlyWithAction;

    public function run(): JsonResponse
    {
        return parent::logic(new EmptyData);
    }

    protected function action(): Logic
    {
        $this->model = $this->user();

        return $this;
    }

    protected function withResource(): MeResource
    {
        return new MeResource($this->model);
    }
}
