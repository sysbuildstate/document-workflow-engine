<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Legal_Compliance']);
        Role::create(['name' => 'Manager']);
    }

    public function test_user_cannot_view_another_users_private_document(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Secret Agreement',
            'content' => 'Confidential terms.',
        ]);

        $response = $this->actingAs($intruder, 'sanctum')->getJson("/api/documents/{$document->id}");

        $response->assertStatus(403);
    }

    public function test_privileged_role_can_view_any_users_document(): void
    {
        $owner = User::factory()->create();
        $officer = User::factory()->create();
        $officer->assignRole('Legal_Compliance');

        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Secret Agreement',
            'content' => 'Confidential terms.',
        ]);

        $response = $this->actingAs($officer, 'sanctum')->getJson("/api/documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Secret Agreement']);
    }
}
