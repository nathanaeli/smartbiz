# Stock Addition API Documentation

## Overview

The `apiAddStock` method allows authorized officers to add stock to products in their assigned shop (duka). This endpoint handles stock additions by updating existing stock quantities and recording all stock movements for audit purposes.

**Endpoint:** `POST /officer/stock`

**Controller:** `App\Http\Controllers\OfficerApiController::apiAddStock`

---

## Authentication & Authorization

### Required Role
- **Officer** - Only users with the 'officer' role can access this endpoint

### Required Permissions
- **adding_stock** - Officers must have this permission to add stock

### Security Features
- ✅ Automatic tenant isolation (officers can only modify stock for their assigned tenant)
- ✅ Duka restriction (stock additions are limited to officer's assigned duka)
- ✅ Product ownership validation
- ✅ Audit trail through stock movement records

---

## Request Parameters

### Headers
```http
Content-Type: application/json
Authorization: Bearer {your_access_token}
```

### Body Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| `product_id` | integer | ✅ | Must exist in products table | The ID of the product to add stock to |
| `quantity` | integer | ✅ | Minimum value: 1 | The amount of stock to add |
| `reason` | string | ❌ | Maximum length: 255 | Optional reason for the stock addition |

### Example Request

```json
{
    "product_id": 123,
    "quantity": 50,
    "reason": "Restocking from supplier"
}
```

---

## Response Format

### Success Response (200 OK)

```json
{
    "success": true,
    "message": "Stock added successfully",
    "data": {
        "stock": {
            "id": 456,
            "product_id": 123,
            "product_name": "Sample Product",
            "duka_id": 789,
            "duka_name": "Main Shop",
            "previous_quantity": 25,
            "added_quantity": 50,
            "new_quantity": 75,
            "updated_at": "2025-01-03T23:40:26.000Z"
        }
    }
}
```

### Error Responses

#### 401 Unauthorized
```json
{
    "error": "Unauthorized"
}
```
**Cause:** Missing or invalid authentication token

#### 403 Forbidden
```json
{
    "error": "Unauthorized"
}
```
**Cause:** User is not an officer or lacks required permissions

#### 422 Unprocessable Entity
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "product_id": ["The product_id field is required."],
        "quantity": ["The quantity must be at least 1."]
    }
}
```
**Cause:** Validation errors in request parameters

#### 404 Not Found
```json
{
    "error": "No active assignments found"
}
```
**Cause:** Officer has no active duka assignments

---

## Method Details

### Process Flow

1. **Authentication Check**
   ```php
   if (! $user->hasRole('officer')) {
       return response()->json(['error' => 'Unauthorized'], 403);
   }
   ```

2. **Request Validation**
   ```php
   $request->validate([
       'product_id' => 'required|exists:products,id',
       'quantity'   => 'required|integer|min:1',
       'reason'     => 'nullable|string|max:255',
   ]);
   ```

3. **Assignment Verification**
   ```php
   $assignment = TenantOfficer::where('officer_id', $user->id)
       ->where('status', true)
       ->first();
   ```

4. **Product Ownership Check**
   ```php
   $product = Product::where('id', $request->product_id)
       ->where('tenant_id', $tenantId)
       ->firstOrFail();
   ```

5. **Stock Record Management**
   ```php
   $stock = Stock::firstOrCreate(
       [
           'product_id' => $request->product_id,
           'duka_id'    => $dukaId,
       ],
       [
           'quantity'        => 0,
           'last_updated_by' => $user->id,
       ]
   );
   ```

6. **Stock Update**
   ```php
   $previousQuantity = $stock->quantity;
   $newQuantity = $previousQuantity + $request->quantity;

   $stock->update([
       'quantity'        => $newQuantity,
       'last_updated_by' => $user->id,
   ]);
   ```

7. **Movement Recording**
   ```php
   StockMovement::create([
       'stock_id'          => $stock->id,
       'user_id'           => $user->id,
       'type'              => 'add',
       'quantity_change'   => $request->quantity,
       'previous_quantity' => $previousQuantity,
       'new_quantity'      => $newQuantity,
       'reason'            => $request->reason ?: 'Stock addition',
   ]);
   ```

---

## Business Logic

### Stock Management
- **Automatic Creation**: If no stock record exists for the product-duka combination, a new stock record is created with quantity 0
- **Additive Updates**: Stock additions are always cumulative (additive)
- **Audit Trail**: Every stock change is recorded in the StockMovement table

### Security Model
- **Tenant Isolation**: Officers can only add stock to products belonging to their tenant
- **Duka Restriction**: Stock additions are limited to the officer's assigned duka
- **Permission Control**: Officers need the `adding_stock` permission (enforced by middleware)

### Data Integrity
- **Product Validation**: Ensures the product exists and belongs to the officer's tenant
- **Quantity Validation**: Prevents negative or zero stock additions
- **Transaction Safety**: Uses database transactions for data consistency

---

## Database Operations

### Tables Affected
1. **stocks** - Updates stock quantities
2. **stock_movements** - Records all stock movements for audit

### StockMovement Record
The method creates a detailed movement record with:
- `type`: 'add'
- `quantity_change`: Positive value representing the addition
- `previous_quantity`: Stock level before addition
- `new_quantity`: Stock level after addition
- `reason`: User-provided reason or default text

---

## Usage Examples

### Basic Stock Addition
```bash
curl -X POST https://your-api-domain.com/officer/stock \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {access_token}" \
  -d '{
    "product_id": 123,
    "quantity": 25,
    "reason": "New shipment received"
  }'
