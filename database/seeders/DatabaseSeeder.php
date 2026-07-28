<?php

namespace Database\Seeders;

use App\Enums\DocumentState;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $legalRole = Role::firstOrCreate(['name' => 'Legal_Compliance']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);

        $author = User::firstOrCreate(
            ['email' => 'author@example.com'],
            [
                'name' => 'Staff Author',
                'password' => Hash::make('password123'),
            ]
        );

        $compliance = User::firstOrCreate(
            ['email' => 'officer@example.com'],
            [
                'name' => 'Compliance Officer',
                'password' => Hash::make('password123'),
            ]
        );
        $compliance->assignRole($legalRole);

        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'General Manager',
                'password' => Hash::make('password123'),
            ]
        );
        $manager->assignRole($managerRole);

        Auth::login($author);

        Document::firstOrCreate(
            ['title' => 'Standard Non-Disclosure Agreement'],
            [
                'user_id' => $author->id,
                'content' => 'This is a standard draft NDA awaiting preliminary review.',
                'state' => DocumentState::DRAFT->value,
            ]
        );

        $reviewDoc = Document::firstOrCreate(
            ['title' => 'Q3 Vendor Contract'],
            [
                'user_id' => $author->id,
                'content' => 'Vendor terms and payment schedules for Q3 operations.',
                'state' => DocumentState::DRAFT->value,
            ]
        );
        if ($reviewDoc->state === DocumentState::DRAFT) {
            $reviewDoc->update(['state' => DocumentState::PENDING_LEGAL_REVIEW->value]);
        }

        $approvedDoc = Document::firstOrCreate(
            ['title' => 'Annual Software License'],
            [
                'user_id' => $author->id,
                'content' => 'Enterprise licensing agreement for internal developer tools.',
                'state' => DocumentState::DRAFT->value,
            ]
        );
        if ($approvedDoc->state === DocumentState::DRAFT) {
            $approvedDoc->update(['state' => DocumentState::PENDING_LEGAL_REVIEW->value]);
            Auth::login($compliance);
            $approvedDoc->update(['state' => DocumentState::MANAGER_APPROVED->value]);
        }

        $executedDoc = Document::firstOrCreate(
            ['title' => 'Master Services Agreement 2026'],
            [
                'user_id' => $author->id,
                'content' => 'Fully executed contract with binding arbitration clauses.',
                'state' => DocumentState::DRAFT->value,
            ]
        );
        if ($executedDoc->state === DocumentState::DRAFT) {
            Auth::login($author);
            $executedDoc->update(['state' => DocumentState::PENDING_LEGAL_REVIEW->value]);
            Auth::login($compliance);
            $executedDoc->update(['state' => DocumentState::MANAGER_APPROVED->value]);
            Auth::login($manager);
            $executedDoc->update(['state' => DocumentState::EXECUTED->value]);
        }

        Auth::logout();
    }
}
