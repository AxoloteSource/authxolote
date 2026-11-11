<?php

namespace App\Http\Controllers\V1\UserList;

use App\Data\UserList\UserListUsersData;
use App\Http\Controllers\Controller;
use App\Logics\UserList\UserListUsersLogic;
use Illuminate\Http\JsonResponse;

/**
 * Users contained in a user list.
 *
 * @group User Lists
 */
class UserListUserController extends Controller
{
    /**
     * List the users of a user list.
     *
     * Returns a paginated collection of users that belong to the given list. If
     * the list is flagged to include all users, every user is returned.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the user list. Example: 0195f51e-4a37-7087-b5f7-57d001537abc
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: asc
     * @queryParam order_by string Column used to sort the results. Example: name
     * @queryParam search string Free text search applied to the user name. Example: jane
     * @queryParam filters string optional JSON-encoded list of filters. Each item must contain "property", "value" and optionally "operator". Example: [{"property":"name","value":"jane","operator":"="}]
     *
     * @response status=206 scenario="Paginated users" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *       "name": "Jane Doe",
     *       "email": "jane.doe@example.com"
     *     }
     *   ],
     *   "first_page_url": "https://authxolote.test/api/v1/user-lists/0195f51e-4a37-7087-b5f7-57d001537abc/users?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "https://authxolote.test/api/v1/user-lists/0195f51e-4a37-7087-b5f7-57d001537abc/users?page=1",
     *   "links": [],
     *   "next_page_url": null,
     *   "path": "https://authxolote.test/api/v1/user-lists/0195f51e-4a37-7087-b5f7-57d001537abc/users",
     *   "per_page": 15,
     *   "prev_page_url": null,
     *   "to": 1,
     *   "total": 1,
     *   "columns": [
     *     {
     *       "accessor": "id",
     *       "title": "ID",
     *       "sortable": true
     *     },
     *     {
     *       "accessor": "name",
     *       "title": "Name",
     *       "sortable": true
     *     },
     *     {
     *       "accessor": "email",
     *       "title": "Email",
     *       "sortable": true
     *     }
     *   ]
     * }
     * @response status=404 scenario="User list not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function index(UserListUsersData $data, UserListUsersLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
