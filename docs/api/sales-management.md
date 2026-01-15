# Sales Management API Documentation

## Overview

The Sales Management API provides endpoints for officers to manage and retrieve sales data. This documentation covers the `GET /officer/sales` endpoint which retrieves a list of sales with comprehensive filtering and pagination capabilities.

## Base URL

```
https://yourdomain.com/api/officer/sales
```

## Authentication

All sales management endpoints require authentication using Laravel Sanctum:

- **Header**: `Authorization: Bearer {token}`
- **Role**: Officer role required
- **Permissions**: Officer must have active assignments to tenants and dukas

## GET /officer/sales - Get Sales List

Retrieves a paginated list of sales with comprehensive filtering options.

### Request

**Method**: `GET`
**Endpoint**: `/api/officer/sales`
**Content-Type**: `application/json`

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `search` | string | Search by sale ID, customer name, phone, or email | `search=john` |
| `duka_id` | integer | Filter by specific duka ID | `duka_id=5` |
| `customer_id` | integer | Filter by customer ID | `customer_id=12` |
| `is_loan` | boolean | Filter by loan status (true/false) | `is_loan=true` |
| `payment_status` | string | Filter by payment status (Fully Paid, Partially Paid, Unpaid) | `payment_status=Fully Paid` |
| `date_from` | date | Filter sales from this date (YYYY-MM-DD) | `date_from=2023-01-01` |
| `date_to` | date | Filter sales up to this date (YYYY-MM-DD) | `date_to=2023-12-31` |
| `min_amount` | decimal | Filter sales with minimum total amount | `min_amount=100.00` |
| `max_amount` | decimal | Filter sales with maximum total amount | `max_amount=1000.00` |
| `per_page` | integer | Number of results per page (default: 15) | `per_page=25` |

### Example Request

```bash
curl -X GET "https://yourdomain.com/api/officer/sales?date_from=2023-01-01&date_to=2023-01-31&is_loan=false&per_page=20" \
     -H "Authorization: Bearer your_access_token_here" \
     -H "Accept: application/json"
```

### Response Structure

The response includes:

1. **Sales Data**: Array of sale objects with customer and duka information
2. **Pagination**: Pagination metadata
3. **Summary Statistics**: Aggregated sales statistics

### Response Fields

#### Sale Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Unique sale identifier |
| `tenant_id` | integer | Tenant ID associated with the sale |
| `duka_id` | integer | Duka (shop) ID where sale occurred |
| `duka_name` | string | Name of the duka |
| `customer_id` | integer | Customer ID (nullable) |
| `customer` | object | Customer details (name, phone, email) |
| `total_amount` | decimal | Total sale amount |
| `discount_amount` | decimal | Discount applied |
| `profit_loss` | decimal | Profit/loss calculation |
| `is_loan` | boolean | Whether sale is on credit |
| `due_date` | date | Due date for loan payments |
| `payment_status` | string | Payment status (Fully Paid, Partially Paid, Unpaid, N/A) |
| `total_payments` | decimal | Total payments made (for loans) |
| `remaining_balance` | decimal | Remaining balance (for loans) |
| `discount_reason` | string | Reason for discount |
| `item_count` | integer | Number of items in sale |
| `created_at` | datetime | Sale creation timestamp |
| `updated_at` | datetime | Last update timestamp |

#### Pagination Object

| Field | Type | Description |
|-------|------|-------------|
| `current_page` | integer | Current page number |
| `last_page` | integer | Total number of pages |
| `per_page` | integer | Results per page |
| `total` | integer | Total number of sales |
| `from` | integer | First item on current page |
| `to` | integer | Last item on current page |

#### Summary Object

| Field | Type | Description |
|-------|------|-------------|
| `total_sales` | integer | Total number of sales |
| `total_amount` | decimal | Sum of all sale amounts |
| `total_loans` | integer | Number of loan sales |
| `total_outstanding_balance` | decimal | Total outstanding balance for loans |
| `cash_sales_count` | integer | Number of cash sales |
| `loan_sales_count` | integer | Number of loan sales |

### Example Response

