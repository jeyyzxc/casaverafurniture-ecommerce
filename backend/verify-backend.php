<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Backend Verification ===\n\n";

echo "1. Testing database connection...\n";
try {
    DB::connection()->getPdo();
    echo "   ✅ Database connection: SUCCESS\n";
    echo "   Database: " . config('database.connections.mysql.database') . "\n";
    echo "   Host: " . config('database.connections.mysql.host') . "\n";
} catch (\Exception $e) {
    echo "   ❌ Database connection: FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Checking database tables...\n";
try {
    $tables = ['products', 'categories', 'admins', 'users', 'orders'];
    foreach ($tables as $table) {
        $count = DB::table($table)->count();
        echo "   ✅ Table '{$table}': {$count} records\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Table check: FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. Checking API routes...\n";
try {
    $routes = Route::getRoutes();
    $apiRoutes = collect($routes)->filter(function ($route) {
        return str_starts_with($route->uri(), 'api/');
    })->count();
    echo "   ✅ API routes registered: {$apiRoutes} routes\n";
} catch (\Exception $e) {
    echo "   ❌ Route check: FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n4. Checking environment...\n";
echo "   APP_ENV: " . config('app.env') . "\n";
echo "   APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";
echo "   APP_URL: " . config('app.url') . "\n";

echo "\n=== Verification Complete ===\n";
echo "✅ Backend is ready!\n";
echo "\nTo start the server, run:\n";
echo "   php artisan serve\n";
echo "\nThe server will be available at: http://localhost:8000\n";
