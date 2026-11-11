<?php

namespace App\Http\Controllers\V1\Actions;

use App\Data\ActionRole\UpdateActionRoleData;
use App\Data\Actions\IndexActionData;
use App\Http\Controllers\Controller;
use App\Logics\Actions\ActionRoleIndexLogic;
use App\Logics\Actions\ActionRoleUpdateLogic;
use Illuminate\Http\JsonResponse;

/**
 * Actions associated with a role.
 *
 * @group Roles
 */
class ActionRoleController extends Controller
{
    /**
     * List the actions of a role.
     *
     * Returns a paginated collection of actions with an `active` flag that
     * indicates whether each action is currently attached to the role.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the role. Example: 0195f51e-4a37-7087-b5f7-57d001537ab7
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: asc
     * @queryParam order_by string Column used to sort the results. Allowed values: name, description. Example: name
     * @queryParam search string Free text search applied to the action name. Example: users
     * @queryParam filters string optional JSON-encoded list of filters. Each item must contain "property", "value" and optionally "operator". Example: [{"property":"name","value":"jane","operator":"="}]
     *
     * @response status=206 scenario="Paginated actions" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab8",
     *       "name": "auth.users.index",
     *       "description": "List users",
     *       "active": true,
     *       "deleted_at": null,
     *       "created_at": "2026-01-15T10:00:00.000000Z",
     *       "updated_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "first_page_url": "https://authxolote.test/api/v1/roles/0195f51e-4a37-7087-b5f7-57d001537ab7/actions?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "https://authxolote.test/api/v1/roles/0195f51e-4a37-7087-b5f7-57d001537ab7/actions?page=1",
     *   "links": [],
     *   "next_page_url": null,
     *   "path": "https://authxolote.test/api/v1/roles/0195f51e-4a37-7087-b5f7-57d001537ab7/actions",
     *   "per_page": 15,
     *   "prev_page_url": null,
     *   "to": 1,
     *   "total": 1,
     *   "columns": [
     *     {
     *       "accessor": "description",
     *       "title": "Descripción",
     *       "sortable": true
     *     },
     *     {
     *       "accessor": "name",
     *       "title": "Nombre",
     *       "sortable": true
     *     }
     *   ]
     * }
     * @response status=404 scenario="Role not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function index(IndexActionData $data, ActionRoleIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Toggle an action for a role.
     *
     * Attaches the action to the role when `active` is true and detaches it
     * when false. The override is stored so the change survives future action
     * syncs.
     *
     * @authenticated
     *
     * @urlParam roleId string required Identifier of the role. Example: 0195f51e-4a37-7087-b5f7-57d001537ab7
     * @urlParam id string required Identifier of the action. Example: 0195f51e-4a37-7087-b5f7-57d001537ab8
     *
     * @bodyParam active boolean required Whether the action must be enabled for the role. Example: true
     *
     * @response status=200 scenario="Assignment updated" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537ab8",
     *     "name": "auth.users.index",
     *     "description": "List users",
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z",
     *     "deleted_at": null
     *   }
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "active": [
     *       "The active field is required."
     *     ]
     *   }
     * }
     */
    public function update(UpdateActionRoleData $data, ActionRoleUpdateLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
