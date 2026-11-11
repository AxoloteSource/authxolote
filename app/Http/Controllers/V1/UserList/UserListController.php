<?php

namespace App\Http\Controllers\V1\UserList;

use App\Data\UserList\UserListDeleteData;
use App\Data\UserList\UserListShowData;
use App\Data\UserList\UserListStoreData;
use App\Data\UserList\UserListUpdateData;
use App\Http\Controllers\Controller;
use App\Logics\UserList\UserListDeleteLogic;
use App\Logics\UserList\UserListIndexLogic;
use App\Logics\UserList\UserListShowLogic;
use App\Logics\UserList\UserListStoreLogic;
use App\Logics\UserList\UserListUpdateLogic;
use AxoloteSource\Logics\Data\IndexData;
use Illuminate\Http\JsonResponse;

/**
 * User list management.
 *
 * A user list groups users under a name and slug so they can be reused, for
 * example as a distribution group.
 *
 * @group User Lists
 */
class UserListController extends Controller
{
    /**
     * List user lists.
     *
     * Returns a paginated collection of user lists with the number of users of
     * each one.
     *
     * @authenticated
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: asc
     * @queryParam order_by string Column used to sort the results. Example: name
     * @queryParam search string Free text search applied to the user list name. Example: support
     * @queryParam filters string optional JSON-encoded list of filters. Each item must contain "property", "value" and optionally "operator". Example: [{"property":"name","value":"jane","operator":"="}]
     *
     * @response status=206 scenario="Paginated user lists" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537abc",
     *       "name": "Support",
     *       "slug": "support",
     *       "description": "Support team",
     *       "users_count": 4,
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "first_page_url": "https://authxolote.test/api/v1/user-lists?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "https://authxolote.test/api/v1/user-lists?page=1",
     *   "links": [],
     *   "next_page_url": null,
     *   "path": "https://authxolote.test/api/v1/user-lists",
     *   "per_page": 15,
     *   "prev_page_url": null,
     *   "to": 1,
     *   "total": 1,
     *   "columns": [
     *     {
     *       "accessor": "name",
     *       "title": "Name",
     *       "sortable": true
     *     },
     *     {
     *       "accessor": "users_count",
     *       "title": "Users",
     *       "sortable": true
     *     }
     *   ]
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.user-lists.index"
     *   }
     * }
     */
    public function index(IndexData $data, UserListIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Get a user list.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the user list. Example: 0195f51e-4a37-7087-b5f7-57d001537abc
     *
     * @response status=200 scenario="User list found" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537abc",
     *     "name": "Support",
     *     "slug": "support",
     *     "description": "Support team",
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="User list not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function show(UserListShowData $data, UserListShowLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Create a user list.
     *
     * @authenticated
     *
     * @bodyParam name string required Name of the user list. Example: Support
     * @bodyParam slug string optional Unique slug of the user list. Example: support
     * @bodyParam description string optional Description of the user list. Example: Support team
     * @bodyParam user_ids string[] optional Identifiers of the users included in the list. Example: ["0195f51e-4a37-7087-b5f7-57d001537ab6"]
     *
     * @response status=201 scenario="User list created" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537abc",
     *     "name": "Support",
     *     "slug": "support",
     *     "description": "Support team",
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "name": [
     *       "The name field is required."
     *     ]
     *   }
     * }
     */
    public function store(UserListStoreData $data, UserListStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Update a user list.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the user list. Example: 0195f51e-4a37-7087-b5f7-57d001537abc
     *
     * @bodyParam name string required Name of the user list. Example: Support
     * @bodyParam slug string optional Unique slug of the user list. Example: support
     * @bodyParam description string optional Description of the user list. Example: Support team
     * @bodyParam user_ids string[] optional Identifiers of the users included in the list. Example: ["0195f51e-4a37-7087-b5f7-57d001537ab6"]
     *
     * @response status=200 scenario="User list updated" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537abc",
     *     "name": "Support",
     *     "slug": "support",
     *     "description": "Support team",
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:05:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="User list not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function update(UserListUpdateData $data, UserListUpdateLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Delete a user list.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the user list. Example: 0195f51e-4a37-7087-b5f7-57d001537abc
     *
     * @response status=204 scenario="User list deleted"
     * @response status=404 scenario="User list not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function destroy(UserListDeleteData $data, UserListDeleteLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
