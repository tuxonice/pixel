<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCanSeeHomePageTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
