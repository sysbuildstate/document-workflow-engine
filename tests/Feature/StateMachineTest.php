<?php

namespace Tests\Feature;

use App\Enums\DocumentState;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Legal_Compliance']);
        Role::create(['name' => 'Manager']);
    }

    public function test_document_initializes_with_default_draft_state(): void
    {
        $user = User::factory()->create();

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'Service Agreement',
            'content' => 'Terms and conditions apply.',
        ]);

        $this->assertSame(DocumentState::DRAFT, $document->state);
    }

    public function test_valid_unidirectional_transition_with_proper_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['Legal_Compliance', 'Manager']);
        $this->actingAs($user);

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'Service Agreement',
            'content' => 'Terms and conditions apply.',
        ]);

        $document->update(['state' => DocumentState::PENDING_LEGAL_REVIEW]);
        $this->assertSame(DocumentState::PENDING_LEGAL_REVIEW, $document->state);

        $document->update(['state' => DocumentState::MANAGER_APPROVED]);
        $this->assertSame(DocumentState::MANAGER_APPROVED, $document->state);

        $document->update(['state' => DocumentState::EXECUTED]);
        $this->assertSame(DocumentState::EXECUTED, $document->state);

        $this->assertDatabaseCount('document_histories', 3);
    }

    public function test_skipping_states_throws_exception(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'Service Agreement',
            'content' => 'Terms and conditions apply.',
        ]);

        $this->expectException(InvalidStateTransitionException::class);
        $document->update(['state' => DocumentState::MANAGER_APPROVED]);
    }

    public function test_unauthorized_user_cannot_transition_past_legal_review(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'Service Agreement',
            'content' => 'Terms and conditions apply.',
        ]);

        $document->update(['state' => DocumentState::PENDING_LEGAL_REVIEW]);

        $this->expectException(InvalidStateTransitionException::class);
        $document->update(['state' => DocumentState::MANAGER_APPROVED]);
    }

    public function test_executed_documents_are_immutable(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['Legal_Compliance', 'Manager']);
        $this->actingAs($user);

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'Service Agreement',
            'content' => 'Terms and conditions apply.',
        ]);

        $document->update(['state' => DocumentState::PENDING_LEGAL_REVIEW]);
        $document->update(['state' => DocumentState::MANAGER_APPROVED]);
        $document->update(['state' => DocumentState::EXECUTED]);

        $this->expectException(InvalidStateTransitionException::class);
        $document->update(['title' => 'Hacked Title Modification']);
    }
}
