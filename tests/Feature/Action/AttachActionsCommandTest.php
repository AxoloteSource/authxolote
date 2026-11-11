<?php

namespace Tests\Feature\Action;

use App\Models\Action;
use Tests\TestCase;

class AttachActionsCommandTest extends TestCase
{
    public function test_command_creates_all_configured_actions(): void
    {
        $this->artisan('authxolote:actions')->assertExitCode(0);

        foreach (config('actions.admin') as $name => $description) {
            $this->assertDatabaseHas('actions', [
                'name' => $name,
                'description' => __($description),
            ]);
        }
    }

    public function test_command_stores_action_descriptions_in_the_current_locale(): void
    {
        app()->setLocale('es');

        $this->artisan('authxolote:actions')->assertExitCode(0);

        $this->assertDatabaseHas('actions', [
            'name' => 'auth.role.index',
            'description' => 'Listar roles',
        ]);

        $this->assertDatabaseHas('actions', [
            'name' => 'auth.menu_items.roles.update',
            'description' => 'Asignar o quitar un rol de un ítem de menú',
        ]);
    }

    public function test_command_updates_existing_action_description_without_changing_its_id(): void
    {
        config(['actions' => ['admin' => ['custom.action.update' => 'New description']]]);

        $action = Action::factory()->create([
            'name' => 'custom.action.update',
            'description' => 'Old description',
        ]);
        $originalId = $action->id;

        $this->artisan('authxolote:actions')->assertExitCode(0);

        $action->refresh();

        $this->assertSame($originalId, $action->id);
        $this->assertSame('New description', $action->description);
    }

    public function test_command_reuses_a_soft_deleted_action(): void
    {
        config(['actions' => ['admin' => ['custom.action.soft_deleted' => 'Soft deleted action']]]);

        Action::factory()->create(['name' => 'custom.action.soft_deleted'])->delete();

        $this->artisan('authxolote:actions')->assertExitCode(0);

        $this->assertSame(
            1,
            Action::withTrashed()->where('name', 'custom.action.soft_deleted')->count(),
        );
    }

    public function test_command_fails_when_configuration_is_empty(): void
    {
        config(['actions' => []]);

        $this->artisan('authxolote:actions')->assertExitCode(1);
    }

    public function test_all_action_translation_keys_exist_in_every_locale(): void
    {
        $english = require lang_path('en/actions.php');
        $spanish = require lang_path('es/actions.php');

        $this->assertSame(array_keys($english), array_keys($spanish));

        foreach (config('actions.admin') as $name => $translationKey) {
            $flatKey = str_replace('actions.', '', $translationKey);

            $this->assertArrayHasKey($flatKey, $english, "Missing [$flatKey] translation in [en] for [$name].");
            $this->assertArrayHasKey($flatKey, $spanish, "Missing [$flatKey] translation in [es] for [$name].");
        }
    }
}
