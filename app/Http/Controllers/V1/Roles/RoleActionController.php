<?php

namespace App\Http\Controllers\V1\Roles;

use App\Data\Role\StoreRoleActionData;
use App\Http\Controllers\Controller;
use App\Logics\Roles\RoleActionStoreLogic;
use Illuminate\Http\JsonResponse;

/**
 * Role and action assignments.
 *
 * @group Roles
 */
class RoleActionController extends Controller
{
    /**
     * Attach actions to roles.
     *
     * Creates the roles and actions that do not exist yet and syncs the given
     * actions with each role. When `force` is true, previous action overrides
     * for the affected roles are removed before syncing.
     *
     * @authenticated
     *
     * @bodyParam roles object required Map of role keys to the list of action names assigned to that role. Example: {"admin": ["auth.users.index", "auth.users.store"], "editor": ["auth.users.index"]}
     * @bodyParam force boolean optional Whether to discard existing action overrides for the affected roles. Defaults to false. Example: false
     *
     * @response status=201 scenario="Actions attached" {
     *   "status": "OK",
     *   "message": null,
     *   "data": null
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "roles": [
     *       "The roles field is required."
     *     ]
     *   }
     * }
     */
    public function store(StoreRoleActionData $data, RoleActionStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