```json
{
    "success": true,
    "data": {
        "sales": [
            {
                "id": 123,
                "tenant_id": 1,
                "duka_id": 5,
                "duka_name": "Main Branch",
                "customer_id": 45,
                "customer": {
                    "id": 45,
                    "name": "John Doe",
                    "phone": "+254712345678",
                    "email": "john@example.com"
                },
                "total_amount": 1500.00,
                "discount_amount": 100.00,
                "profit_loss": 450.00,
                "is_loan": false,
                "due_date": null,
                "payment_status": "N/A",
                "total_payments": 0.00,
                "remaining_balance": 0.00,
                "discount_reason": "Bulk purchase discount",
                "item_count": 5,
                "created_at": "2023-01-15T10:30:45Z",
                "updated_at": "2023-01-15T10:30:45Z"
            },
            {
                "id": 124,
                "tenant_id": 1,
                "duka_id": 5,
                "duka_name": "Main Branch",
                "customer_id": 67,
                "customer": {
                    "id": 67,
                    "name": "Jane Smith",
                    "phone": "+254723456789",
                    "email": "jane@example.com"
                },
                "total_amount": 2500.00,
                "discount_amount": 0.00,
                "profit_loss": 800.00,
                "is_loan": true,
                "due_date": "2023-02-15",
                "payment_status": "Partially Paid",
                "total_payments": 1000.00,
                "remaining_balance": 1500.00,
                "discount_reason": null,
                "item_count": 3,
                "created_at": "2023-01-16T14:20:10Z",
                "updated_at": "2023-01-20T09:15:22Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 3,
            "per_page": 15,
            "total": 35,
            "from": 1,
            "to": 15
        },
        "summary": {
            "total_sales": 35,
            "total_amount": 42500.00,
            "total_loans": 12,
            "total_outstanding_balance": 18500.00,
            "cash_sales_count": 23,
            "loan_sales_count": 12
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

### 1. Get Recent Sales
```bash
GET /api/officer/sales?date_from=2023-01-01&date_to=2023-01-31
```

### 2. Get Loan Sales Only
```bash
GET /api/officer/sales?is_loan=true
```

### 3. Get High-Value Sales
```bash
GET /api/officer/sales?min_amount=1000
```

### 4. Search for Specific Customer Sales
```bash
GET /api/officer/sales?search=John
```

### 5. Get Sales for Specific Duka
```bash
GET /api/officer/sales?duka_id=5
```

## Integration Examples

### JavaScript (Axios)

```javascript
import axios from 'axios';

const getSales = async (params = {}) => {
    try {
        const response = await axios.get('/api/officer/sales', {
            params: {
                per_page: 20,
                ...params
            },
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        return response.data;
    } catch (error) {
        console.error('Error fetching sales:', error);
        throw error;
    }
};

// Usage
getSales({ date_from: '2023-01-01', is_loan: false })
    .then(data => console.log('Sales data:', data))
    .catch(error => console.error('Error:', error));
```

### PHP (cURL)

```php
<?php
$token = 'your_access_token_here';
$url = 'https://yourdomain.com/api/officer/sales?date_from=2023-01-01&per_page=25';

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
print_r($data);
?>
```

### Python (Requests)

```python
import requests

token = "your_access_token_here"
url = "https://yourdomain.com/api/officer/sales"

params = {
    "date_from": "2023-01-01",
    "date_to": "2023-01-31",
    "per_page": 30
}

headers = {
    "Authorization": f"Bearer {token}",
    "Accept": "application/json"
}

response = requests.get(url, params=params, headers=headers)
data = response.json()

print("Sales Data:", data)
```

## Best Practices

1. **Filtering**: Always use appropriate filters to reduce response size and improve performance
2. **Pagination**: Use reasonable `per_page` values (15-50) for better UI performance
3. **Caching**: Consider caching responses on the client side for frequently accessed data
4. **Error Handling**: Implement proper error handling for unauthorized access and validation errors
5. **Date Ranges**: When using date filters, ensure date formats are consistent (YYYY-MM-DD)

## Related Endpoints

- `GET /api/officer/sales/{id}` - Get detailed sale information
- `POST /api/officer/sales` - Create a new sale
- `GET /api/officer/sales/{id}/invoice` - Get invoice data for a sale

## Rate Limiting

The API implements rate limiting to prevent abuse:
- **Limit**: 100 requests per minute per user
- **Response Headers**:
  - `X-RateLimit-Limit`: Total allowed requests
  - `X-RateLimit-Remaining`: Remaining requests
  - `X-RateLimit-Reset`: Time when limit resets (timestamp)

## Versioning

This documentation covers API version 1.0. The API version is specified in the response headers:
- `API-Version: 1.0`

## Support

For issues or questions regarding the Sales Management API, contact:
- **Email**: support@smartbiz.com
- **Phone**: +254 700 123456
- **Documentation**: https://docs.smartbiz.com/api/sales
