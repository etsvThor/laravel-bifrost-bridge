<?php

namespace EtsvThor\BifrostBridge\Http\Controllers;

use EtsvThor\BifrostBridge\DataTransferObjects\Collections\BifrostRoleDataCollection;
use EtsvThor\BifrostBridge\Jobs\ProcessWebhookBifrost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController
{
    /**
     * Synchronize roles with bifrost
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bifrost(Request $request): JsonResponse
    {
        if (config('bifrost.enabled') !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Bifrost disabled.',
            ], 500);
        }

        if ($request->verifySignature(config('bifrost.auth_push_key'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature.',
            ], 403);
        }

        ProcessWebhookBifrost::dispatch(
            BifrostRoleDataCollection::create($request->get('roles'))
        );

        return response()->json(['success' => true]);
    }
}
