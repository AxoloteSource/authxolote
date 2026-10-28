<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Actions by Role
    |--------------------------------------------------------------------------
    |
    | Every role key contains the actions that must be created and attached to
    | that role when running the `authxolote:actions` command. The action name
    | is used as the array key and its description as the value.
    |
    */

    'admin' => [
        'auth.role.attach.actions' => 'Attach actions to role',
        'auth.role.index' => 'List roles',
        'auth.role.store' => 'Create roles',
        'auth.role.show' => 'Show roles',
        'auth.role.update' => 'Update roles',
        'auth.role.delete' => 'Delete roles',
        'auth.role.actions.index' => 'List role actions',
        'auth.role.actions.update' => 'Attach or detach an action from a role',
        'auth.action.index' => 'List actions',
        'auth.action.store' => 'Create actions',
        'auth.action.show' => 'Show actions',
        'auth.action.update' => 'Update actions',
        'auth.action.delete' => 'Delete actions',
        'auth.applications.index' => 'List applications',
        'auth.applications.store' => 'Create applications',
        'auth.applications.show' => 'Show applications',
        'auth.applications.update' => 'Update applications',
        'auth.applications.destroy' => 'Delete applications',
        'auth.menus.index' => 'List menus',
        'auth.menus.store' => 'Create menus',
        'auth.menus.show' => 'Show menus',
        'auth.menus.show_all' => 'Show all menu items regardless of role',
        'auth.menus.update' => 'Update menus',
        'auth.menus.destroy' => 'Delete menus',
        'auth.menu_items.index' => 'List menu items',
        'auth.menu_items.store' => 'Create menu items',
        'auth.menu_items.show' => 'Show menu items',
        'auth.menu_items.update' => 'Update menu items',
        'auth.menu_items.roles.update' => 'Attach or detach a role from a menu item',
        'auth.menu_items.destroy' => 'Delete menu items',
        'auth.user-lists.index' => 'List user lists',
        'auth.user-lists.store' => 'Create user lists',
        'auth.user-lists.show' => 'Show user lists',
        'auth.user-lists.update' => 'Update user lists',
        'auth.user-lists.destroy' => 'Delete user lists',
        'users.index' => 'List users',
        'users.store' => 'Create users',
        'auth.flow.index' => 'List flow records',
        'auth.flow.store' => 'Create flow records',
        'auth.flow.show' => 'Show flow records',
        'auth.flow.update' => 'Update flow records',
        'auth.flow.delete' => 'Delete flow records',
        'auth.change-password' => 'Change user password',
        'auth.reset-password' => 'Reset user password',
    ],

];
