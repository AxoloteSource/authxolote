<?php

namespace App\Http\Controllers\V1\User;

use App\Data\User\UserIndexData;
use App\Data\User\UserStoreData;
use App\Http\Controllers\Controller;
use App\Logics\User\UserIndexLogic;
use App\Logics\User\UserStoreLogic;
use Illuminate\Http\JsonResponse;

/**
 * User management.
 *
 * @group Users
 */
class UserController extends Controller
{
    /**
     * List users.
     *
     * Returns a paginated collection of users together with the column
     * definition used by the client tables.
     *
     * @authenticated
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: asc
     * @queryParam order_by string Column used to sort the results. Example: name
     * @queryParam search string Free text search applied to the user email. Example: jane
     * @queryParam filters string optional JSON-encoded list of filters. Each item must contain "property", "value" and optionally "operator". Example: [{"property":"name","value":"jane","operator":"="}]
     *
     * @response status=206 scenario="Paginated users" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *       "name": "Jane Doe",
     *       "email": "jane.doe@example.com",
     *       "role_id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *       "role": {
     *         "id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *         "name": "Admin"
     *       },
     *       "email_verified_at": null,
     *       "created_at": "2026-01-15T10:00:00.000000Z",
     *       "updated_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "first_page_url": "https://authxolote.test/api/v1/users?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "https://authxolote.test/api/v1/users?page=1",
     *   "links": [],
     *   "next_page_url": null,
     *   "path": "https://authxolote.test/api/v1/users",
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
     *       "accessor": "email",
     *       "title": "Email",
     *       "sortable": true
     *     }
     *   ]
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "users.index"
     *   }
     * }
     */
    public function index(UserIndexData $data, UserIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Create a user.
     *
     * Creates a user with the given role and returns its profile.
     *
     * @authenticated
     *
     * @bodyParam name string required Full name of the user. Example: Jane Doe
     * @bodyParam email string required Unique email address of the user. Example: jane.doe@example.com
     * @bodyParam role_id string required Identifier of the role assigned to the user. Example: 0195f51e-4a37-7087-b5f7-57d001537ab7
     * @bodyParam password string required Password of the user. Minimum length is 8 characters. Example: Password123!
     *
     * @response status=201 scenario="User created" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *     "name": "Jane Doe",
     *     "email": "jane.doe@example.com",
     *     "role_id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *     "role": {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *       "name": "Admin"
     *     },
     *     "email_verified_at": null,
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "email": [
     *       "The email has already been taken."
     *     ]
     *   }
     * }
     */
    public function store(UserStoreData $data, UserStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
