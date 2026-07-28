<?php

namespace Tests\Feature;

use App\Enums\DocumentState;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Legal_Compliance']);
        Role::create(['name' => 'Manager']);
    }

    public function test_user_can_register_and_receive_sanctum_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Officer',
            'email' => 'officer@example.com',
            'password' => 'password123',
            'role' => 'Legal_Compliance',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['access_token', 'token_type', 'user']);
    }

    public function test_authenticated_user_can_create_document(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/documents', [
            'title' => 'API Contract',
            'content' => 'Created via Sanctum endpoint.',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'API Contract', 'state' => DocumentState::DRAFT->value]);
    }

    public function test_api_returns_403_on_invalid_state_transition(): void
    {
        $user = User::factory()->create();
        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'API Contract',
            'content' => 'Created via Sanctum endpoint.',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/documents/{$document->id}", [
            'state' => DocumentState::MANAGER_APPROVED->value,
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'State transition forbidden or document is immutable.']);
    }
}
