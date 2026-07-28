<?php

namespace Tests\Feature;

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

        $this->assertSame('Draft', $document->state);
    }

    public function test_user_can_be_assigned_legal_compliance_role_for_state_transitions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Legal_Compliance');

        $this->assertTrue($user->hasRole('Legal_Compliance'));
        $this->assertFalse($user->hasRole('Manager'));
    }
}
