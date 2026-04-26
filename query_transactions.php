<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class);

$transactions = \App\Models\Transaction::all(['id', 'buyer_id', 'photographer_id', 'total_amount', 'status', 'created_at'])->toArray();
echo json_encode($transactions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
