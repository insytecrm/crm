<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Tenancy;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        if (app()->bound(Tenancy::class) && tenancy()->initialized) {
            tenancy()->end();
        }

        if (array_key_exists('tenant', DB::getConnections())) {
            DB::purge('tenant');
        }

        foreach (glob(database_path('tenant*.sqlite')) ?: [] as $file) {
            $this->deleteSqliteFile($file);
        }

        parent::tearDown();
    }

    protected function deleteSqliteFile(string $path): void
    {
        if (! is_file($path)) {
            return;
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                unlink($path);

                return;
            } catch (\Throwable) {
                gc_collect_cycles();
                usleep(100000);
            }
        }
    }
}
