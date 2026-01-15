# Stock Model Documentation

## Overview

The `Stock` model represents inventory management for products within specific dukans (shops) in the SmartBiz system. It tracks product quantities, batch information, expiry dates, and maintains relationships with dukans, products, and users. The model includes comprehensive stock status tracking, value calculations, and activity logging for audit purposes.

## Table Structure

**Table Name:** `stocks`

### Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `duka_id` | integer | Yes | Foreign key to the duka (shop) where stock is held |
| `product_id` | integer | Yes | Foreign key to the product being stocked |
| `quantity` | integer | Yes | Current stock quantity (must be >= 0) |
| `last_updated_by` | integer | Yes | Foreign key to the user who last updated the stock |
| `batch_number` | string | No | Optional batch/lot number for traceability |
| `expiry_date` | date | No | Optional expiry date for perishable items |
| `notes` | text | No | Optional additional notes about the stock |
| `created_at` | timestamp | Auto | Record creation timestamp |
| `updated_at` | timestamp | Auto | Record last update timestamp |
| `deleted_at` | timestamp | Auto | Soft delete timestamp (nullable) |

### Casts

- `quantity`: Cast to integer
- `duka_id`: Cast to integer
- `product_id`: Cast to integer
- `last_updated_by`: Cast to integer
- `expiry_date`: Cast to date
- `deleted_at`: Cast to datetime

## Relationships

### Belongs To

#### Duka Relationship
```php
public function duka(): BelongsTo
{
    return $this->belongsTo(Duka::class);
}
```
- **Related Model:** `Duka`
- **Foreign Key:** `duka_id`
- **Description:** Links stock to the specific duka/shop location

#### Product Relationship
```php
public function product(): BelongsTo
{
    return $this->belongsTo(Product::class);
}
```
- **Related Model:** `Product`
- **Foreign Key:** `product_id`
- **Description:** Links stock to the product details

#### Last Updated By Relationship
```php
public function lastUpdatedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'last_updated_by');
}
```
- **Related Model:** `User`
- **Foreign Key:** `last_updated_by`
- **Description:** Tracks which user last modified the stock record

### Has Many

#### Movements Relationship
```php
public function movements(): HasMany
{
    return $this->hasMany(StockMovement::class);
}
```
- **Related Model:** `StockMovement`
- **Foreign Key:** `stock_id` (in StockMovement table)
- **Description:** Tracks all stock movements (in/out) for audit and history

## Accessors

### Value Accessor
```php
public function getValueAttribute(): float
{
    return $this->quantity * ($this->product?->base_price ?? 0);
}
```
- **Description:** Calculates total value of current stock based on product base price
- **Return Type:** Float
- **Usage:** `$stock->value`

### Formatted Value Accessor
```php
public function getFormattedValueAttribute(): string
{
    return number_format($this->value) . ' TZS';
}
```
- **Description:** Returns formatted value with Tanzanian Shilling currency
- **Return Type:** String
- **Usage:** `$stock->formatted_value`

### Status Accessor
```php
public function getStatusAttribute(): string
{
    if ($this->quantity <= 0) return 'Out of Stock';
    if ($this->quantity < 10) return 'Low Stock';
    if ($this->quantity < 50) return 'Medium';
    return 'Good';
}
```
- **Description:** Determines stock status based on quantity thresholds
- **Return Type:** String
- **Possible Values:** 'Out of Stock', 'Low Stock', 'Medium', 'Good'
- **Usage:** `$stock->status`

### Status Badge Accessor
```php
public function getStatusBadgeAttribute(): string
{
    return match ($this->status) {
        'Out of Stock' => 'bg-danger',
        'Low Stock'    => 'bg-danger',
        'Medium'       => 'bg-warning',
        'Good'         => 'bg-success',
        default        => 'bg-secondary',
    };
}
```
- **Description:** Returns Bootstrap CSS class for status badge styling
- **Return Type:** String
- **Usage:** `$stock->status_badge`

## Scopes

### Low Stock Scope
```php
public function scopeLow($query, $limit = 10)
{
    return $query->where('quantity', '<=', $limit);
}
```
- **Description:** Filters stocks with quantity at or below the specified limit
- **Parameters:** `$limit` (integer, default: 10)
- **Usage:** `Stock::low(5)->get()`

