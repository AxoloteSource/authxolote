<?php

namespace App\Logics\Flow;

use App\Models\Action;
use App\Models\Role;
use AxoloteSource\Logics\Data\Flow\FlowByIdData;
use AxoloteSource\Logics\Logics\Flow\FlowDeleteLogicBase;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class FlowDeleteLogic extends FlowDeleteLogicBase
{
    protected Data|FlowByIdData $input;

    public function run(Data|FlowByIdData $input): JsonResponse
    {
        return parent::logic($input);
    }

    public function allowedModels(): array
    {
        return [
            'roles' => Role::class,
            'actions' => Action::class,
        ];
    }

    public function resources(): array
    {
        return [];
    }

    public function publicModels(): array
    {
        return [];
    }

    public function isAllow(): array
    {
        return [
            'roles' => 'auth.role.delete',
            'actions' => 'auth.action.delete',
        ];
    }
}
