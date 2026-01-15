# Sales with Items and Products API Documentation

## Overview

The Sales with Items and Products API provides a comprehensive endpoint for retrieving detailed sales data including all related items, products, categories, and financial information. This endpoint is designed for advanced reporting, analytics, and detailed sales management.

## Base URL

```
https://yourdomain.com/api/officer/sales-with-items
```

## Authentication

All sales management endpoints require authentication using Laravel Sanctum:

- **Header**: `Authorization: Bearer {token}`
- **Role**: Officer role required
- **Permissions**: Officer must have active assignments to tenants and dukas

## GET /officer/sales-with-items - Get Sales with Comprehensive Items and Products Data

Retrieves a comprehensive list of sales with detailed items, products, categories, and financial information.

### Request

**Method**: `GET`
**Endpoint**: `/api/officer/sales-with-items`
**Content-Type**: `application/json`

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `search` | string | Search by sale ID, customer name/phone/email, or product name/SKU | `search=john` |
| `duka_id` | integer | Filter by specific duka ID | `duka_id=5` |
| `customer_id` | integer | Filter by customer ID | `customer_id=12` |
| `product_id` | integer | Filter by product ID | `product_id=8` |
| `category_id` | integer | Filter by product category ID | `category_id=3` |
| `is_loan` | boolean | Filter by loan status (true/false) | `is_loan=true` |
| `payment_status` | string | Filter by payment status (Fully Paid, Partially Paid, Unpaid) | `payment_status=Fully Paid` |
| `date_from` | date | Filter sales from this date (YYYY-MM-DD) | `date_from=2023-01-01` |
| `date_to` | date | Filter sales up to this date (YYYY-MM-DD) | `date_to=2023-12-31` |
| `min_amount` | decimal | Filter sales with minimum total amount | `min_amount=100.00` |
| `max_amount` | decimal | Filter sales with maximum total amount | `max_amount=1000.00` |

### Example Request

```bash
curl -X GET "https://yourdomain.com/api/officer/sales-with-items?date_from=2023-01-01&date_to=2023-01-31&category_id=3" \
     -H "Authorization: Bearer your_access_token_here" \
     -H "Accept: application/json"
```

### Response Structure

The response includes:

1. **Sales Data**: Array of comprehensive sale objects with detailed items, products, and related information
2. **Comprehensive Statistics**: Advanced analytics and statistics across all sales
3. **Metadata**: Request metadata including timestamps and API version

### Response Fields

#### Sale Object

| Field | Type | Description |
|-------|------|-------------|
| `sale_id` | integer | Unique sale identifier |
| `invoice_number` | string | Formatted invoice number |
| `sale_date` | datetime | Sale creation timestamp |
| `tenant` | object | Tenant information |
| `duka` | object | Duka (shop) details |
| `customer` | object | Customer information |
| `items` | array | Detailed sale items with full product information |
| `sale_summary` | object | Sale summary statistics |
| `financial_summary` | object | Financial analysis and metrics |
| `loan_information` | object | Loan details and payment history |
| `created_at` | datetime | Sale creation timestamp |
| `updated_at` | datetime | Last update timestamp |

#### Duka Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Duka identifier |
| `name` | string | Duka name |
| `location` | string | Duka location |
| `phone` | string | Duka phone number |

#### Customer Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Customer identifier |
| `name` | string | Customer name |
| `phone` | string | Customer phone |
| `email` | string | Customer email |
| `address` | string | Customer address |
| `status` | string | Customer status |

#### Sale Item Object

| Field | Type | Description |
|-------|------|-------------|
| `sale_item_id` | integer | Sale item identifier |
| `product_id` | integer | Product identifier |
| `product_name` | string | Product name |
| `product_sku` | string | Product SKU |
| `product_description` | string | Product description |
| `category` | object | Product category details |
| `unit` | string | Unit of measurement |
| `base_price` | decimal | Product base price |
| `selling_price` | decimal | Product selling price |
| `profit_margin` | decimal | Profit margin percentage |
| `quantity` | integer | Quantity sold |
| `unit_price` | decimal | Unit price at sale |
| `discount_amount` | decimal | Discount amount |
| `item_total` | decimal | Item total price |
| `product_item` | object | Individual product item details |
| `product_image` | string | Product image URL |
| `product_barcode` | string | Product barcode |
| `product_is_active` | boolean | Product active status |

