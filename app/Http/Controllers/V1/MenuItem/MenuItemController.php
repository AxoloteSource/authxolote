<?php

namespace App\Http\Controllers\V1\MenuItem;

use App\Data\MenuItem\MenuItemDeleteData;
use App\Data\MenuItem\MenuItemReorderData;
use App\Data\MenuItem\MenuItemShowData;
use App\Data\MenuItem\MenuItemStoreData;
use App\Data\MenuItem\MenuItemUpdateData;
use App\Data\MenuItem\UpdateMenuItemRoleData;
use App\Http\Controllers\Controller;
use App\Logics\MenuItem\MenuItemDeleteLogic;
use App\Logics\MenuItem\MenuItemIndexLogic;
use App\Logics\MenuItem\MenuItemReorderLogic;
use App\Logics\MenuItem\MenuItemRoleUpdateLogic;
use App\Logics\MenuItem\MenuItemShowLogic;
use App\Logics\MenuItem\MenuItemStoreLogic;
use App\Logics\MenuItem\MenuItemUpdateLogic;
use AxoloteSource\Logics\Data\IndexData;
use Illuminate\Http\JsonResponse;

/**
 * Menu item management.
 *
 * Menu items can be links or headers and may be nested through `parent_id`.
 *
 * @group Menu Items
 */
