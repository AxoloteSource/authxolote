<?php

namespace App\Http\Controllers\V1\Application;

use App\Data\Application\ApplicationDeleteData;
use App\Data\Application\ApplicationShowData;
use App\Data\Application\ApplicationStoreData;
use App\Data\Application\ApplicationUpdateData;
use App\Http\Controllers\Controller;
use App\Logics\Application\ApplicationDeleteLogic;
use App\Logics\Application\ApplicationIndexLogic;
use App\Logics\Application\ApplicationShowLogic;
use App\Logics\Application\ApplicationStoreLogic;
use App\Logics\Application\ApplicationUpdateLogic;
use AxoloteSource\Logics\Data\IndexData;
use Illuminate\Http\JsonResponse;

/**
 * Application management.
 *
 * Applications group menus. Each application is identified by a unique slug
 * when it is retrieved.
 *
 * @group Applications
 */
class ApplicationController extends Controller
{
    /**
     * List applications.
     *
     * Returns a paginated collection of applications with the number of menus
     * associated with each one.
     *
     * @authenticated
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: asc
     * @queryParam order_by string Column used to sort the results. Example: name
     * @queryParam search string Free text search applied to the application name. Example: backoffice
     * @queryParam filters string optional JSON-encoded list of filters. Each item must contain "property", "value" and optionally "operator". Example: [{"property":"name","value":"jane","operator":"="}]
     *
     * @response status=206 scenario="Paginated applications" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537ab9",
     *       "name": "Backoffice",
     *       "slug": "backoffice",
     *       "description": "Internal administration application",
     *       "menus_count": 3,
     *       "active": true,
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "first_page_url": "https://authxolote.test/api/v1/applications?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "https://authxolote.test/api/v1/applications?page=1",
     *   "links": [],
     *   "next_page_url": null,
     *   "path": "https://authxolote.test/api/v1/applications",
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
     *       "accessor": "slug",
     *       "title": "Slug",
     *       "sortable": true
     *     }
     *   ]
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.applications.index"
     *   }
     * }
     */
    public function index(IndexData $data, ApplicationIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Get an application.
     *
     * Returns a single application, identified by its slug, with the menus that
     * belong to it.
     *
     * @authenticated
     *
     * @urlParam slug string required Unique slug of the application. Example: backoffice
     *
     * @response status=200 scenario="Application found" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537ab9",
     *     "name": "Backoffice",
     *     "slug": "backoffice",
     *     "description": "Internal administration application",
     *     "active": true,
     *     "menus": [
     *       {
     *         "id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *         "name": "Settings",
     *         "slug": "settings"
     *       }
     *     ],
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="Application not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function show(ApplicationShowData $data, ApplicationShowLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Create an application.
     *
     * @authenticated
     *
     * @bodyParam name string required Name of the application. Example: Backoffice
     * @bodyParam slug string required Unique slug of the application. Example: backoffice
     * @bodyParam description string optional Description of the application. Example: Internal administration application
     * @bodyParam active boolean optional Whether the application is active. Defaults to true. Example: true
     *
     * @response status=201 scenario="Application created" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537ab9",
     *     "name": "Backoffice",
     *     "slug": "backoffice",
     *     "description": "Internal administration application",
     *     "active": true,
     *     "menus": [],
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response status=422 scenario="Validation error" {
     *   "status": "error",
     *   "message": null,
     *   "data": {
     *     "slug": [
     *       "The slug field is required."
     *     ]
     *   }
     * }
     */
    public function store(ApplicationStoreData $data, ApplicationStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Update an application.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the application. Example: 0195f51e-4a37-7087-b5f7-57d001537ab9
     *
     * @bodyParam name string required Name of the application. Example: Backoffice
     * @bodyParam slug string required Unique slug of the application. Example: backoffice
     * @bodyParam description string optional Description of the application. Example: Internal administration application
     * @bodyParam active boolean optional Whether the application is active. Example: true
     *
     * @response status=200 scenario="Application updated" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537ab9",
     *     "name": "Backoffice",
     *     "slug": "backoffice",
     *     "description": "Internal administration application",
     *     "active": true,
     *     "menus": [],
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:05:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="Application not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function update(ApplicationUpdateData $data, ApplicationUpdateLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Delete an application.
     *
     * Soft deletes the application. It is not removed from the database.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the application. Example: 0195f51e-4a37-7087-b5f7-57d001537ab9
     *
     * @response status=204 scenario="Application deleted"
     * @response status=404 scenario="Application not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function destroy(ApplicationDeleteData $data, ApplicationDeleteLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
