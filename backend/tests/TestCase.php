<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');

        Artisan::call('migrate', ['--force' => true]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    protected function createUserWithAccess(array $roles = [], array $permissions = []): User
    {
        $user = User::factory()->create();

        if ($roles !== []) {
            $user->syncRoles($roles);
        }

        if ($permissions !== []) {
            $user->syncPermissions($permissions);
        }

        return $user->fresh();
    }

    protected function actingAsUserWithAccess(array $roles = [], array $permissions = []): User
    {
        $user = $this->createUserWithAccess($roles, $permissions);
        Sanctum::actingAs($user);

        return $user;
    }
}
