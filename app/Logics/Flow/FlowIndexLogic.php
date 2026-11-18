<?php

namespace App\Logics\Flow;

use App\Models\Action;
use AxoloteSource\Logics\Data\Flow\FlowIndexData;
use AxoloteSource\Logics\Logics\Flow\FlowIndexLogicBase;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class FlowIndexLogic extends FlowIndexLogicBase
{
    protected Data|FlowIndexData $input;

    public function run(Data|FlowIndexData $input): JsonResponse
    {
        return parent::logic($input);
    }

    public function allowedModels(): array
    {
        return [
            'actions' => Action::class,
        ];
    }

    public function resources(): array
    {
        return [];
    }

    public function searchColum(): array
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
            'actions' => 'auth.action.index',
        ];
    }

    public function filtersModel(): array
    {
        return [];
    }
}
