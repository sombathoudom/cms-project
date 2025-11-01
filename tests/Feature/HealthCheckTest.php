<?php

use Illuminate\Support\Facades\Redis;

it('returns ok health status', function () {
    Redis::shouldReceive('connection->ping')->andReturn('PONG');

    $response = $this->get('/health');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'uptime',
            'db',
            'redis',
        ])
        ->assertJson(['status' => 'ok', 'db' => 'ok', 'redis' => 'ok']);
});

it('handles redis failure gracefully', function () {
    Redis::shouldReceive('connection->ping')->andThrow(new RuntimeException('Redis unavailable'));

    $response = $this->get('/health');
    $response->assertStatus(503)
        ->assertJson(['status' => 'error', 'redis' => 'error']);
});
