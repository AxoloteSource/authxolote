<?php

namespace App\Http\Controllers\V1\UserType;

use App\Http\Controllers\Controller;
use App\Logics\UserType\UserTypeIndexLogic;
use AxoloteSource\Logics\Data\IndexData;
use Illuminate\Http\JsonResponse;

/**
 * User type catalog.
 *
 * @group User Types
 */
class UserTypeController extends Controller
{
    /**
     * List user types.
     *
     * Returns the available user types. These identifiers are used when
     * creating or filtering users.
     *
     * @authenticated
     *
     * @response status=200 scenario="User types" {
     *   "status": "OK",
     *   "message": null,
     *   "data": [
     *     {
     *       "id": "a333c865-c1d7-4a9c-875f-b619b5534df8",
     *       "name": "App",
     *       "key": "app"
     *     },
     *     {
     *       "id": "a333c865-bdcb-4658-b1f2-e2e8a78a192a",
     *       "name": "Human",
     *       "key": "human"
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
    public function index(IndexData $data, UserTypeIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
