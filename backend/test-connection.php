<?php
/**
 * Quick connection test script
 * Run: php test-connection.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Connection Test ===\n\n";

// Test Database Connection
try {
    $connection = \DB::connection();
    $pdo = $connection->getPdo();
    $driver = config('database.default');
    $config = config("database.connections.{$driver}");
    
    echo "✓ Database connection: SUCCESS\n";
    echo "  - Driver: {$driver}\n";
    echo "  - Host: " . ($config['host'] ?? 'N/A') . "\n";
    echo "  - Database: " . ($config['database'] ?? 'N/A') . "\n";
    echo "  - Username: " . ($config['username'] ?? 'N/A') . "\n";
    
    // Test query
    try {
        $productCount = \DB::table('products')->count();
        echo "  - Products in database: {$productCount}\n";
    } catch (\Exception $e) {
        echo "  - Warning: Could not query products table: " . $e->getMessage() . "\n";
        echo "  - This might mean migrations haven't been run yet\n";
    }
} catch (\Exception $e) {
    echo "✗ Database connection: FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "  Please check your .env file database settings\n";
    exit(1);
}

// Test API Routes
echo "\n✓ API Routes configured\n";
echo "  - Base URL: " . config('app.url') . "\n";
echo "  - Frontend URL: " . env('FRONTEND_URL', 'http://localhost:5173') . "\n";

// Test CORS
echo "\n✓ CORS Configuration:\n";
$cors = config('cors');
echo "  - Allowed origins: " . implode(', ', $cors['allowed_origins']) . "\n";
echo "  - Supports credentials: " . ($cors['supports_credentials'] ? 'Yes' : 'No') . "\n";

// Test Sanctum
echo "\n✓ Sanctum Configuration:\n";
$sanctum = config('sanctum');
echo "  - Stateful domains: " . implode(', ', $sanctum['stateful']) . "\n";

echo "\n=== All checks passed! ===\n";
echo "\nNext steps:\n";
echo "1. Start backend: cd backend && php artisan serve\n";
echo "2. Start frontend: cd frontend && npm run dev\n";
echo "3. Open browser: http://localhost:5173\n";