#### Category Object (within items)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Category identifier |
| `name` | string | Category name |
| `description` | string | Category description |
| `status` | string | Category status |

#### Product Item Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Product item identifier |
| `qr_code` | string | QR code |
| `status` | string | Item status |
| `sold_at` | datetime | Date when item was sold |

#### Sale Summary Object

| Field | Type | Description |
|-------|------|-------------|
| `item_count` | integer | Number of items in sale |
| `total_quantity` | integer | Total quantity sold |
| `unique_products` | integer | Number of unique products |
| `unique_categories` | integer | Number of unique categories |
| `average_quantity_per_item` | decimal | Average quantity per item |

#### Financial Summary Object

| Field | Type | Description |
|-------|------|-------------|
| `subtotal` | decimal | Subtotal before discounts |
| `discount_amount` | decimal | Total discount amount |
| `discount_reason` | string | Reason for discount |
| `total_amount` | decimal | Final total amount |
| `profit_loss` | decimal | Profit/loss calculation |
| `average_item_price` | decimal | Average price per item |
| `profit_margin_percentage` | decimal | Overall profit margin percentage |

#### Loan Information Object

| Field | Type | Description |
|-------|------|-------------|
| `is_loan` | boolean | Whether sale is on credit |
| `due_date` | date | Due date for loan |
| `payment_status` | string | Payment status |
| `total_payments` | decimal | Total payments received |
| `remaining_balance` | decimal | Remaining balance |
| `payments` | array | Array of payment objects |
| `payment_history` | object | Payment history statistics |

#### Payment Object

| Field | Type | Description |
|-------|------|-------------|
| `payment_id` | integer | Payment identifier |
| `amount` | decimal | Payment amount |
| `payment_method` | string | Payment method |
| `payment_date` | datetime | Payment date |
| `notes` | string | Payment notes |
| `recorded_by` | object | User who recorded payment |
| `created_at` | datetime | Payment creation timestamp |

#### Comprehensive Statistics Object

| Section | Fields | Description |
|---------|--------|-------------|
| `sales_overview` | `total_sales`, `total_amount`, `total_discounts`, `total_profit`, `average_sale_amount`, `average_items_per_sale`, `average_quantity_per_sale` | Overall sales metrics |
| `product_analysis` | `total_items_sold`, `total_quantity_sold`, `unique_products_sold`, `unique_categories_sold` | Product performance analysis |
| `loan_analysis` | `total_loans`, `total_outstanding_balance`, `total_payments_received`, `loan_to_cash_ratio` | Loan performance metrics |
| `sales_type_breakdown` | `cash_sales_count`, `loan_sales_count`, `cash_sales_percentage`, `loan_sales_percentage` | Sales type distribution |
| `customer_analysis` | `unique_customers`, `sales_with_customers`, `sales_without_customers` | Customer engagement metrics |

### Example Response

