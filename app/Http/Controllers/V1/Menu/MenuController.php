<?php

namespace App\Http\Controllers\V1\Menu;

use App\Data\Menu\MenuDeleteData;
use App\Data\Menu\MenuSetupData;
use App\Data\Menu\MenuShowData;
use App\Data\Menu\MenuStoreData;
use App\Data\Menu\MenuUpdateData;
use App\Http\Controllers\Controller;
use App\Logics\Menu\MenuDeleteLogic;
use App\Logics\Menu\MenuIndexLogic;
use App\Logics\Menu\MenuSetupLogic;
use App\Logics\Menu\MenuShowAllLogic;
use App\Logics\Menu\MenuShowLogic;
use App\Logics\Menu\MenuStoreLogic;
use App\Logics\Menu\MenuUpdateLogic;
use AxoloteSource\Logics\Data\IndexData;
use Illuminate\Http\JsonResponse;

/**
 * Menu management.
 *
 * Menus belong to an application and contain the menu items shown to users.
 * Publicly readable menus are identified by their slug.
 *
 * @group Menus
 */
class MenuController extends Controller
{
    /**
     * List menus.
     *
     * Returns a paginated collection of menus with the number of items of each
     * one.
     *
     * @authenticated
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: asc
     * @queryParam order_by string Column used to sort the results. Example: name
     * @queryParam search string Free text search applied to the menu name. Example: settings
     * @queryParam filters string optional JSON-encoded list of filters. Each item must contain "property", "value" and optionally "operator". Example: [{"property":"name","value":"jane","operator":"="}]
     *
     * @response status=206 scenario="Paginated menus" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *       "name": "Settings",
     *       "slug": "settings",
     *       "application": "Backoffice",
     *       "items_count": 4,
     *       "active": true,
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "first_page_url": "https://authxolote.test/api/v1/menus?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "https://authxolote.test/api/v1/menus?page=1",
     *   "links": [],
     *   "next_page_url": null,
     *   "path": "https://authxolote.test/api/v1/menus",
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
     *       "accessor": "items_count",
     *       "title": "Items",
     *       "sortable": true
     *     }
     *   ]
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.menus.index"
     *   }
     * }
     */
    public function index(IndexData $data, MenuIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Get a menu.
     *
     * Returns a menu identified by its slug with the menu items visible for the
     * role of the authenticated user.
     *
     * @authenticated
     *
     * @urlParam slug string required Unique slug of the menu. Example: settings
     *
     * @response status=200 scenario="Menu found" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *     "application_id": "0195f51e-4a37-7087-b5f7-57d001537ab9",
     *     "application": "Backoffice",
     *     "name": "Settings",
     *     "slug": "settings",
     *     "icon": "settings",
     *     "sort_order": 0,
     *     "active": true,
     *     "items": [
     *       {
     *         "id": "0195f51e-4a37-7087-b5f7-57d001537abb",
     *         "menu_id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *         "parent_id": null,
     *         "type": "link",
     *         "name": "Users",
     *         "slug": "users",
     *         "route": "users.index",
     *         "path": "/users",
     *         "icon": "people",
     *         "sort_order": 0,
     *         "active": true,
     *         "menu": "Settings",
     *         "parent": null,
     *         "children": [],
     *         "created_at": "2026-01-15T10:00:00.000000Z",
     *         "updated_at": "2026-01-15T10:00:00.000000Z"
     *       }
     *     ],
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="Menu not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *     "data": null
     * }
     */
    public function show(MenuShowData $data, MenuShowLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Get a menu with all its items.
     *
     * Returns a menu identified by its slug with every menu item, regardless of
     * the role of the authenticated user. Each item includes a `has_role` flag
     * that indicates whether the current role can see it.
     *
     * @authenticated
     *
     * @urlParam slug string required Unique slug of the menu. Example: settings
     *
     * @response status=200 scenario="Menu found" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *     "application_id": "0195f51e-4a37-7087-b5f7-57d001537ab9",
     *     "application": "Backoffice",
     *     "name": "Settings",
     *     "slug": "settings",
     *     "icon": "settings",
     *     "sort_order": 0,
     *     "active": true,
     *     "items": [
     *       {
     *         "id": "0195f51e-4a37-7087-b5f7-57d001537abb",
     *         "menu_id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *         "parent_id": null,
     *         "type": "link",
     *         "name": "Users",
     *         "slug": "users",
     *         "route": "users.index",
     *         "path": "/users",
     *         "icon": "people",
     *         "sort_order": 0,
     *         "active": true,
     *         "has_role": true,
     *         "menu": "Settings",
     *         "parent": null,
     *         "children": [],
     *         "created_at": "2026-01-15T10:00:00.000000Z",
     *         "updated_at": "2026-01-15T10:00:00.000000Z"
     *       }
     *     ],
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="Menu not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function showAll(MenuShowData $data, MenuShowAllLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Create a menu.
     *
     * @authenticated
     *
     * @bodyParam application_id string required Identifier of the application that owns the menu. Example: 0195f51e-4a37-7087-b5f7-57d001537ab9
     * @bodyParam name string required Name of the menu. Example: Settings
     * @bodyParam slug string required Unique slug of the menu. Example: settings
     * @bodyParam icon string optional Icon identifier of the menu. Example: settings
     * @bodyParam size string optional Visual size of the menu. Allowed values: sm, md, lg. Defaults to md. Example: md
     * @bodyParam sort_order int optional Position of the menu in the application. Defaults to 0. Example: 0
     * @bodyParam active boolean optional Whether the menu is active. Defaults to true. Example: true
     *
     * @response status=201 scenario="Menu created" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *     "application_id": "0195f51e-4a37-7087-b5f7-57d001537ab9",
     *     "application": "Backoffice",
     *     "name": "Settings",
     *     "slug": "settings",
     *     "icon": "settings",
     *     "sort_order": 0,
     *     "active": true,
     *     "items": [],
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
    public function store(MenuStoreData $data, MenuStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Update a menu.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the menu. Example: 0195f51e-4a37-7087-b5f7-57d001537aba
     *
     * @bodyParam application_id string required Identifier of the application that owns the menu. Example: 0195f51e-4a37-7087-b5f7-57d001537ab9
     * @bodyParam name string required Name of the menu. Example: Settings
     * @bodyParam slug string required Unique slug of the menu. Example: settings
     * @bodyParam icon string optional Icon identifier of the menu. Example: settings
     * @bodyParam size string optional Visual size of the menu. Allowed values: sm, md, lg. Defaults to md. Example: md
     * @bodyParam sort_order int optional Position of the menu in the application. Example: 0
     * @bodyParam active boolean optional Whether the menu is active. Example: true
     *
     * @response status=200 scenario="Menu updated" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *     "application_id": "0195f51e-4a37-7087-b5f7-57d001537ab9",
     *     "application": "Backoffice",
     *     "name": "Settings",
     *     "slug": "settings",
     *     "icon": "settings",
     *     "sort_order": 0,
     *     "active": true,
     *     "items": [],
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:05:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="Menu not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function update(MenuUpdateData $data, MenuUpdateLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Delete a menu.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the menu. Example: 0195f51e-4a37-7087-b5f7-57d001537aba
     *
     * @response status=204 scenario="Menu deleted"
     * @response status=404 scenario="Menu not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function destroy(MenuDeleteData $data, MenuDeleteLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Set up a menu from a definition.
     *
     * Creates the application if it does not exist yet and creates (or reuses)
     * the menu and its menu items from the given tree. Existing menu items are
     * reused by slug; the roles of each item are synced when provided.
     *
     * When `force` is true, the existing menu and all of its items are deleted
     * and recreated from scratch. Roles, item order and any manual change are
     * lost, and items without roles inherit the roles of their parent.
     *
     * @authenticated
     *
     * @bodyParam name string required Name of the menu. The slug is generated from this value. Example: Settings
     * @bodyParam size string optional Visual size of the menu. Allowed values: sm, md, lg. Defaults to md. Example: md
     * @bodyParam force boolean optional Delete and recreate the existing menu. Defaults to false. Example: false
     * @bodyParam application object required Application that owns the menu.
     * @bodyParam application.name string required Name of the application. Example: Backoffice
     * @bodyParam application.slug string required Unique slug of the application. Example: backoffice
     * @bodyParam application.description string optional Description of the application. Example: Internal administration application
     * @bodyParam menu_items object required Menu tree keyed by item slug. Each value accepts `name`, `route`, `path`, `icon`, `roles` and a nested `menu_items` object to create children.
     *
     * @response status=201 scenario="Menu set up" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *     "application_id": "0195f51e-4a37-7087-b5f7-57d001537ab9",
     *     "application": "Backoffice",
     *     "name": "Settings",
     *     "slug": "settings",
     *     "icon": null,
     *     "sort_order": 0,
     *     "active": true,
     *     "items": [],
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
    public function setup(MenuSetupData $data, MenuSetupLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
