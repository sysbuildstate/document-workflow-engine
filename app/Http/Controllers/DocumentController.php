<?php

namespace App\Http\Controllers;

use App\Enums\DocumentState;
use App\Exceptions\InvalidStateTransitionException;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentStateRequest;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Document::with(['user', 'history'])->latest();

        if (! $user->hasRole(['Legal_Compliance', 'Manager'])) {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->get());
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = Document::create([
            'user_id' => Auth::id(),
            'title' => $request->validated('title'),
            'content' => $request->validated('content'),
        ]);

        return response()->json($document->load('history'), 201);
    }

    public function show(Document $document): JsonResponse
    {
        Gate::authorize('view', $document);

        return response()->json($document->load(['user', 'history']));
    }

    public function update(UpdateDocumentStateRequest $request, Document $document): JsonResponse
    {
        Gate::authorize('update', $document);

        try {
            $payload = $request->validated();

            if (isset($payload['state'])) {
                $payload['state'] = DocumentState::from($payload['state']);
            }

            $document->update($payload);

            return response()->json($document->fresh(['user', 'history']));
        } catch (InvalidStateTransitionException $e) {
            return response()->json([
                'message' => 'State transition forbidden or document is immutable.',
            ], 403);
        }
    }

    public function destroy(Document $document): JsonResponse
    {
        Gate::authorize('delete', $document);

        try {
            $document->delete();

            return response()->json(null, 204);
        } catch (InvalidStateTransitionException $e) {
            return response()->json([
                'message' => 'Cannot delete an Executed document.',
            ], 403);
        }
    }
}
