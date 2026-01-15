# Stock Update API - Updated Implementation

## Overview
The `apiUpdateStock` method has been updated to automatically determine the `duka_id` from the authenticated officer's assignments, eliminating the need to pass `duka_id` in the request body.

## Changes Made

### 1. Request Validation
**Before:**
```php
$request->validate([
    'product_id'      => 'required|exists:products,id',
    'duka_id'         => 'required|exists:dukas,id', // ❌ Required
    'quantity_change' => 'required|integer',
    'operation'       => 'required|in:add,reduce,set',
    'reason'          => 'nullable|string|max:255',
]);
```

**After:**
```php
$request->validate([
    'product_id'      => 'required|exists:products,id',
    // 'duka_id'         => 'required|exists:dukas,id', // ✅ Removed
    'quantity_change' => 'required|integer',
    'operation'       => 'required|in:add,reduce,set',
    'reason'          => 'nullable|string|max:255',
]);
```

### 2. Duka ID Resolution
**Before:**
```php
// Get tenant ID from officer's assignments
$assignment = TenantOfficer::where('officer_id', $user->id)
    ->where('status', true)
    ->first();

$tenantId = $assignment->tenant_id;

// Verify duka belongs to officer
$officerDuka = TenantOfficer::where('tenant_id', $tenantId)
    ->where('officer_id', $user->id)
    ->where('duka_id', $request->duka_id) // ❌ From request
    ->where('status', true)
    ->exists();
```

**After:**
```php
// Get tenant ID and duka ID from officer's assignments
$assignment = TenantOfficer::where('officer_id', $user->id)
    ->where('status', true)
    ->first();

$tenantId = $assignment->tenant_id;
$dukaId   = $assignment->duka_id; // ✅ From officer assignment
```

### 3. Stock Record Management
**Before:**
```php
$stock = Stock::firstOrCreate(
    [
        'product_id' => $request->product_id,
        'duka_id'    => $request->duka_id, // ❌ From request
    ],
    // ...
);
```

**After:**
```php
$stock = Stock::firstOrCreate(
    [
        'product_id' => $request->product_id,
        'duka_id'    => $dukaId, // ✅ From officer assignment
    ],
    // ...
);
```

## API Usage

### Request Format
```json
POST /api/officer/stock
Headers: {
    "Authorization": "Bearer {token}",
    "Content-Type": "application/json"
}
Body: {
    "product_id": 123,
    "quantity_change": 10,
    "operation": "add",
    "reason": "Stock replenishment"
}
```

### Response Format
```json
{
    "success": true,
    "message": "Stock updated successfully",
    "data": {
        "stock": {
            "id": 456,
            "product_id": 123,
            "product_name": "Sample Product",
            "duka_id": 789,
            "duka_name": "Main Store",
            "previous_quantity": 5,
            "new_quantity": 15,
            "quantity_change": 10,
            "operation": "add",
            "updated_at": "2026-01-03T23:36:12.000000Z"
        }
    }
}
```

## Stock Movement Tracking
The method continues to create `StockMovement` records for audit purposes:

```php
StockMovement::create([
    'stock_id'          => $stock->id,
    'user_id'           => $user->id,
    'type'              => $movementType, // 'add' or 'remove'
    'quantity_change'   => $quantityChange, // Positive or negative
    'previous_quantity' => $previousQuantity,
    'new_quantity'      => $newQuantity,
    'reason'            => $request->reason ?: ucfirst($request->operation) . ' stock',
]);
```

## Security & Validation

1. **Officer Role Verification**: Only users with 'officer' role can access this endpoint
2. **Permission Checks**: 
   - `adding_stock` permission required for 'add' operations
   - `reduce_stock` permission required for 'reduce' operations
3. **Product Ownership**: Product must belong to the officer's tenant
4. **Duka Assignment**: Automatically uses the officer's assigned duka (no manual duka selection)

## Operations Supported

1. **Add Stock** (`operation: "add"`)
   - Increases stock quantity
   - Requires positive `quantity_change`
   - Creates 'add' type stock movement

2. **Reduce Stock** (`operation: "reduce"`)
   - Decreases stock quantity
   - Requires positive `quantity_change`
   - Cannot reduce below zero
   - Creates 'remove' type stock movement

3. **Set Stock** (`operation: "set"`)
   - Sets absolute stock quantity
   - Requires non-negative `quantity_change`
   - Creates 'add' or 'remove' movement based on difference

## Benefits

1. **Simplified API**: No need to specify duka_id in request
2. **Security**: Officers can only update stock in their assigned dukas
3. **Audit Trail**: Complete stock movement history maintained
4. **Permission Control**: Granular permission system for stock operations
5. **Data Integrity**: Automatic validation of officer-product-duka relationships
