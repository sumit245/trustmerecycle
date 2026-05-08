<?php

namespace App\Http\Controllers;

use App\Models\CollectionJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ProtectedFileController extends Controller
{
    public function show(Request $request, CollectionJob $collectionJob, string $field): Response
    {
        abort_unless(in_array($field, ['collection_proof_image', 'challan_image'], true), 404);

        $path = $collectionJob->{$field};
        abort_unless(is_string($path) && $path !== '', 404);

        $user = $request->user();
        abort_unless($user, 403);

        if (! $user->isAdmin()) {
            abort_unless(
                $collectionJob->godown && $collectionJob->godown->vendor_id === $user->id,
                403,
            );
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }
}
