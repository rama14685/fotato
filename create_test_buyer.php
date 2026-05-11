<?php
/**
 * Quick test script to create a buyer user + register their face in user_faces.
 * Run from: php create_test_buyer.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\UserFace;
use Illuminate\Support\Facades\Hash;

// Create test buyer user (or update if exists)
$user = User::updateOrCreate(
    ['email' => 'buyer@fotlist.test'],
    [
        'name'     => 'Test Buyer',
        'password' => Hash::make('password'),
        'role'     => 'customer',
        'status'   => 'active',
    ]
);

// Fake 128-d descriptor (all zeros except first element)
$fakeDescriptor = array_fill(0, 128, 0.01);
$fakeDescriptor[0] = 0.5;
$fakeDescriptor[1] = -0.3;
$fakeDescriptor[2] = 0.8;

UserFace::updateOrCreate(
    ['user_id' => $user->id],
    ['face_descriptor' => $fakeDescriptor]
);

echo "✅ Test buyer created:\n";
echo "   Email:    buyer@fotlist.test\n";
echo "   Password: password\n";
echo "   Role:     customer\n";
echo "   UserFace: registered (" . count($fakeDescriptor) . " dimensions)\n";
echo "\nYou can now login at http://localhost:8000/login\n";
