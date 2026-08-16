<?php

namespace EtsvThor\BifrostBridge\Tests\Feature;

use EtsvThor\BifrostBridge\Tests\Feature\Traits\CreatesRoles;
use EtsvThor\BifrostBridge\Tests\Feature\Traits\CreatesUsers;
use EtsvThor\BifrostBridge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WebhookRouteDisabledTest extends TestCase
{
    use CreatesUsers, CreatesRoles;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('bifrost.auth_push_key', null);
    }

    #[Test]
    public function the_webhook_route_is_not_registered_without_a_push_key(): void
    {
        $this->assertFalse($this->app['router']->has('webhooks.bifrost'));

        $this->postJson('/webhooks/bifrost', ['roles' => []])->assertNotFound();
    }
}
