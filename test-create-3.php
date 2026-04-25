<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = App\Models\User::role('admin')->first();
Illuminate\Support\Facades\Auth::login($admin);

try {
    echo "Calling authorize manually...\n";
    $comp = new App\Livewire\Project\Create();
    // we can't call authorize directly if it's protected, let's just test via Livewire
    Livewire\Livewire::test(App\Livewire\Project\Create::class);
    echo "Component mounted successfully.\n";
} catch (\Throwable $e) {
    echo "Exception Class: " . get_class($e) . "\n";
    echo "Exception: " . $e->getMessage() . "\n";
}
