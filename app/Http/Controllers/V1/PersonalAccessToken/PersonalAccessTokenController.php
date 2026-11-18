<?php

namespace App\Http\Controllers\V1\PersonalAccessToken;

use App\Data\PersonalAccessToken\PersonalAccessTokenDeleteData;
use App\Data\PersonalAccessToken\PersonalAccessTokenIndexData;
use App\Data\PersonalAccessToken\PersonalAccessTokenRevokeData;
use App\Data\PersonalAccessToken\PersonalAccessTokenStoreData;
use App\Http\Controllers\Controller;
use App\Logics\PersonalAccessToken\PersonalAccessTokenDeleteLogic;
use App\Logics\PersonalAccessToken\PersonalAccessTokenIndexLogic;
use App\Logics\PersonalAccessToken\PersonalAccessTokenRevokeLogic;
use App\Logics\PersonalAccessToken\PersonalAccessTokenStoreLogic;
use Illuminate\Http\JsonResponse;

/**
 * Personal access token management.
 *
 * Tokens are issued by Laravel Passport and can be used as
 * `Authorization: Bearer {token}` against the API. The plain-text token is
 * only returned when it is created.
 *
 * @group Personal Access Tokens
 */
class PersonalAccessTokenController extends Controller
{
    /**
     * List personal access tokens.
     *
     * Returns every personal access token in the system. Use `user_id` to
     * filter by a specific user. Plain-text tokens are never returned.
     *
     * @authenticated
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: desc
     * @queryParam order_by string Column used to sort the results. Example: created_at
     * @queryParam search string Free text search applied to the token name. Example: deploy
     * @queryParam user_id string optional Identifier of the user whose tokens will be listed. Example: 0195f51e-4a37-7087-b5f7-57d001537ab6
     *
     * @response status=206 scenario="Paginated personal access tokens" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "a1b2c3d4e5f6",
     *       "name": "deploy",
     *       "expires_at": "2026-12-11T10:00:00.000000Z",
     *       "revoked": false,
     *       "created_at": "2026-11-11T10:00:00.000000Z",
     *       "user": {
     *         "id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *         "name": "Jane Doe",
     *         "email": "jane.doe@example.com",
     *         "user_type_id": "a333c865-bdcb-4658-b1f2-e2e8a78a192a"
     *       }
     *     }
     *   ],
     *   "columns": []
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.personal-access-tokens.index"
     *   }
     * }
     */
    public function index(PersonalAccessTokenIndexData $data, PersonalAccessTokenIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Create a personal access token.
     *
     * Creates a token for the given user. The plain-text token is returned only
     * in this response and cannot be retrieved again.
     *
     * @authenticated
     *
     * @bodyParam user_id string required Identifier of the user that will own the token. Must be the caller's own id or belong to an app-type user. Example: 0195f51e-4a37-7087-b5f7-57d001537ab6
     * @bodyParam name string required Name used to identify the token. Example: deploy
     * @bodyParam expires_in int optional Token lifetime in days. Allowed values: 7, 30, 60, 90. Omit or send null for a token that does not expire. Example: 30
     *
     * @response status=201 scenario="Personal access token created" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "a1b2c3d4e5f6",
     *     "name": "deploy",
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *     "token_type": "Bearer",
     *     "expires_at": "2026-12-11T10:00:00.000000Z",
     *     "revoked": false,
     *     "created_at": "2026-11-11T10:00:00.000000Z",
     *     "user": {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *       "name": "Jane Doe",
     *       "email": "jane.doe@example.com",
     *       "user_type_id": "a333c865-bdcb-4658-b1f2-e2e8a78a192a"
     *     }
     *   }
     * }
     * @response status=403 scenario="Token for another user forbidden" {
     *   "status": "error",
     *   "message": "You may not create a personal access token for another user.",
     *   "data": null
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "user_id": [
     *       "The user id field is required."
     *     ]
     *   }
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.personal-access-tokens.store"
     *   }
     * }
     */
    public function store(PersonalAccessTokenStoreData $data, PersonalAccessTokenStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Revoke a personal access token.
     *
     * Marks any personal access token as revoked. The token record is kept.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the personal access token. Example: a1b2c3d4e5f6
     *
     * @response status=200 scenario="Personal access token revoked" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "a1b2c3d4e5f6",
     *     "name": "deploy",
     *     "expires_at": "2026-12-11T10:00:00.000000Z",
     *     "revoked": true,
     *     "created_at": "2026-11-11T10:00:00.000000Z",
     *     "user": {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab6",
     *       "name": "Jane Doe",
     *       "email": "jane.doe@example.com",
     *       "user_type_id": "a333c865-bdcb-4658-b1f2-e2e8a78a192a"
     *     }
     *   }
     * }
     * @response status=404 scenario="Token not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.personal-access-tokens.revoke"
     *   }
     * }
     */
    public function revoke(PersonalAccessTokenRevokeData $data, PersonalAccessTokenRevokeLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Delete a personal access token.
     *
     * Permanently deletes any personal access token.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the personal access token. Example: a1b2c3d4e5f6
     *
     * @response status=204 scenario="Personal access token deleted"
     * @response status=404 scenario="Token not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.personal-access-tokens.destroy"
     *   }
     * }
     */
    public function destroy(PersonalAccessTokenDeleteData $data, PersonalAccessTokenDeleteLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
