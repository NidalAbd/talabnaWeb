<?php

namespace Tests\Feature;

use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use MigratesTolerantly;

    public function test_the_home_page_answers(): void
    {
        $this->migrateTolerantly();

        $this->get('/')->assertStatus(200);
    }
}
