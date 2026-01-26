# Payment Status History Column Fix

## Problem

The `payment_status_history` table was created with a column named `notes`, but the code was trying to insert into a column named `comment`, causing this error:

```
ERROR: column "comment" of relation "payment_status_history" does not exist
```

## Root Cause

- **Migration** creates column: `notes` (line 146 in `2024_01_01_000008_create_payments_tables.php`)
- **Model** had in fillable: `comment` ❌
- **Controllers** were using: `comment` ❌

## Fix Applied

### 1. Updated PaymentStatusHistory Model
**File:** `backend/app/Models/PaymentStatusHistory.php`
- Changed `comment` → `notes` in fillable array
- Added `changed_by_type` to fillable (was missing but exists in migration)

### 2. Updated PaymentController
**File:** `backend/app/Http/Controllers/Api/Admin/PaymentController.php`
- Changed `comment` → `notes` in `verify()` method (line 120)
- Changed `comment` → `notes` in `reject()` method (line 168)
- Added `changed_by_type` => 'admin' to both create statements

## Database Schema (Correct)

```php
Schema::create('payment_status_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payment_id')->constrained()->onDelete('cascade');
    $table->string('status', 50);
    $table->string('previous_status', 50)->nullable();
    $table->text('notes')->nullable();  // ✅ This is the correct column name
    $table->enum('changed_by_type', ['system', 'admin', 'customer', 'gateway'])->default('system');
    $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');
    $table->timestamps();
});
```

## Testing

After this fix, payment verification and rejection should work without errors:

1. **Verify Payment:**
   - Admin verifies a payment
   - Status history is created with `notes` field ✅

2. **Reject Payment:**
   - Admin rejects a payment
   - Status history is created with `notes` field ✅

## Status

✅ **Fixed** - The model and controllers now match the database schema.