```json
{
    "success": true,
    "data": {
        "sales": [
            {
                "sale_id": 123,
                "invoice_number": "INV-000123",
                "sale_date": "2023-01-15T10:30:45Z",
                "tenant": {
                    "id": 1
                },
                "duka": {
                    "id": 5,
                    "name": "Main Branch",
                    "location": "Nairobi CBD",
                    "phone": "+254712000000"
                },
                "customer": {
                    "id": 45,
                    "name": "John Doe",
                    "phone": "+254712345678",
                    "email": "john@example.com",
                    "address": "123 Main St, Nairobi",
                    "status": "active"
                },
                "items": [
                    {
                        "sale_item_id": 456,
                        "product_id": 78,
                        "product_name": "Premium Coffee",
                        "product_sku": "COF-PRM-500",
                        "product_description": "500g Premium Arabica Coffee",
                        "category": {
                            "id": 3,
                            "name": "Beverages",
                            "description": "Hot and cold beverages",
                            "status": "active"
                        },
                        "unit": "pcs",
                        "base_price": 800.00,
                        "selling_price": 1200.00,
                        "profit_margin": 50.00,
                        "quantity": 2,
                        "unit_price": 1200.00,
                        "discount_amount": 100.00,
                        "item_total": 2300.00,
                        "product_item": {
                            "id": 987,
                            "qr_code": "QR-2023-00987",
                            "status": "sold",
                            "sold_at": "2023-01-15T10:30:45Z"
                        },
                        "product_image": "https://storage.com/products/coffee.jpg",
                        "product_barcode": "123456789012",
                        "product_is_active": true
                    },
                    {
                        "sale_item_id": 457,
                        "product_id": 82,
                        "product_name": "Stainless Steel Mug",
                        "product_sku": "MUG-SS-350",
                        "product_description": "350ml Stainless Steel Travel Mug",
                        "category": {
                            "id": 4,
                            "name": "Accessories",
                            "description": "Coffee accessories",
                            "status": "active"
                        },
                        "unit": "pcs",
                        "base_price": 450.00,
                        "selling_price": 750.00,
                        "profit_margin": 66.67,
                        "quantity": 1,
                        "unit_price": 750.00,
                        "discount_amount": 0.00,
                        "item_total": 750.00,
                        "product_item": {
                            "id": 988,
                            "qr_code": "QR-2023-00988",
                            "status": "sold",
                            "sold_at": "2023-01-15T10:30:45Z"
                        },
                        "product_image": "https://storage.com/products/mug.jpg",
                        "product_barcode": "234567890123",
                        "product_is_active": true
                    }
                ],
                "sale_summary": {
                    "item_count": 2,
                    "total_quantity": 3,
                    "unique_products": 2,
                    "unique_categories": 2,
                    "average_quantity_per_item": 1.50
                },
                "financial_summary": {
                    "subtotal": 3100.00,
                    "discount_amount": 100.00,
                    "discount_reason": "Bulk purchase discount",
                    "total_amount": 3000.00,
                    "profit_loss": 1250.00,
                    "average_item_price": 1500.00,
                    "profit_margin_percentage": 41.67
                },
                "loan_information": {
                    "is_loan": false,
                    "due_date": null,
                    "payment_status": "N/A",
                    "total_payments": 0.00,
                    "remaining_balance": 0.00,
                    "payments": [],
                    "payment_history": {
                        "total_payments_count": 0,
                        "first_payment_date": null,
                        "last_payment_date": null
                    }
                },
                "created_at": "2023-01-15T10:30:45Z",
                "updated_at": "2023-01-15T10:30:45Z"
            }
        ],
        "comprehensive_statistics": {
            "sales_overview": {
                "total_sales": 35,
                "total_amount": 125000.00,
                "total_discounts": 8500.00,
                "total_profit": 45000.00,
                "average_sale_amount": 3571.43,
                "average_items_per_sale": 3.25,
                "average_quantity_per_sale": 5.80
            },
            "product_analysis": {
                "total_items_sold": 114,
                "total_quantity_sold": 203,
                "unique_products_sold": 42,
                "unique_categories_sold": 8
            },
            "loan_analysis": {
                "total_loans": 12,
                "total_outstanding_balance": 28500.00,
                "total_payments_received": 15000.00,
                "loan_to_cash_ratio": 34.29
            },
            "sales_type_breakdown": {
                "cash_sales_count": 23,
                "loan_sales_count": 12,
                "cash_sales_percentage": 65.71,
                "loan_sales_percentage": 34.29
            },
            "customer_analysis": {
                "unique_customers": 28,
                "sales_with_customers": 30,
                "sales_without_customers": 5
            }
        },
        "metadata": {
            "timestamp": "2023-01-15T10:45:22Z",
            "timezone": "Africa/Nairobi",
            "api_version": "1.0"
        }
    }
}
```

### Error Responses

**403 Unauthorized**
```json
{
    "error": "Unauthorized"
}
```

**403 No Active Assignments**
```json
{
    "error": "No active assignments found"
}
```

## Use Cases

### 1. Get Sales by Product Category
```bash
GET /api/officer/sales-with-items?category_id=3
```