class MenuItemController extends Controller
{
    /**
     * List menu items.
     *
     * Returns a paginated collection of root menu items. Children are nested
     * inside each item.
     *
     * @authenticated
     *
     * @queryParam page int Page to retrieve. Example: 1
     * @queryParam limit int Number of records per page. Example: 15
     * @queryParam order string Sort direction. Allowed values: asc, desc. Example: asc
     * @queryParam order_by string Column used to sort the results. Example: sort_order
     * @queryParam search string Free text search applied to the item name. Example: users
     * @queryParam filters string optional JSON-encoded list of filters. Each item must contain "property", "value" and optionally "operator". Example: [{"property":"name","value":"jane","operator":"="}]
     *
     * @response status=206 scenario="Paginated menu items" {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0195f51e-4a37-7087-b5f7-57d001537abb",
     *       "type": "link",
     *       "name": "Users",
     *       "slug": "users",
     *       "route": "users.index",
     *       "path": "/users",
     *       "icon": "people",
     *       "active": true,
     *       "menu": "Settings",
     *       "items_count": 0,
     *       "children": [],
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "first_page_url": "https://authxolote.test/api/v1/menu-items?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "https://authxolote.test/api/v1/menu-items?page=1",
     *   "links": [],
     *   "next_page_url": null,
     *   "path": "https://authxolote.test/api/v1/menu-items",
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
     *       "accessor": "type",
     *       "title": "Type",
     *       "sortable": true
     *     }
     *   ]
     * }
     * @response status=403 scenario="Missing permission" {
     *   "message": "You do not have permission to access this resource",
     *   "data": {
     *     "action": "auth.menu_items.index"
     *   }
     * }
     */
    public function index(IndexData $data, MenuItemIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Get a menu item.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the menu item. Example: 0195f51e-4a37-7087-b5f7-57d001537abb
     *
     * @response status=200 scenario="Menu item found" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537abb",
     *     "menu_id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *     "parent_id": null,
     *     "type": "link",
     *     "name": "Users",
     *     "slug": "users",
     *     "route": "users.index",
     *     "path": "/users",
     *     "icon": "people",
     *     "sort_order": 0,
     *     "active": true,
     *     "menu": "Settings",
     *     "parent": null,
     *     "children": [],
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="Menu item not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function show(MenuItemShowData $data, MenuItemShowLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Create a menu item.
     *
     * @authenticated
     *
     * @bodyParam menu_id string required Identifier of the menu that owns the item. Example: 0195f51e-4a37-7087-b5f7-57d001537aba
     * @bodyParam parent_id string optional Identifier of the parent menu item. Example: null
     * @bodyParam type string required Type of the item. Allowed values: header, link, section. Example: link
     * @bodyParam name string required Display name of the item. Example: Users
     * @bodyParam slug string required Unique slug of the item. Example: users
     * @bodyParam route string optional Frontend route name. Example: users.index
     * @bodyParam path string optional Frontend path. Example: /users
     * @bodyParam icon string optional Icon identifier. Example: people
     * @bodyParam sort_order int optional Position of the item. Defaults to 0. Example: 0
     * @bodyParam active boolean optional Whether the item is active. Defaults to true. Example: true
     *
     * @response status=201 scenario="Menu item created" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537abb",
     *     "menu_id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *     "parent_id": null,
     *     "type": "link",
     *     "name": "Users",
     *     "slug": "users",
     *     "route": "users.index",
     *     "path": "/users",
     *     "icon": "people",
     *     "sort_order": 0,
     *     "active": true,
     *     "menu": "Settings",
     *     "parent": null,
     *     "children": [],
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
    public function store(MenuItemStoreData $data, MenuItemStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Update a menu item.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the menu item. Example: 0195f51e-4a37-7087-b5f7-57d001537abb
     *
     * @bodyParam menu_id string required Identifier of the menu that owns the item. Example: 0195f51e-4a37-7087-b5f7-57d001537aba
     * @bodyParam parent_id string optional Identifier of the parent menu item. Example: null
     * @bodyParam type string required Type of the item. Allowed values: header, link, section. Example: link
     * @bodyParam name string required Display name of the item. Example: Users
     * @bodyParam slug string required Unique slug of the item. Example: users
     * @bodyParam route string optional Frontend route name. Example: users.index
     * @bodyParam path string optional Frontend path. Example: /users
     * @bodyParam icon string optional Icon identifier. Example: people
     * @bodyParam sort_order int optional Position of the item. Example: 0
     * @bodyParam active boolean optional Whether the item is active. Example: true
     *
     * @response status=200 scenario="Menu item updated" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537abb",
     *     "menu_id": "0195f51e-4a37-7087-b5f7-57d001537aba",
     *     "parent_id": null,
     *     "type": "link",
     *     "name": "Users",
     *     "slug": "users",
     *     "route": "users.index",
     *     "path": "/users",
     *     "icon": "people",
     *     "sort_order": 0,
     *     "active": true,
     *     "menu": "Settings",
     *     "parent": null,
     *     "children": [],
     *     "created_at": "2026-01-15T10:00:00.000000Z",
     *     "updated_at": "2026-01-15T10:05:00.000000Z"
     *   }
     * }
     * @response status=404 scenario="Menu item not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function update(MenuItemUpdateData $data, MenuItemUpdateLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Reorder the menu items of a menu.
     *
     * The payload must include every item of the menu. Each item defines its
     * new parent (or `null` for a root item) and its position among its
     * siblings. Positions are normalized to consecutive integers.
     *
     * @authenticated
     *
     * @bodyParam menu_id string required Identifier of the menu. Example: 0195f51e-4a37-7087-b5f7-57d001537aba
     * @bodyParam items object[] required Flat list with every menu item and its new position.
     * @bodyParam items[].id string required Identifier of the menu item. Example: 0195f51e-4a37-7087-b5f7-57d001537abb
     * @bodyParam items[].parent_id string optional Identifier of the new parent, or null for a root item. Example: null
     * @bodyParam items[].sort_order int required Position among its siblings. Example: 0
     *
     * @response status=200 scenario="Menu reordered" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "reordered": 3
     *   }
     * }
     * @response status=422 scenario="Invalid hierarchy" {
     *   "status": "error",
     *   "message": "The menu item hierarchy contains a cycle.",
     *   "data": null
     * }
     */
    public function reorder(MenuItemReorderData $data, MenuItemReorderLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Toggle a menu item for a role.
     *
     * Attaches the menu item to the role when `active` is true and detaches it
     * when false.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the menu item. Example: 0195f51e-4a37-7087-b5f7-57d001537abb
     * @urlParam roleId string required Identifier of the role. Example: 0195f51e-4a37-7087-b5f7-57d001537ab7
     *
     * @bodyParam active boolean required Whether the menu item must be visible for the role. Example: true
     *
     * @response status=200 scenario="Visibility updated" {
     *   "status": "OK",
     *   "message": null,
     *   "data": {
     *     "id": "0195f51e-4a37-7087-b5f7-57d001537abb",
     *     "name": "Users",
     *     "slug": "users",
     *     "active": true
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
    public function updateRole(UpdateMenuItemRoleData $data, MenuItemRoleUpdateLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    /**
     * Delete a menu item.
     *
     * @authenticated
     *
     * @urlParam id string required Identifier of the menu item. Example: 0195f51e-4a37-7087-b5f7-57d001537abb
     *
     * @response status=204 scenario="Menu item deleted"
     * @response status=404 scenario="Menu item not found" {
     *   "status": "error",
     *   "message": "Not Found",
     *   "data": null
     * }
     */
    public function destroy(MenuItemDeleteData $data, MenuItemDeleteLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
