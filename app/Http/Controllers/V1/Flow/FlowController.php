<?php

namespace App\Http\Controllers\V1\Flow;

use App\Http\Controllers\Controller;
use App\Logics\Flow\FlowDeleteLogic;
use App\Logics\Flow\FlowIndexLogic;
use App\Logics\Flow\FlowShowLogic;
use App\Logics\Flow\FlowStoreLogic;
use App\Logics\Flow\FlowUpdateLogic;
use AxoloteSource\Logics\Data\Flow\FlowByIdData;
use AxoloteSource\Logics\Data\Flow\FlowIndexData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Generic CRUD flow.
 *
 * A single set of routes proxies CRUD operations to the model indicated in the
 * `{model}` segment. Supported models are `roles` and `actions`; listing is
 * supported for `actions` only. Every operation still requires the action
 * permission configured for the resolved model.
 *
 * @group Flow
 */
class FlowController extends Controller
{
    /**
     * List records of a model.
     *
     * Returns a paginated collection of the requested model. Currently only the
     * `actions` model supports this operation.
     *
     * @authenticated
     *
     * @urlParam model string required Model to operate on. Allowed value: actions. Example: actions
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: asc
     * @queryParam order_by string Column used to sort the results. Example: name
     * @queryParam search string Free text search. Example: users
     * @queryParam filters string optional JSON-encoded list of filters. Each item must contain "property", "value" and optionally "operator". Example: [{"property":"name","value":"jane","operator":"="}]
     *
     * @response status=206 scenario="Paginated records" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab8",
     *       "name": "auth.users.index",
     *       "description": "List users",
     *       "created_at": "2026-01-15T10:00:00.000000Z",
     *       "updated_at": "2026-01-15T10:00:00.000000Z",
     *       "deleted_at": null
     *     }
     *   ],
     *   "first_page_url": "https://authxolote.test/api/v1/actions?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "https://authxolote.test/api/v1/actions?page=1",
     *   "links": [],
     *   "next_page_url": null,
     *   "path": "https://authxolote.test/api/v1/actions",
     *   "per_page": 15,
     *   "prev_page_url": null,
     *   "to": 1,
     *   "total": 1,
     *   "columns": []
     * }
     * @response status=404 scenario="Unsupported model" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function index(FlowIndexData $data, FlowIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Create a record of a model.
     *
     * The body depends on the model: `roles` expects `name`, `key` and
     * `description`; `actions` expects `name` and `description`.
     *
     * @authenticated
     *
     * @urlParam model string required Model to operate on. Allowed values: roles, actions. Example: roles
     *
     * @bodyParam name string required Name of the record. Example: Admin
     * @bodyParam key string optional Unique key of the record. Required when the model is `roles`. Example: admin
     * @bodyParam description string required Description of the record. Example: Full access role
     *
     * @response status=201 scenario="Record created" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *     "name": "Admin",
     *     "description": "Full access role",
     *     "key": "admin",
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
    public function store(Request $data, FlowStoreLogic $logic): JsonResponse
    {
        return $logic->run(array_merge(
            $data->all(),
            ['model' => $data->route('model'), 'id' => $data->route('id')]
        ));
    }

    /**
     * Get a record of a model.
     *
     * @authenticated
     *
     * @urlParam model string required Model to operate on. Allowed values: roles, actions. Example: roles
     * @urlParam id string required Identifier of the record. Example: 0195f51e-4a37-7087-b5f7-57d001537ab7
     *
     * @response status=200 scenario="Record found" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *     "name": "Admin",
     *     "description": "Full access role",
     *     "key": "admin",
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z",
     *     "deleted_at": null
     *   }
     * }
     * @response status=404 scenario="Record not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function show(FlowByIdData $data, FlowShowLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Update a record of a model.
     *
     * The body depends on the model: `roles` expects `name` and `description`;
     * `actions` expects `name`, `description` and `id`.
     *
     * @authenticated
     *
     * @urlParam model string required Model to operate on. Allowed values: roles, actions. Example: roles
     * @urlParam id string required Identifier of the record. Example: 0195f51e-4a37-7087-b5f7-57d001537ab7
     *
     * @bodyParam name string required Name of the record. Example: Admin
     * @bodyParam description string required Description of the record. Example: Full access role
     *
     * @response status=200 scenario="Record updated" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537ab7",
     *     "name": "Admin",
     *     "description": "Full access role",
     *     "key": "admin",
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:05:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="Record not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function update(Request $data, FlowUpdateLogic $logic): JsonResponse
    {
        return $logic->run(array_merge(
            $data->all(),
            ['model' => $data->route('model'), 'id' => $data->route('id')]
        ));
    }

    /**
     * Delete a record of a model.
     *
     * @authenticated
     *
     * @urlParam model string required Model to operate on. Allowed values: roles, actions. Example: roles
     * @urlParam id string required Identifier of the record. Example: 0195f51e-4a37-7087-b5f7-57d001537ab7
     *
     * @response status=204 scenario="Record deleted"
     * @response status=404 scenario="Record not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function delete(FlowByIdData $data, FlowDeleteLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
