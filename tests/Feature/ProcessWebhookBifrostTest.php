<?php

namespace EtsvThor\BifrostBridge\Tests\Feature;

use EtsvThor\BifrostBridge\BifrostBridge;
use EtsvThor\BifrostBridge\Data\BifrostRoleData;
use EtsvThor\BifrostBridge\Jobs\ProcessWebhookBifrost;
use EtsvThor\BifrostBridge\Tests\Feature\Traits\CreatesRoles;
use EtsvThor\BifrostBridge\Tests\Feature\Traits\CreatesUsers;
use EtsvThor\BifrostBridge\Tests\TestCase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelData\DataCollection;

class ProcessWebhookBifrostTest extends TestCase
{
    use CreatesUsers, CreatesRoles;

    protected function tearDown(): void
    {
        BifrostBridge::resolveRoleClassUsing(null);

        parent::tearDown();
    }

    /**
     * @param array<int, array{id: int, name: string, users: array<int, int>}> $roles
     */
    protected function handleWebhook(array $roles): void
    {
        (new ProcessWebhookBifrost(
            BifrostRoleData::collect($roles, DataCollection::class),
        ))->handle();
    }

    #[Test]
    public function it_bails_when_no_role_class_is_available(): void
    {
        BifrostBridge::resolveRoleClassUsing(fn () => null);

        Log::shouldReceive('warning')
            ->once()
            ->with('No role class found, but bifrost auth push triggered.');
        Log::shouldReceive('debug')->never();

        $this->handleWebhook([['id' => 1, 'name' => 'admin', 'users' => [1]]]);
    }

    #[Test]
    public function it_ignores_roles_that_do_not_exist_locally(): void
    {
        $user = $this->createUser(1);

        $this->handleWebhook([['id' => 1, 'name' => 'does-not-exist', 'users' => [1]]]);

        $this->assertCount(0, $user->refresh()->roles);
    }

    #[Test]
    public function it_ignores_bifrost_users_that_do_not_exist_locally(): void
    {
        $role = $this->createRole('admin');

        $this->handleWebhook([['id' => 1, 'name' => 'admin', 'users' => [1, 2, 3]]]);

        $this->assertCount(0, $role->refresh()->users);
    }

    // --- auto_assign disabled -------------------------------------------------

    #[Test]
    public function it_attaches_and_detaches_users_without_auto_assign(): void
    {
        config()->set('bifrost.auto_assign', false);

        $role = $this->createRole('admin');
        $stays = $this->createUser(1);
        $added = $this->createUser(2);
        $removed = $this->createUser(3);

        $role->users()->attach([$stays->getKey(), $removed->getKey()]);

        $this->handleWebhook([['id' => 10, 'name' => 'admin', 'users' => [1, 2]]]);

        $this->assertEqualsCanonicalizing(
            [$stays->getKey(), $added->getKey()],
            $role->refresh()->users->pluck('id')->all(),
        );
    }

    #[Test]
    public function it_detaches_manually_assigned_users_when_auto_assign_is_disabled(): void
    {
        config()->set('bifrost.auto_assign', false);

        $role = $this->createRole('admin');
        $manual = $this->createUser(1);

        $role->users()->attach($manual->getKey(), ['auto_assigned' => 0]);

        $this->handleWebhook([['id' => 10, 'name' => 'admin', 'users' => []]]);

        $this->assertCount(0, $role->refresh()->users);
    }

    #[Test]
    public function it_does_not_flag_attached_users_as_auto_assigned_when_disabled(): void
    {
        config()->set('bifrost.auto_assign', false);

        $role = $this->createRole('admin');
        $user = $this->createUser(1);

        $this->handleWebhook([['id' => 10, 'name' => 'admin', 'users' => [1]]]);

        $this->assertEquals(0, $role->refresh()->users->first()->pivot->auto_assigned);
        $this->assertTrue($user->refresh()->hasRole('admin'));
    }

    // --- auto_assign enabled --------------------------------------------------

    #[Test]
    public function it_flags_attached_users_as_auto_assigned_when_enabled(): void
    {
        config()->set('bifrost.auto_assign', true);

        $role = $this->createRole('admin');
        $this->createUser(1);

        $this->handleWebhook([['id' => 10, 'name' => 'admin', 'users' => [1]]]);

        $this->assertEquals(1, $role->refresh()->users->first()->pivot->auto_assigned);
    }

    #[Test]
    public function it_does_not_detach_manually_assigned_users_when_auto_assign_is_enabled(): void
    {
        config()->set('bifrost.auto_assign', true);

        $role = $this->createRole('admin');
        $manual = $this->createUser(1);
        $auto = $this->createUser(2);

        $role->users()->attach($manual->getKey(), ['auto_assigned' => 0]);
        $role->users()->attach($auto->getKey(), ['auto_assigned' => 1]);

        $this->handleWebhook([['id' => 10, 'name' => 'admin', 'users' => []]]);

        $this->assertEquals(
            [$manual->getKey()],
            $role->refresh()->users->pluck('id')->all(),
        );
    }

    // --- auth_push_detach_on_remove ------------------------------------------

    #[Test]
    public function it_detaches_every_user_from_removed_roles_when_auto_assign_is_disabled(): void
    {
        config()->set('bifrost.auto_assign', false);
        config()->set('bifrost.auth_push_detach_on_remove', true);

        $gone = $this->createRole('gone');
        $kept = $this->createRole('kept');

        $manual = $this->createUser(1);
        $auto = $this->createUser(2);

        $gone->users()->attach($manual->getKey(), ['auto_assigned' => 0]);
        $gone->users()->attach($auto->getKey(), ['auto_assigned' => 1]);
        $kept->users()->attach($manual->getKey(), ['auto_assigned' => 0]);

        $this->handleWebhook([['id' => 10, 'name' => 'kept', 'users' => [1]]]);

        $this->assertCount(0, $gone->refresh()->users);
        $this->assertCount(1, $kept->refresh()->users);
    }

    #[Test]
    public function it_only_detaches_auto_assigned_users_from_removed_roles_when_auto_assign_is_enabled(): void
    {
        config()->set('bifrost.auto_assign', true);
        config()->set('bifrost.auth_push_detach_on_remove', true);

        $gone = $this->createRole('gone');

        $manual = $this->createUser(1);
        $auto = $this->createUser(2);

        $gone->users()->attach($manual->getKey(), ['auto_assigned' => 0]);
        $gone->users()->attach($auto->getKey(), ['auto_assigned' => 1]);

        $this->handleWebhook([]);

        $this->assertEquals(
            [$manual->getKey()],
            $gone->refresh()->users->pluck('id')->all(),
        );
    }

    #[Test]
    public function it_keeps_users_on_removed_roles_when_detach_on_remove_is_disabled(): void
    {
        config()->set('bifrost.auth_push_detach_on_remove', false);

        $gone = $this->createRole('gone');
        $user = $this->createUser(1);
        $gone->users()->attach($user->getKey(), ['auto_assigned' => 1]);

        $this->handleWebhook([]);

        $this->assertCount(1, $gone->refresh()->users);
    }
}
