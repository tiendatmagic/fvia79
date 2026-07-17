<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$id = '019f7172-171a-7351-9a33-230fabff0935';
$deposit = App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)->first();
echo json_encode($deposit, JSON_PRETTY_PRINT);
echo "\n\n--- ALL DEPOSITS ---\n";
$all = App\Infrastructure\Persistence\Eloquent\DepositModel::all();
foreach ($all as $d) {
    echo $d->id . ' | ' . $d->code . ' | ' . $d->status . ' | ' . $d->created_at . "\n";
}
