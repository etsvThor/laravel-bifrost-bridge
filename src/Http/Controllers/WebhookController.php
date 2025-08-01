<?php

namespace EtsvThor\BifrostBridge\Http\Controllers;

use EtsvThor\BifrostBridge\Data\BifrostRoleData;
use EtsvThor\BifrostBridge\Jobs\ProcessWebhookBifrost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\LaravelData\DataCollection;

class WebhookController
{
    /**
     * Synchronize roles with bifrost
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bifrost(Request $request): JsonResponse
    {
        if (config('bifrost.enabled') !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Bifrost disabled.',
            ], 500);
        }

        if (! $request->verifySignature(config('bifrost.auth_push_key'))) { // @phpstan-ignore method.notFound
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature.',
            ], 403);
        }

        ProcessWebhookBifrost::dispatch(
            BifrostRoleData::collect($request->get('roles'), DataCollection::class),
        );

        return response()->json(['success' => true]);
    }
}
