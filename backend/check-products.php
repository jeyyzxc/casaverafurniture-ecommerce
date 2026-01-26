<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Product Status Check ===\n\n";

// Check total products
$total = \DB::table('products')->count();
echo "Total products in database: {$total}\n";

// Check by status
$statuses = \DB::table('products')
    ->select('status', \DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
    
echo "\nProducts by status:\n";
foreach ($statuses as $status) {
    echo "  - {$status->status}: {$status->count}\n";
}

// Check active products
$active = \DB::table('products')->where('status', 'active')->count();
echo "\nActive products (status='active'): {$active}\n";

// Check published products (active + published_at condition)
$published = \DB::table('products')
    ->where('status', 'active')
    ->where(function($q) {
        $q->whereNull('published_at')
          ->orWhere('published_at', '<=', now());
    })
    ->count();
echo "Published products (active + published_at): {$published}\n";

// Check published_at values
$publishedAtStatus = \DB::table('products')
    ->select(\DB::raw('
        COUNT(*) as total,
        SUM(CASE WHEN published_at IS NULL THEN 1 ELSE 0 END) as null_published,
        SUM(CASE WHEN published_at IS NOT NULL AND published_at <= NOW() THEN 1 ELSE 0 END) as published_past,
        SUM(CASE WHEN published_at IS NOT NULL AND published_at > NOW() THEN 1 ELSE 0 END) as published_future
    '))
    ->where('status', 'active')
    ->first();
    
echo "\nPublished_at breakdown for active products:\n";
echo "  - NULL published_at: {$publishedAtStatus->null_published}\n";
echo "  - Published in past: {$publishedAtStatus->published_past}\n";
echo "  - Published in future: {$publishedAtStatus->published_future}\n";

// Sample products
echo "\nSample products (first 5):\n";
$samples = \DB::table('products')
    ->select('id', 'name', 'status', 'published_at')
    ->limit(5)
    ->get();
    
foreach ($samples as $product) {
    $publishedAt = $product->published_at ? date('Y-m-d H:i:s', strtotime($product->published_at)) : 'NULL';
    echo "  - [{$product->id}] {$product->name} | status: {$product->status} | published_at: {$publishedAt}\n";
}

echo "\n=== Check Complete ===\n";
