<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetPasswordPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_password_page_renders_with_token_and_email(): void
    {
        $response = $this->get(route('filament.admin.pages.set-password', [
            'token' => 'test-token',
            'email' => 'user@example.com',
        ]));

        $response->assertOk();
        $response->assertSee('Set Up Your Password');
        $response->assertSee('wire:submit="setPassword"', false);
    }
}
