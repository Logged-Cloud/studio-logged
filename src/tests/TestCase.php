<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Production-data safety guard.
     *
     * Runs BEFORE parent::setUp() because that boots the app and the
     * RefreshDatabase trait setup which calls migrate:fresh. If anything
     * has the test connection pointed at MySQL (missing phpunit.xml
     * <server> tag, docker-compose env override, config:cache leak)
     * RefreshDatabase would drop every row in the live database.
     *
     * The guard reads $_SERVER and $_ENV directly because we have not yet
     * booted Laravel · config() would error here. PHPUnit's <server> tag
     * populates both, which is exactly what we need.
     *
     * Removing this is a production-data risk. studio-logged was emptied by
     * exactly this misconfiguration on 2026-05-21.
     */
    protected function setUp(): void
    {
        $connection = $_SERVER['DB_CONNECTION'] ?? $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: '';
        $database = $_SERVER['DB_DATABASE'] ?? $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '';

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "REFUSING to run tests against {$connection} / {$database}. "
                ."Tests must use sqlite :memory: · check phpunit.xml's <server> "
                ."tags for DB_CONNECTION and DB_DATABASE. This guard prevents "
                ."RefreshDatabase from dropping production tables."
            );
        }

        parent::setUp();
    }
}