### Out of Stock Scope
```php
public function scopeOutOfStock($query)
{
    return $query->where('quantity', 0);
}
```
- **Description:** Filters stocks with zero quantity
- **Usage:** `Stock::outOfStock()->get()`

## Activity Logging

The model uses Spatie Activity Log package for comprehensive audit tracking.

### Log Configuration
```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->useLogName('stock')
        ->logFillable()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->logOnly(['quantity', 'last_updated_by', 'batch_number', 'expiry_date'])
        ->setDescriptionForEvent(fn(string $eventName) =>
            "Stock {$eventName}: {$this->product?->name} (Qty: {$this->quantity})"
        );
}
```

### Logged Fields
- `quantity`
- `last_updated_by`
- `batch_number`
- `expiry_date`

### Activity Description Format
"Stock {event}: {Product Name} (Qty: {Quantity})"

### Events Logged
- Created
- Updated
- Deleted (soft delete)

## Traits Used

- `HasFactory`: Enables model factories for testing
- `SoftDeletes`: Allows soft deletion of stock records
- `LogsActivity`: Provides activity logging functionality

## Business Logic

### Stock Status Thresholds
- **Out of Stock:** quantity <= 0
- **Low Stock:** 0 < quantity < 10
- **Medium:** 10 <= quantity < 50
- **Good:** quantity >= 50

### Value Calculation
- Total value = quantity × product base price
- Handles null product relationships gracefully
- Returns 0 if product base_price is not set

### Soft Deletes
- Stock records are soft deleted to maintain historical data
- Relationships and movements remain intact
- Can be restored if needed

## Usage Examples

### Basic Queries
```php
// Get all stocks for a specific duka
$stocks = Stock::where('duka_id', 1)->get();

// Get low stock items
$lowStocks = Stock::low()->get();

// Get out of stock items
$outOfStock = Stock::outOfStock()->get();
```

### With Relationships
```php
// Get stock with product and duka details
$stock = Stock::with(['product', 'duka'])->find(1);

// Get stock movements
$movements = $stock->movements;
```

### Accessors Usage
```php
$stock = Stock::find(1);

$value = $stock->value; // 150000.00
$formattedValue = $stock->formatted_value; // "150,000 TZS"
$status = $stock->status; // "Good"
$badgeClass = $stock->status_badge; // "bg-success"
```

### Activity Log
```php
// Get recent stock activities
$activities = Activity::where('log_name', 'stock')
    ->where('subject_id', $stockId)
    ->latest()
    ->get();
```

## Validation Rules

When creating/updating stock records:

- `duka_id`: Required, must exist in dukans table
- `product_id`: Required, must exist in products table
- `quantity`: Required, integer, minimum 0
- `last_updated_by`: Required, must exist in users table
- `batch_number`: Optional string
- `expiry_date`: Optional date, must be future date if provided
- `notes`: Optional text

## Database Constraints

- Foreign key constraints on `duka_id`, `product_id`, `last_updated_by`
- Unique constraint consideration: One stock record per product per duka
- Soft delete support with `deleted_at` column

## Performance Considerations

- Index on `duka_id`, `product_id` for efficient queries
- Consider composite index on `(duka_id, product_id)` for uniqueness
- Activity logging may impact write performance on high-frequency updates

## Integration Points

- **Sales System:** Stock quantities automatically reduced on sales
- **Purchase System:** Stock quantities increased on purchases
- **Inventory Reports:** Used for stock level reporting and alerts
- **Product Management:** Linked to product catalog for pricing and details

## Error Handling

- Handles missing product relationships gracefully in accessors
- Validates foreign key existence before saving
- Activity logging failures don't prevent stock updates

## Testing

### Unit Tests
- Test accessor calculations
- Test scope filtering
- Test relationship loading
- Test activity logging

### Integration Tests
- Test stock updates through sales
- Test soft delete behavior
- Test foreign key constraints

## Changelog

### Version 1.0.0
- Initial implementation
- Basic CRUD operations
- Relationship definitions
- Accessor and scope implementations
- Activity logging integration
- Soft delete support

---

## Support

For model-related questions or modifications, please refer to the development team or main system documentation.
