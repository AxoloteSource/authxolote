<?php

namespace Tests\Feature\Menu;

use App\Models\Application;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Role;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MenuSetupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('menu_item_role')->delete();
        MenuItem::query()->forceDelete();
        Menu::query()->forceDelete();
        Application::query()->forceDelete();
    }

    public function test_it_can_setup_a_menu_with_application_and_items(): void
    {
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Messages',
            'application' => [
                'name' => 'Messages',
                'slug' => 'messages',
                'description' => 'Messages app',
            ],
            'menu_items' => [
                'home' => [
                    'type' => 'link',
                    'path' => '/',
                    'icon' => 'House',
                ],
                'whatsapp' => [
                    'icon' => 'MessageSquare',
                    'menu_items' => [
                        'whatsapp_templates' => [
                            'path' => '/whatsapp-templates',
                            'icon' => 'MessageSquare',
                            'name' => 'whatsapp_templates',
                        ],
                        'whatsapp_info' => [
                            'path' => '/whatsapp-info',
                            'icon' => 'MessageSquare',
                            'name' => 'whatsapp_info',
                        ],
                    ],
                ],
                'email' => [
                    'icon' => 'Mail',
                    'menu_items' => [
                        'email_templates' => [
                            'path' => '/emails',
                            'icon' => 'Mail',
                            'name' => 'email_templates',
                        ],
                    ],
                ],
                'campaigns' => [
                    'type' => 'link',
                    'path' => '/campaigns',
                    'icon' => 'Megaphone',
                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('applications', [
            'name' => 'Messages',
            'slug' => 'messages',
        ]);

        $this->assertDatabaseHas('menus', [
            'name' => 'Messages',
            'slug' => 'Messages',
        ]);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'home',
            'type' => 'link',
            'path' => '/',
        ]);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'whatsapp',
            'type' => 'header',
        ]);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'whatsapp_templates',
            'type' => 'link',
            'path' => '/whatsapp-templates',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'application',
                'items',
            ],
        ]);
    }

    public function test_it_creates_header_items_for_nested_menu_items(): void
    {
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Test Menu',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'parent_item' => [
                    'icon' => 'Folder',
                    'menu_items' => [
                        'child_item' => [
                            'path' => '/child',
                            'icon' => 'File',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'parent_item',
            'type' => 'header',
        ]);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'child_item',
            'type' => 'link',
            'path' => '/child',
        ]);

        $parent = MenuItem::where('slug', 'parent_item')->first();
        $child = MenuItem::where('slug', 'child_item')->first();

        $this->assertEquals($parent->id, $child->parent_id);
    }

    public function test_it_creates_section_items_from_the_explicit_type(): void
    {
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Test Menu',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'salamandr-ia' => [
                    'type' => 'section',
                    'menu_items' => [
                        'drivers' => [
                            'path' => '/ia/drivers',
                            'icon' => 'Cpu',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'salamandr-ia',
            'type' => 'section',
            'path' => null,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'drivers',
            'type' => 'link',
            'path' => '/ia/drivers',
        ]);
    }

    public function test_it_updates_the_type_of_existing_menu_items(): void
    {
        $this->loginRoot();

        $payload = [
            'name' => 'Test Menu',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'salamandr-ia' => [
                    'type' => 'header',
                    'menu_items' => [
                        'drivers' => [
                            'path' => '/ia/drivers',
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'salamandr-ia',
            'type' => 'header',
        ]);

        $payload['menu_items']['salamandr-ia']['type'] = 'section';

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'salamandr-ia',
            'type' => 'section',
        ]);

        $parent = MenuItem::where('slug', 'salamandr-ia')->first();
        $child = MenuItem::where('slug', 'drivers')->first();

        $this->assertEquals($parent->id, $child->parent_id);
    }

    public function test_it_rejects_an_invalid_explicit_type(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Test Menu',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'salamandr-ia' => [
                    'type' => 'secrtion',
                    'menu_items' => [
                        'drivers' => [
                            'path' => '/ia/drivers',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_it_creates_and_updates_the_menu_size(): void
    {
        $this->loginRoot();

        $payload = [
            'name' => 'Test Menu',
            'size' => 'sm',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'home' => [
                    'path' => '/',
                    'icon' => 'House',
                ],
            ],
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseHas('menus', [
            'name' => 'Test Menu',
            'size' => 'sm',
        ]);

        $payload['size'] = 'lg';

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseHas('menus', [
            'name' => 'Test Menu',
            'size' => 'lg',
        ]);
    }

    public function test_it_rejects_an_invalid_menu_size(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Test Menu',
            'size' => 'xl',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'home' => [
                    'path' => '/',
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_it_requires_application(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Messages',
            'menu_items' => [
                'home' => [
                    'type' => 'link',
                    'path' => '/',
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_it_requires_menu_items(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Messages',
            'application' => [
                'name' => 'Messages',
                'slug' => 'messages',
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_it_requires_name(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus/setup', [
            'application' => [
                'name' => 'Messages',
                'slug' => 'messages',
            ],
            'menu_items' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->postJson('/api/v1/menus/setup', [
            'name' => 'Messages',
            'application' => [
                'name' => 'Messages',
                'slug' => 'messages',
            ],
            'menu_items' => [],
        ]);
    }

    public function test_it_does_not_create_duplicate_application(): void
    {
        $this->loginRoot();

        $payload = [
            'name' => 'Messages',
            'application' => [
                'name' => 'Messages',
                'slug' => 'messages',
                'description' => 'Messages app',
            ],
            'menu_items' => [
                'home' => [
                    'type' => 'link',
                    'path' => '/',
                    'icon' => 'House',
                ],
            ],
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);
        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseCount('applications', 1);
    }

    public function test_it_does_not_create_duplicate_menu(): void
    {
        $this->loginRoot();

        $payload = [
            'name' => 'Messages',
            'application' => [
                'name' => 'Messages',
                'slug' => 'messages',
            ],
            'menu_items' => [
                'home' => [
                    'type' => 'link',
                    'path' => '/',
                    'icon' => 'House',
                ],
            ],
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);
        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseCount('menus', 1);
    }

    public function test_it_does_not_create_duplicate_menu_items(): void
    {
        $this->loginRoot();

        $payload = [
            'name' => 'Messages',
            'application' => [
                'name' => 'Messages',
                'slug' => 'messages',
            ],
            'menu_items' => [
                'home' => [
                    'type' => 'link',
                    'path' => '/',
                    'icon' => 'House',
                ],
            ],
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);
        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseCount('menu_items', 1);
    }

    public function test_it_rejects_menu_slug_when_already_registered_for_another_application(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $application = Application::factory()->create([
            'name' => 'test_1',
            'slug' => 'test_1',
        ]);

        Menu::factory()->create([
            'application_id' => $application->id,
            'name' => 'test',
            'slug' => 'test',
        ]);

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'test',
            'application' => [
                'name' => 'test_2',
                'slug' => 'test_2',
            ],
            'menu_items' => [
                'home' => [
                    'type' => 'link',
                    'path' => '/',
                    'icon' => 'House',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', __('The menu slug is already registered for another application.'));
    }

    public function test_it_creates_missing_children_when_parent_exists(): void
    {
        $this->loginRoot();

        $payload = [
            'name' => 'Messages',
            'application' => [
                'name' => 'Messages',
                'slug' => 'messages',
            ],
            'menu_items' => [
                'whatsapp' => [
                    'icon' => 'MessageSquare',
                    'menu_items' => [
                        'whatsapp_templates' => [
                            'path' => '/whatsapp-templates',
                            'icon' => 'MessageSquare',
                            'name' => 'whatsapp_templates',
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);
        $this->assertDatabaseCount('menu_items', 2);

        $payload['menu_items']['whatsapp']['menu_items']['whatsapp_info'] = [
            'path' => '/whatsapp-info',
            'icon' => 'MessageSquare',
            'name' => 'whatsapp_info',
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);
        $this->assertDatabaseCount('menu_items', 3);
    }

    public function test_it_attaches_roles_to_menu_items(): void
    {
        $this->loginRoot();

        $adminRole = Role::where('key', 'admin')->first();
        $messagesRole = Role::where('key', 'messages')->first();

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Test Menu',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'home' => [
                    'path' => '/',
                    'icon' => 'House',
                    'roles' => ['admin'],
                ],
                'settings' => [
                    'path' => '/settings',
                    'icon' => 'Settings',
                    'roles' => ['admin', 'messages'],
                ],
                'public_page' => [
                    'path' => '/public',
                    'icon' => 'Globe',
                ],
            ],
        ]);

        $response->assertStatus(201);

        $home = MenuItem::where('slug', 'home')->first();
        $settings = MenuItem::where('slug', 'settings')->first();
        $publicPage = MenuItem::where('slug', 'public_page')->first();

        $this->assertDatabaseHas('menu_item_role', [
            'menu_item_id' => $home->id,
            'role_id' => $adminRole->id,
        ]);

        $this->assertCount(1, $home->roles);

        $this->assertDatabaseHas('menu_item_role', [
            'menu_item_id' => $settings->id,
            'role_id' => $adminRole->id,
        ]);

        $this->assertDatabaseHas('menu_item_role', [
            'menu_item_id' => $settings->id,
            'role_id' => $messagesRole->id,
        ]);

        $this->assertCount(2, $settings->roles);

        $this->assertCount(0, $publicPage->roles);
    }

    public function test_it_ignores_nonexistent_role_keys(): void
    {
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Test Menu',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'home' => [
                    'path' => '/',
                    'icon' => 'House',
                    'roles' => ['admin', 'nonexistent_role'],
                ],
            ],
        ]);

        $response->assertStatus(201);

        $home = MenuItem::where('slug', 'home')->first();

        $this->assertCount(1, $home->roles);
    }

    public function test_it_rejects_menu_item_slug_when_already_registered_for_another_menu(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $application = Application::factory()->create();
        $menu = Menu::factory()->create(['application_id' => $application->id]);
        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'slug' => 'home',
            'parent_id' => null,
        ]);

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Another Menu',
            'application' => [
                'name' => 'Another App',
                'slug' => 'another-app',
            ],
            'menu_items' => [
                'home' => [
                    'path' => '/',
                    'icon' => 'House',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', __('The menu item slug ":slug" is already registered for another menu or parent.', ['slug' => 'home']));
    }

    public function test_it_recreates_the_menu_and_items_when_force_is_true(): void
    {
        $this->loginRoot();

        $payload = [
            'name' => 'Test Menu',
            'size' => 'md',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'home' => [
                    'type' => 'link',
                    'name' => 'Home',
                    'path' => '/',
                    'icon' => 'House',
                    'roles' => ['root'],
                ],
                'admin' => [
                    'type' => 'section',
                    'name' => 'Admin',
                    'icon' => 'Shield',
                    'roles' => ['admin'],
                    'menu_items' => [
                        'users' => [
                            'name' => 'Users',
                            'path' => '/users',
                            'icon' => 'Users',
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $menu = Menu::where('slug', 'Test Menu')->firstOrFail();
        $home = MenuItem::where('slug', 'home')->firstOrFail();
        $messages = Role::where('key', 'messages')->firstOrFail();
        $root = Role::where('key', 'root')->firstOrFail();
        $admin = Role::where('key', 'admin')->firstOrFail();

        $home->update([
            'name' => 'Modificado',
            'path' => '/cambiado',
            'icon' => 'Star',
            'sort_order' => 99,
        ]);
        $home->roles()->attach($messages->id);

        $manual = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'slug' => 'manual',
            'name' => 'Manual',
        ]);

        $payload['force'] = true;
        $payload['size'] = 'lg';

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseCount('menus', 1);
        $this->assertDatabaseCount('menu_items', 3);

        $this->assertDatabaseMissing('menu_items', ['id' => $home->id]);
        $this->assertDatabaseMissing('menu_items', ['id' => $manual->id]);

        $newMenu = Menu::where('slug', 'Test Menu')->firstOrFail();
        $newHome = MenuItem::where('slug', 'home')->firstOrFail();
        $newUsers = MenuItem::where('slug', 'users')->firstOrFail();

        $this->assertNotSame($menu->id, $newMenu->id);
        $this->assertSame('lg', $newMenu->size->value);

        $this->assertSame('Home', $newHome->name);
        $this->assertSame('/', $newHome->path);
        $this->assertSame('House', $newHome->icon);
        $this->assertSame(0, $newHome->sort_order);

        $this->assertDatabaseHas('menu_item_role', [
            'menu_item_id' => $newHome->id,
            'role_id' => $root->id,
        ]);
        $this->assertDatabaseMissing('menu_item_role', [
            'menu_item_id' => $newHome->id,
            'role_id' => $messages->id,
        ]);

        $this->assertDatabaseHas('menu_item_role', [
            'menu_item_id' => $newUsers->id,
            'role_id' => $admin->id,
        ]);
    }

    public function test_it_keeps_manual_changes_when_force_is_false(): void
    {
        $this->loginRoot();

        $payload = [
            'name' => 'Test Menu',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'home' => [
                    'type' => 'link',
                    'name' => 'Home',
                    'path' => '/',
                    'icon' => 'House',
                ],
            ],
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        MenuItem::where('slug', 'home')->firstOrFail()->update(['name' => 'Personalizado']);

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseHas('menu_items', [
            'slug' => 'home',
            'name' => 'Personalizado',
        ]);
    }

    public function test_it_recreates_a_soft_deleted_menu_when_force_is_true(): void
    {
        $this->loginRoot();

        $payload = [
            'name' => 'Test Menu',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [
                'home' => [
                    'type' => 'link',
                    'path' => '/',
                ],
            ],
        ];

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        Menu::where('slug', 'Test Menu')->firstOrFail()->delete();

        $payload['force'] = true;

        $this->postJson('/api/v1/menus/setup', $payload)->assertStatus(201);

        $this->assertDatabaseCount('menus', 1);
        $this->assertDatabaseCount('menu_items', 1);

        $recreated = Menu::where('slug', 'Test Menu')->firstOrFail();

        $this->assertNull($recreated->deleted_at);
    }

    public function test_it_does_not_delete_a_menu_from_another_application_when_force_is_true(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $application = Application::factory()->create([
            'name' => 'Test App',
            'slug' => 'test-app',
        ]);
        $menu = Menu::factory()->create([
            'application_id' => $application->id,
            'name' => 'Test Menu',
            'slug' => 'Test Menu',
        ]);
        $item = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'slug' => 'home',
            'parent_id' => null,
        ]);

        $response = $this->postJson('/api/v1/menus/setup', [
            'name' => 'Test Menu',
            'force' => true,
            'application' => [
                'name' => 'Other App',
                'slug' => 'other-app',
            ],
            'menu_items' => [
                'home' => [
                    'path' => '/',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', __('The menu slug is already registered for another application.'));

        $this->assertDatabaseHas('menus', ['id' => $menu->id]);
        $this->assertDatabaseHas('menu_items', ['id' => $item->id]);
    }
}
