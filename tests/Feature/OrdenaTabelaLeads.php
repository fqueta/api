<?php

namespace Tests\Feature;

use App\Http\Controllers\ClientesController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrdenaTabelaLeads extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');
        // $d = (new ClientesController)->
        $response->assertStatus(200);
    }
}