### 2. Get Sales for Specific Product
```bash
GET /api/officer/sales-with-items?product_id=78
```

### 3. Get Sales with Detailed Product Information
```bash
GET /api/officer/sales-with-items?date_from=2023-01-01&date_to=2023-01-31
```

### 4. Search Across Multiple Fields
```bash
GET /api/officer/sales-with-items?search=coffee
```

### 5. Get Loan Sales with Payment History
```bash
GET /api/officer/sales-with-items?is_loan=true
```

## Integration Examples

### JavaScript (Axios)

```javascript
import axios from 'axios';

const getSalesWithItems = async (params = {}) => {
    try {
        const response = await axios.get('/api/officer/sales-with-items', {
            params: {
                ...params
            },
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        return response.data;
    } catch (error) {
        console.error('Error fetching sales with items:', error);
        throw error;
    }
};

// Usage with category filter
getSalesWithItems({ category_id: 3, date_from: '2023-01-01' })
    .then(data => {
        console.log('Comprehensive sales data:', data);
        // Process comprehensive statistics
        const stats = data.data.comprehensive_statistics;
        console.log('Total sales:', stats.sales_overview.total_sales);
        console.log('Total profit:', stats.sales_overview.total_profit);
    })
    .catch(error => console.error('Error:', error));
```

### PHP (cURL)

```php
<?php
$token = 'your_access_token_here';
$url = 'https://yourdomain.com/api/officer/sales-with-items?category_id=3&date_from=2023-01-01';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// Process the comprehensive data
if ($data['success']) {
    $sales = $data['data']['sales'];
    $stats = $data['data']['comprehensive_statistics'];

    echo "Total Sales: " . $stats['sales_overview']['total_sales'] . "\n";
    echo "Total Amount: " . $stats['sales_overview']['total_amount'] . "\n";
    echo "Unique Products: " . $stats['product_analysis']['unique_products_sold'] . "\n";

    // Process individual sales
    foreach ($sales as $sale) {
        echo "Sale #" . $sale['sale_id'] . " - " . $sale['sale_summary']['item_count'] . " items\n";

        foreach ($sale['items'] as $item) {
            echo "  - " . $item['product_name'] . " (x" . $item['quantity'] . ") - KES " . $item['item_total'] . "\n";
        }
    }
}
?>
```

### Python (Requests)

```python
import requests

token = "your_access_token_here"
url = "https://yourdomain.com/api/officer/sales-with-items"

params = {
    "product_id": 78,
    "date_from": "2023-01-01",
    "date_to": "2023-01-31"
}

headers = {
    "Authorization": f"Bearer {token}",
    "Accept": "application/json"
}

response = requests.get(url, params=params, headers=headers)
data = response.json()

if data['success']:
    sales = data['data']['sales']
    stats = data['data']['comprehensive_statistics']

    print(f"Retrieved {len(sales)} sales with {stats['product_analysis']['total_items_sold']} items")
    print(f"Total revenue: KES {stats['sales_overview']['total_amount']}")
    print(f"Total profit: KES {stats['sales_overview']['total_profit']}")

    # Analyze product performance
    product_sales = {}
    for sale in sales:
        for item in sale['items']:
            product_name = item['product_name']
            if product_name not in product_sales:
                product_sales[product_name] = {
                    'quantity': 0,
                    'revenue': 0,
                    'profit': 0
                }
            product_sales[product_name]['quantity'] += item['quantity']
            product_sales[product_name]['revenue'] += item['item_total']
            product_sales[product_name]['profit'] += (item['selling_price'] - item['base_price']) * item['quantity']

    print("\nTop Products:")
    for product, metrics in sorted(product_sales.items(), key=lambda x: x[1]['revenue'], reverse=True)[:5]:
        print(f"  {product}: {metrics['quantity']} units, KES {metrics['revenue']} revenue, KES {metrics['profit']} profit")
```

## Best Practices

