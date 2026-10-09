<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature', 'Architecture', 'Unit');

// Bind RefreshDatabase per module test dir as modules land, e.g.:
// uses(RefreshDatabase::class)->in('Feature/M001_Auth');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
