<?php
require __DIR__ . '/bootstrap/app.php';
\ = require_once __DIR__ . '/bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

\ = \App\Models\User::all();
echo "\n=== USER LIST ===\n";
echo str_pad("NAME", 20) . " | " . str_pad("ROLE", 15) . " | " . "EMAIL\n";
echo str_repeat("-", 60) . "\n";
foreach(\ as \) {
    echo str_pad(\->name, 20) . " | " . str_pad(\->role, 15) . " | " . \->email . "\n";
}