1. **Filtering**: Use specific filters to reduce response size and improve performance
2. **Data Analysis**: Leverage the comprehensive statistics for business insights
3. **Caching**: Consider caching responses for frequently accessed reports
4. **Pagination**: For large datasets, consider implementing client-side pagination
5. **Error Handling**: Implement proper error handling for unauthorized access and validation errors
6. **Data Processing**: Process the comprehensive data on the client side for advanced analytics

## Advanced Use Cases

### 1. Product Performance Analysis
```javascript
// Analyze which products sell best together
const productPairs = {};

data.data.sales.forEach(sale => {
    const productIds = sale.items.map(item => item.product_id);
    productIds.forEach((id1, i) => {
        productIds.slice(i + 1).forEach(id2 => {
            const pairKey = [id1, id2].sort().join('-');
            productPairs[pairKey] = (productPairs[pairKey] || 0) + 1;
        });
    });
});

const topPairs = Object.entries(productPairs)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 5);

console.log('Top product pairs:', topPairs);
```

### 2. Customer Purchase Patterns
```javascript
// Analyze customer purchase patterns
const customerPatterns = {};

data.data.sales.forEach(sale => {
    if (sale.customer) {
        const customerId = sale.customer.id;
        if (!customerPatterns[customerId]) {
            customerPatterns[customerId] = {
                totalSpent: 0,
                totalVisits: 0,
                categories: {},
                lastPurchase: null
            };
        }

        customerPatterns[customerId].totalSpent += sale.financial_summary.total_amount;
        customerPatterns[customerId].totalVisits += 1;
        customerPatterns[customerId].lastPurchase = sale.sale_date;

        sale.items.forEach(item => {
            const categoryName = item.category ? item.category.name : 'Unknown';
            customerPatterns[customerId].categories[categoryName] =
                (customerPatterns[customerId].categories[categoryName] || 0) + item.item_total;
        });
    }
});

// Find high-value customers
const highValueCustomers = Object.entries(customerPatterns)
    .filter(([id, pattern]) => pattern.totalSpent > 10000)
    .sort((a, b) => b[1].totalSpent - a[1].totalSpent);

console.log('High-value customers:', highValueCustomers);
```

### 3. Profit Margin Analysis
```javascript
// Analyze profit margins by category
const categoryProfits = {};

data.data.sales.forEach(sale => {
    sale.items.forEach(item => {
        const categoryName = item.category ? item.category.name : 'Unknown';
        const profit = (item.selling_price - item.base_price) * item.quantity;

        if (!categoryProfits[categoryName]) {
            categoryProfits[categoryName] = {
                totalRevenue: 0,
                totalProfit: 0,
                totalQuantity: 0
            };
        }

        categoryProfits[categoryName].totalRevenue += item.item_total;
        categoryProfits[categoryName].totalProfit += profit;
        categoryProfits[categoryName].totalQuantity += item.quantity;
    });
});

// Calculate margins
Object.entries(categoryProfits).forEach(([category, metrics]) => {
    metrics.marginPercentage = (metrics.totalProfit / metrics.totalRevenue) * 100;
    metrics.averageMarginPerUnit = metrics.totalProfit / metrics.totalQuantity;
});

console.log('Category profit analysis:', categoryProfits);
```

## Related Endpoints

- `GET /api/officer/sales` - Get basic sales list with pagination
- `GET /api/officer/sales/{id}` - Get detailed sale information
- `POST /api/officer/sales` - Create a new sale
- `GET /api/officer/sales/{id}/invoice` - Get invoice data for a sale
- `GET /api/officer/invoices` - Get comprehensive invoice data

## Rate Limiting

The API implements rate limiting to prevent abuse:
- **Limit**: 60 requests per minute per user
- **Response Headers**:
  - `X-RateLimit-Limit`: Total allowed requests
  - `X-RateLimit-Remaining`: Remaining requests
  - `X-RateLimit-Reset`: Time when limit resets (timestamp)

## Versioning

This documentation covers API version 1.0. The API version is specified in the response headers:
- `API-Version: 1.0`

## Support

For issues or questions regarding the Sales with Items and Products API, contact:
- **Email**: support@smartbiz.com
- **Phone**: +254 700 123456
- **Documentation**: https://docs.smartbiz.com/api/sales-with-items
