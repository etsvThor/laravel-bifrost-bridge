<?php

namespace EtsvThor\BifrostBridge\Tests\Feature;

use EtsvThor\BifrostBridge\Jobs\ProcessWebhookBifrost;
use EtsvThor\BifrostBridge\Tests\Feature\Traits\CreatesRoles;
use EtsvThor\BifrostBridge\Tests\Feature\Traits\CreatesUsers;
use EtsvThor\BifrostBridge\Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;

class WebhookControllerTest extends TestCase
{
    use CreatesUsers, CreatesRoles;

    /**
     * @param array<string, mixed> $payload
     */
    protected function postWebhook(array $payload, ?string $signature = null, bool $withHeader = true): \Illuminate\Testing\TestResponse
    {
        $content = json_encode($payload);

        $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];

        if ($withHeader) {
            $headers['X-Signature'] = $signature ?? hash_hmac('sha256', $content, config('bifrost.auth_push_key'));
        }

        return $this->call(
            'POST',
            '/webhooks/bifrost',
            server: $this->transformHeadersToServerVars($headers),
            content: $content,
        );
    }

    #[Test]
    public function the_webhook_route_is_registered(): void
    {
        $this->assertTrue($this->app['router']->has('webhooks.bifrost'));
    }

    #[Test]
    public function it_dispatches_the_job_for_a_valid_signature(): void
    {
        Bus::fake();

        $payload = ['roles' => [
            ['id' => 1, 'name' => 'admin', 'users' => [1, 2]],
            ['id' => 2, 'name' => 'member', 'users' => []],
        ]];

        $this->postWebhook($payload)
            ->assertOk()
            ->assertExactJson(['success' => true]);

        Bus::assertDispatched(ProcessWebhookBifrost::class, function (ProcessWebhookBifrost $job) {
            $roles = (fn () => $this->roles)->call($job);

            return $roles->count() === 2
                && $roles[0]->name === 'admin'
                && $roles[0]->users === [1, 2]
                && $roles[1]->name === 'member'
                && $roles[1]->users === [];
        });
    }

    #[Test]
    public function it_rejects_an_invalid_signature(): void
    {
        Bus::fake();

        $this->postWebhook(['roles' => []], 'not-the-right-signature')
            ->assertForbidden()
            ->assertExactJson(['success' => false, 'message' => 'Invalid signature.']);

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function it_rejects_a_missing_signature(): void
    {
        Bus::fake();

        $this->postWebhook(['roles' => []], withHeader: false)
            ->assertForbidden();

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function it_rejects_a_signature_that_does_not_match_the_body(): void
    {
        Bus::fake();

        $signature = hash_hmac('sha256', json_encode(['roles' => []]), config('bifrost.auth_push_key'));

        $this->postWebhook(['roles' => [['id' => 1, 'name' => 'admin', 'users' => []]]], $signature)
            ->assertForbidden();

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function it_fails_when_bifrost_is_disabled(): void
    {
        Bus::fake();

        config()->set('bifrost.enabled', false);

        $this->postWebhook(['roles' => []])
            ->assertStatus(500)
            ->assertExactJson(['success' => false, 'message' => 'Bifrost disabled.']);

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function it_actually_syncs_roles_when_the_queue_runs_synchronously(): void
    {
        $role = $this->createRole('admin');
        $user = $this->createUser(1);

        $this->postWebhook(['roles' => [
            ['id' => 1, 'name' => 'admin', 'users' => [1]],
        ]])->assertOk();

        $this->assertTrue($user->refresh()->hasRole('admin'));
        $this->assertCount(1, $role->refresh()->users);
    }
}
