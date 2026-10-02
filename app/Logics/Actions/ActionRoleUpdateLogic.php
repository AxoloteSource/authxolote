<?php

namespace App\Logics\Actions;

use App\Data\ActionRole\UpdateActionRoleData;
use App\Models\Action;
use App\Models\ActionRoleOverride;
use AxoloteSource\Logics\Logics\UpdateLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

class ActionRoleUpdateLogic extends UpdateLogic
{
    public Action|Model $model;

    public function __construct(Action $action)
    {
        parent::__construct($action);
    }

    public function run(UpdateActionRoleData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function before(): bool
    {
        $roleId = $this->input->roleId;
        $action = $this->model->find($this->input->id);
        $this->input->active ? $action->roles()->syncWithoutDetaching([$roleId]) : $action->roles()->detach($roleId);

        ActionRoleOverride::updateOrCreate(
            ['role_id' => $roleId, 'action_id' => $action->id],
            ['active' => $this->input->active]
        );

        return true;
    }
}
