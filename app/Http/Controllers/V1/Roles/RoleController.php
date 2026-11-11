<?php

namespace App\Http\Controllers\V1\Roles;

use App\Http\Controllers\Controller;
use App\Logics\Roles\RoleIndexLogic;
use AxoloteSource\Logics\Data\IndexData;
use Illuminate\Http\JsonResponse;

/**
 * Role management.
 *
 * @group Roles
 */
class RoleController extends Controller
{
    /**
     * List roles.
     *
     * Returns a paginated collection of roles together with the column
     * definition used by the client tables.
     *
     * @authenticated
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: asc
     * @queryParam order_by string Column used to sort the results. Allowed values: name, description, key. Example: name
     * @queryParam search string Free text search applied to the role name. Example: admin
     * @queryParam filters string optional JSON-encoded list of filters. Each item must contain "property", "value" and optionally "operator". Example: [{"property":"name","value":"jane","operator":"="}]
     *
     * @response status=206 scenario="Paginated roles" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *       "name": "Admin",
     *       "description": "Full access role",
     *       "key": "admin",
     *       "created_at": "2026-01-15T10:00:00.000000Z",
     *       "updated_at": "2026-01-15T10:00:00.000000Z",
     *       "deleted_at": null
     *     }
     *   ],
     *   "first_page_url": "https://authxolote.test/api/v1/roles?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "https://authxolote.test/api/v1/roles?page=1",
     *   "links": [],
     *   "next_page_url": null,
     *   "path": "https://authxolote.test/api/v1/roles",
     *   "per_page": 15,
     *   "prev_page_url": null,
     *   "to": 1,
     *   "total": 1,
     *   "columns": [
     *     {
     *       "accessor": "name",
     *       "title": "Nombre",
     *       "sortable": true
     *     },
     *     {
     *       "accessor": "description",
     *       "title": "Descripción",
     *       "sortable": true
     *     },
     *     {
     *       "accessor": "key",
     *       "title": "key",
     *       "sortable": true
     *     }
     *   ]
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.role.index"
     *   }
     * }
     */
    public function index(IndexData $data, RoleIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
