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

class DocumentController extends Controller
{
    public function index(): JsonResponse
    {
        $documents = Document::with(['user', 'history'])->latest()->get();

        return response()->json($documents);
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
        return response()->json($document->load(['user', 'history']));
    }

    public function update(UpdateDocumentStateRequest $request, Document $document): JsonResponse
    {
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
