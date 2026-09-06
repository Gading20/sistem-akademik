<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest users must be redirected to the login page.
     */
    public function test_the_dashboard_requires_authentication(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
