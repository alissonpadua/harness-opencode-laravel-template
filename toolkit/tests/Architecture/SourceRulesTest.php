<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Tests\Support\SourceScan;

arch('controllers never reach for the DB facade')
    ->expect('App\Http\Controllers')
    ->not->toUse([DB::class]);

test('mass assignment ban: $request->all() never in app/ or routes/', function () {
    expect(SourceScan::findInProject(['\$request->all\(\)', 'request\(\)->all\(\)']))->toBe([]);
});

test('mass assignment scanner detects a violating snippet (negative fixture)', function () {
    $hits = SourceScan::scanString('<?php $u = User::create($request->all());', '\$request->all\(\)');
    expect($hits)->not->toBeEmpty();
});

test('hardened mass-assignment shortcuts banned: unguard + $guarded = []', function () {
    $hits = SourceScan::findInProject(['unguard\s*\(', '\$guarded\s*=\s*\[\s*\]', 'Model::preventSilentlyDiscardedAttributes'], ['app']);
    // third pattern intentionally lists the ALLOW-call as banned-in-app glue too; adjust as needed
    expect($hits)->toBe([]);
});

test('controllers contain no writes: ->save(, ::create(, new App\Models outside whitelisted constructs', function () {
    $hits = SourceScan::findInDirectory(
        'app/Http/Controllers',
        ['->save\(', '::create\(', '\bdatabase\(\)->', 'new\s+App\\\\Models\\\\']
    );
    expect($hits)->toBe([]);
});

test('actions never call mailers or http clients directly — dispatch events instead', function () {
    $hits = SourceScan::findInDirectory('app/Actions', [
        'Facades\\\\Mail::', '->notify\(', 'Facades\\\\Http::', 'Http::(get|post|put|delete)\(', '->send\(',
    ]);
    expect($hits)->toBe([]);
});

test('every *Action class lives in the App\Actions namespace', function () {
    $misplaced = [];
    foreach (SourceScan::projectPhpFiles(['app']) as $file) {
        $content = (string) file_get_contents($file);
        if (preg_match('/class\s+(\w*Action)\s/', $content) === 1 && ! str_contains($content, 'namespace App\Actions')) {
            $misplaced[] = $file;
        }
    }
    expect($misplaced)->toBe([]);
});

test('action placement scanner works (negative fixture)', function () {
    $content = "<?php namespace App\\Http; class MakeCoffeeAction {}";
    $isMisplaced = preg_match('/class\s+\w*Action\s/', $content) === 1 && ! str_contains($content, 'namespace App\Actions');
    expect($isMisplaced)->toBeTrue();
});

test('readonly-by-default: every Action and Middleware class is declared final readonly', function () {
    $offenders = [];

    foreach (['app/Actions', 'app/Http/Middleware'] as $dir) {
        foreach (SourceScan::projectPhpFiles([$dir]) as $file) {
            $content = (string) file_get_contents($file);

            if (preg_match('/^(final\s+)?(readonly\s+)?class/m', $content) === 1
                && preg_match('/^final readonly class/m', $content) !== 1) {
                $offenders[] = ltrim(str_replace(dirname(__DIR__, 2), '', $file), '/');
            }
        }
    }

    expect($offenders)->toBe([], 'docs/conventions.md: stateless classes must be `final readonly`; justify mutability via ADR + human approval');
});

test('readonly rule scanner works (positive + negative fixtures)', function () {
    $bad = "<?php\nnamespace App\\Actions;\nfinal class LooseAction {}";
    $good = "<?php\nnamespace App\\Actions;\nfinal readonly class TightAction {}";
    $hasClass = fn (string $c): bool => preg_match('/^(final\s+)?(readonly\s+)?class/m', $c) === 1;
    $isTight = fn (string $c): bool => preg_match('/^final readonly class/m', $c) === 1;

    expect($hasClass($bad))->toBeTrue();
    expect($isTight($bad))->toBeFalse();
    expect($isTight($good))->toBeTrue();
});

it('declares model attributes only via properties + casts() method', function () {
    $banned = [
        'protected $casts =' => 'use the casts(): array method (L11+ style)',
        '#\[Fillable' => 'use protected $fillable',
        '#\[Hidden' => 'use protected $hidden',
        'function get[A-Za-z]*Attribute\(' => 'use Attribute::make inside casts()',
        'function set[A-Za-z]*Attribute\(' => 'use Attribute::make inside casts()',
    ];

    foreach (glob(app_path('Models/*.php')) ?: [] as $file) {
        $src = (string) file_get_contents($file);

        foreach ($banned as $pattern => $hint) {
            expect($src)->not->toMatch('/'.$pattern.'/', basename($file).': '.$hint);
        }
    }
});

/* ── pattern templates — UNCOMMENT + adapt when the feature exists ────────── */

// Vendor SDK isolation (e.g. payments): confine imports to one adapter namespace.
// test('stripe and cashier imports are confined to app/Billing', function () {
//     $hits = SourceScan::findInProject(['^use Stripe\\\\', '^use Laravel\\\\Cashier\\\\'], ['app', 'routes'], ['app/Billing']);
//     expect($hits)->toBe([]);
// });

// Notification funnel: single dispatcher + delivery job allowed raw notify().
// it('funnels all notifications through the dispatcher', function () {
//     $allowed = ['app/Jobs/Notifications/DeliverNotification.php'];
//     ... see reference project (agent-first boilerplate) ...
// });

// Sole upload surface allow-list (finfo + re-encode policy).
// test('the logo is the ONLY upload surface', function () {
//     $hits = SourceScan::findInProject(['UploadedFile', '->file\('], ['app'], ['app/Actions/…UploadAction.php']);
//     expect($hits)->toBe([]);
// });
