<?php

namespace App\Logics\Roles;

use App\Data\Role\StoreRoleActionData;
use App\Models\Action;
use App\Models\ActionRoleOverride;
use App\Models\Role;
use AxoloteSource\Logics\Enums\Http;
use AxoloteSource\Logics\Logics\Logic;
use AxoloteSource\Logics\Traits\OnlyWithAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Spatie\LaravelData\Data;

class RoleActionStoreLogic extends Logic
{
    use OnlyWithAction;

    protected Data|StoreRoleActionData $input;

    public function run(StoreRoleActionData|Data $input): JsonResponse
    {
        return parent::logic($input);
    }

    protected function action(): Logic
    {
        $force = $this->input->force;

        $this->input->roles->each(function ($actions, $roleKey) use ($force) {
            $role = Role::firstOrCreate(
                ['key' => strtolower($roleKey)],
                ['name' => ucfirst($roleKey)]
            );

            if ($force) {
                ActionRoleOverride::where('role_id', $role->id)->delete();
            }

            $actionIds = collect($actions)->map(function ($value, $key) {
                $name = is_string($key) ? $key : $value;
                $description = is_string($key) ? $value : null;

                return Action::updateOrCreate(
                    ['name' => $name],
                    ['description' => $description]
                )->id;
            });

            $overrides = ActionRoleOverride::where('role_id', $role->id)
                ->get()
                ->keyBy('action_id');

            $syncedActionIds = $actionIds
                ->reject(fn (string $actionId): bool => $overrides->has($actionId) && ! $overrides->get($actionId)->active)
                ->merge($overrides->where('active', true)->keys())
                ->unique()
                ->values();

            $role->actions()->sync($syncedActionIds);
        });

        return $this;
    }

    protected function response(): JsonResponse
    {
        return Response::success(data: $this->withResource(), status: Http::Created);
    }
}
