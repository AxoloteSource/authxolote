<?php

namespace App\Logics\Flow;

use App\Data\Actions\UpdateActionData;
use App\Data\Role\UpdateRoleData;
use App\Models\Action;
use App\Models\Role;
use AxoloteSource\Logics\Logics\Flow\FlowUpdateLogicBase;

class FlowUpdateLogic extends FlowUpdateLogicBase
{
    public function allowedModels(): array
    {
        return [
            'roles' => Role::class,
            'actions' => Action::class,
        ];
    }

    public function validates(): array
    {
        return [
            'roles' => UpdateRoleData::class,
            'actions' => UpdateActionData::class,
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
            'roles' => 'auth.role.update',
            'actions' => 'auth.action.update',
        ];
    }
}
