<?php

namespace Tests\Feature\Setting;

use App\Enums\SettingEnum;
use App\Enums\SettingValueTypeEnum;
use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Tests\TestCase;

class SettingSeederTest extends TestCase
{
    public function test_existing_setting_values_are_not_overwritten(): void
    {
        Setting::query()
            ->where('name', SettingEnum::OtpLength->value)
            ->update(['value' => '8']);

        $this->seed(SettingSeeder::class);

        $this->assertSame('8', Setting::findByKey(SettingEnum::OtpLength)?->value);
    }

    public function test_existing_setting_metadata_is_not_overwritten(): void
    {
        Setting::query()
            ->where('name', SettingEnum::PasswordMinLength->value)
            ->update([
                'group' => 'custom',
                'setting_value_type_id' => SettingValueTypeEnum::String->value,
            ]);

        $this->seed(SettingSeeder::class);

        $setting = Setting::findByKey(SettingEnum::PasswordMinLength);

        $this->assertSame('custom', $setting?->group);
        $this->assertSame(SettingValueTypeEnum::String->value, $setting?->setting_value_type_id);
    }

    public function test_missing_settings_are_created(): void
    {
        Setting::findByKey(SettingEnum::OtpRateLimitMax)?->forceDelete();

        $this->seed(SettingSeeder::class);

        $this->assertSame('5', Setting::findByKey(SettingEnum::OtpRateLimitMax)?->value);
    }

    public function test_running_the_seeder_twice_does_not_duplicate_settings(): void
    {
        $settingsCount = Setting::count();

        $this->seed(SettingSeeder::class);

        $this->assertSame($settingsCount, Setting::count());
    }
}
