<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::role('admin')->first();
if (!$user) {
    echo "No admin user found.\n";
    exit;
}
Illuminate\Support\Facades\Auth::login($user);

$pm = App\Models\User::role('project-manager')->first();
if (!$pm) {
    echo "No project manager found.\n";
    exit;
}

try {
    $comp = Livewire\Livewire::test(App\Livewire\Project\Create::class)
        ->set('name', 'Test Admin Project')
        ->set('manager_id', $pm->id)
        ->set('status', 'planning')
        ->call('save');
    
    echo "Success! Errors: " . json_encode($comp->errors()->toArray()) . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