```

### Adding Stock to New Product
```bash
curl -X POST https://your-api-domain.com/officer/stock \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {access_token}" \
  -d '{
    "product_id": 456,
    "quantity": 100
  }'
```

### JavaScript/AJAX Example
```javascript
const addStock = async (productId, quantity, reason) => {
  try {
    const response = await fetch('/officer/stock', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${userToken}`
      },
      body: JSON.stringify({
        product_id: productId,
        quantity: quantity,
        reason: reason
      })
    });

    const data = await response.json();
    
    if (data.success) {
      console.log('Stock added successfully:', data.data.stock);
    } else {
      console.error('Error:', data.error);
    }
  } catch (error) {
    console.error('Network error:', error);
  }
};
```

---

## Error Handling

### Common Error Scenarios

1. **No Active Assignments**
   - **Scenario**: Officer has no active duka assignments
   - **Response**: 404 with "No active assignments found"
   - **Solution**: Contact administrator for proper duka assignment

2. **Invalid Product**
   - **Scenario**: Product doesn't exist or doesn't belong to officer's tenant
   - **Response**: 404 with product not found error
   - **Solution**: Verify product ID and tenant association

3. **Insufficient Permissions**
   - **Scenario**: Officer lacks `adding_stock` permission
   - **Response**: 403 with unauthorized error
   - **Solution**: Request permission from administrator

4. **Validation Errors**
   - **Scenario**: Invalid quantity or missing required fields
   - **Response**: 422 with detailed validation errors
   - **Solution**: Correct input data according to validation rules

---

## Related Endpoints

### Stock Management
- `GET /officer/stocks` - List all stocks
- `PUT /officer/stock` - Update stock (add/reduce/set)
- `GET /officer/stock/{id}/movements` - View stock movement history

### Product Management
- `GET /officer/products` - List products
- `GET /officer/products/{id}` - Get product details
- `POST /officer/products` - Create new product

### Permission Management
- `GET /officer/permissions` - Get officer permissions

---

## Best Practices

### For API Consumers
1. **Always validate quantities** before sending requests
2. **Handle all error responses** appropriately
3. **Provide meaningful reasons** for stock additions
4. **Check response data** to confirm successful updates
5. **Implement retry logic** for network failures

### For Developers
1. **Log stock movements** for audit purposes
2. **Implement rate limiting** to prevent abuse
3. **Monitor stock levels** for low stock alerts
4. **Validate business rules** (e.g., maximum stock limits)
5. **Consider bulk operations** for large stock additions

---

## Changelog

### Version 1.0 (Current)
- Initial implementation
- Basic stock addition functionality
- Security and validation features
- Comprehensive error handling
- Audit trail through stock movements

---

## Support & Contact

For technical support or questions about this API endpoint:
- **Documentation Issues**: Refer to this documentation
- **Bug Reports**: Contact the development team
- **Feature Requests**: Submit through the appropriate channel
- **General Questions**: Check the FAQ or contact support

---

*Last Updated: January 3, 2025*
*API Version: 1.0*
*Controller: App\Http\Controllers\OfficerApiController*
