<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Actions by Role
    |--------------------------------------------------------------------------
    |
    | Every role key contains the actions that must be created and attached to
    | that role when running the `authxolote:actions` command. The action name
    | is used as the array key and its translation key as the value. The
    | translations are resolved by the command at runtime.
    |
    */

    'admin' => [
        'auth.role.attach.actions' => 'actions.role_attach_actions',
        'auth.role.index' => 'actions.role_index',
        'auth.role.store' => 'actions.role_store',
        'auth.role.show' => 'actions.role_show',
        'auth.role.update' => 'actions.role_update',
        'auth.role.delete' => 'actions.role_delete',
        'auth.role.actions.index' => 'actions.role_actions_index',
        'auth.role.actions.update' => 'actions.role_actions_update',
        'auth.action.index' => 'actions.action_index',
        'auth.action.store' => 'actions.action_store',
        'auth.action.show' => 'actions.action_show',
        'auth.action.update' => 'actions.action_update',
        'auth.action.delete' => 'actions.action_delete',
        'auth.applications.index' => 'actions.applications_index',
        'auth.applications.store' => 'actions.applications_store',
        'auth.applications.show' => 'actions.applications_show',
        'auth.applications.update' => 'actions.applications_update',
        'auth.applications.destroy' => 'actions.applications_destroy',
        'auth.menus.index' => 'actions.menus_index',
        'auth.menus.store' => 'actions.menus_store',
        'auth.menus.show' => 'actions.menus_show',
        'auth.menus.show_all' => 'actions.menus_show_all',
        'auth.menus.update' => 'actions.menus_update',
        'auth.menus.destroy' => 'actions.menus_destroy',
        'auth.menu_items.index' => 'actions.menu_items_index',
        'auth.menu_items.store' => 'actions.menu_items_store',
        'auth.menu_items.show' => 'actions.menu_items_show',
        'auth.menu_items.update' => 'actions.menu_items_update',
        'auth.menu_items.reorder' => 'actions.menu_items_reorder',
        'auth.menu_items.roles.update' => 'actions.menu_items_roles_update',
        'auth.menu_items.destroy' => 'actions.menu_items_destroy',
        'auth.user-lists.index' => 'actions.user_lists_index',
        'auth.user-lists.store' => 'actions.user_lists_store',
        'auth.user-lists.show' => 'actions.user_lists_show',
        'auth.user-lists.update' => 'actions.user_lists_update',
        'auth.user-lists.destroy' => 'actions.user_lists_destroy',
        'users.index' => 'actions.users_index',
        'users.store' => 'actions.users_store',
        'auth.flow.index' => 'actions.flow_index',
        'auth.flow.store' => 'actions.flow_store',
        'auth.flow.show' => 'actions.flow_show',
        'auth.flow.update' => 'actions.flow_update',
        'auth.flow.delete' => 'actions.flow_delete',
        'auth.change-password' => 'actions.change_password',
        'auth.reset-password' => 'actions.reset_password',
        'auth.validate-credentials' => 'actions.validate_credentials',
    ],

];
