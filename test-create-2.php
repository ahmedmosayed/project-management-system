<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = App\Models\User::role('admin')->first();
$pm = App\Models\User::role('project-manager')->first();

if (!$admin || !$pm) {
    echo "Missing users.\n";
    exit;
}

echo "Testing Admin:\n";
Illuminate\Support\Facades\Auth::login($admin);
echo "Can Admin create project? " . (auth()->user()->can('create', App\Models\Project::class) ? 'Yes' : 'No') . "\n";
try {
    $comp = Livewire\Livewire::test(App\Livewire\Project\Create::class);
    echo "Admin mounted component successfully! (THIS IS BAD)\n";
} catch (\Illuminate\Auth\Access\AuthorizationException $e) {
    echo "Admin got AuthorizationException! (THIS IS GOOD)\n";
}

echo "\nTesting PM:\n";
Illuminate\Support\Facades\Auth::login($pm);
echo "Can PM create project? " . (auth()->user()->can('create', App\Models\Project::class) ? 'Yes' : 'No') . "\n";
try {
    $comp = Livewire\Livewire::test(App\Livewire\Project\Create::class)
        ->set('name', 'Test PM Project')
        ->set('manager_id', $pm->id)
        ->set('status', 'planning')
        ->call('save');
    echo "PM created project successfully! Errors: " . json_encode($comp->errors()->toArray()) . "\n";
} catch (\Exception $e) {
    echo "PM Exception: " . $e->getMessage() . "\n";
}
