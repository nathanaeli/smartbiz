# Loan Payments API Documentation

This document provides comprehensive documentation for the Loan Payments API endpoints.

## Base URL

```
http://127.0.0.1:8000/api
```

## Authentication

All endpoints require authentication using Laravel Sanctum. Include the following header in your requests:

```
Authorization: Bearer {your_access_token}
Content-Type: application/json
```

## Authentication

All endpoints require authentication using Laravel Sanctum. Include the following header in your requests:

```
Authorization: Bearer {your_access_token}
Content-Type: application/json
```

## Endpoints Overview

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/loan-payments` | List all loan payments |
| POST | `/api/loan-payments` | Create a new loan payment |
| GET | `/api/loan-payments/{id}` | Get specific loan payment |
| PUT | `/api/loan-payments/{id}` | Update loan payment |
| DELETE | `/api/loan-payments/{id}` | Delete loan payment |
| GET | `/api/loan-payments/statistics` | Get payment statistics |
| GET | `/api/loan-payments/sale/{saleId}` | Get payments for specific sale |

## Detailed Endpoints

### 1. List All Loan Payments

**Endpoint:** `GET /api/loan-payments`

**Query Parameters:**
- `sale_id` (optional): Filter by sale ID
- `from_date` (optional): Filter payments from date (YYYY-MM-DD)
- `to_date` (optional): Filter payments to date (YYYY-MM-DD)
- `user_id` (optional): Filter by officer who made the payment
- `min_amount` (optional): Filter by minimum amount
- `max_amount` (optional): Filter by maximum amount
- `sort_by` (optional): Sort field (default: payment_date)
- `sort_order` (optional): Sort order (asc/desc, default: desc)
- `per_page` (optional): Results per page (default: 15)

**Example Request:**
```bash
curl -X GET "http://127.0.0.1:8000/api/loan-payments?from_date=2024-01-01&per_page=20" \
  -H "Authorization: Bearer {your_token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Loan payments retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "sale_id": 5,
        "amount": 1500.00,
        "payment_date": "2024-12-15",
        "notes": "Partial payment for December",
        "user_id": 3,
        "sale": {
          "customer": {
            "name": "John Doe"
          }
        },
        "user": {
          "name": "Jane Officer"
        },
        "created_at": "2024-12-15T10:30:00.000000Z",
        "updated_at": "2024-12-15T10:30:00.000000Z"
      }
    ],
    "first_page_url": "http://127.0.0.1:8000/api/loan-payments?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "http://127.0.0.1:8000/api/loan-payments?page=3",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      }
    ],
    "next_page_url": "http://127.0.0.1:8000/api/loan-payments?page=2",
    "path": "http://127.0.0.1:8000/api/loan-payments",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 45
  }
}
```

### 2. Create New Loan Payment

**Endpoint:** `POST /api/loan-payments`

**Request Body:**
```json
{
  "sale_id": 5,
  "amount": 1500.00,
  "payment_date": "2024-12-15",
  "notes": "Partial payment for December"
}
```

**Validation Rules:**
- `sale_id` (required): Must exist in sales table
- `amount` (required): Must be numeric, minimum 0.01
- `payment_date` (required): Must be valid date
- `notes` (optional): String, maximum 1000 characters

**Example Request:**
```bash
curl -X POST "http://127.0.0.1:8000/api/loan-payments" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "sale_id": 5,
    "amount": 1500.00,
    "payment_date": "2024-12-15",
    "notes": "Partial payment for December"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Loan payment created successfully",
  "data": {
    "id": 15,
    "sale_id": 5,
    "amount": 1500.00,
    "payment_date": "2024-12-15",
    "notes": "Partial payment for December",
    "user_id": 3,
    "sale": {
      "customer": {
        "name": "John Doe"
      }
    },
    "user": {
      "name": "Jane Officer"
    },
    "created_at": "2024-12-15T10:30:00.000000Z",
    "updated_at": "2024-12-15T10:30:00.000000Z"
  }
}
```

### 3. Get Specific Loan Payment

**Endpoint:** `GET /api/loan-payments/{id}`

**Example Request:**
```bash
curl -X GET "http://127.0.0.1:8000/api/loan-payments/15" \
  -H "Authorization: Bearer {your_token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Loan payment retrieved successfully",
  "data": {
    "id": 15,
    "sale_id": 5,
    "amount": 1500.00,
    "payment_date": "2024-12-15",
    "notes": "Partial payment for December",
    "user_id": 3,
    "sale": {
      "customer": {
        "name": "John Doe"
      }
    },
    "user": {
      "name": "Jane Officer"
    },
    "created_at": "2024-12-15T10:30:00.000000Z",
    "updated_at": "2024-12-15T10:30:00.000000Z"
  }
}
```

### 4. Update Loan Payment

**Endpoint:** `PUT /api/loan-payments/{id}`

**Request Body:**
```json
{
  "amount": 2000.00,
  "payment_date": "2024-12-16",
  "notes": "Updated payment amount"
}
```

**Example Request:**
```bash
curl -X PUT "http://127.0.0.1:8000/api/loan-payments/15" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 2000.00,
    "payment_date": "2024-12-16",
    "notes": "Updated payment amount"
  }'
```

### 5. Delete Loan Payment

**Endpoint:** `DELETE /api/loan-payments/{id}`

**Example Request:**
```bash
curl -X DELETE "http://127.0.0.1:8000/api/loan-payments/15" \
  -H "Authorization: Bearer {your_token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Loan payment deleted successfully"
}
```

### 6. Get Payment Statistics

**Endpoint:** `GET /api/loan-payments/statistics`

**Query Parameters:**
- `from_date` (optional): Statistics from date
- `to_date` (optional): Statistics to date

**Example Request:**
```bash
curl -X GET "http://127.0.0.1:8000/api/loan-payments/statistics?from_date=2024-01-01" \
  -H "Authorization: Bearer {your_token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Loan payment statistics retrieved successfully",
  "data": {
    "total_payments": 150,
    "total_amount": 75000.50,
    "average_payment": 500.00,
    "payments_this_month": 25,
    "amount_this_month": 12500.75,
    "daily_average": 500.00
  }
}
```

### 7. Get Payments by Sale

**Endpoint:** `GET /api/loan-payments/sale/{saleId}`

**Example Request:**
```bash
curl -X GET "http://127.0.0.1:8000/api/loan-payments/sale/5" \
  -H "Authorization: Bearer {your_token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Sale payments retrieved successfully",
  "data": {
    "payments": [
      {
        "id": 15,
        "amount": 1500.00,
        "payment_date": "2024-12-15",
        "notes": "Partial payment",
        "user": {
          "name": "Jane Officer"
        },
        "created_at": "2024-12-15T10:30:00.000000Z"
      }
    ],
    "sale_info": {
      "id": 5,
      "customer": "John Doe",
      "total_amount": 5000.00,
      "total_paid": 1500.00,
      "remaining_balance": 3500.00,
      "payment_status": "partial"
    }
  }
}
```

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "amount": ["The amount field is required"],
    "sale_id": ["The sale id field is required"]
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Unauthorized access to this sale"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Loan payment not found"
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Error retrieving loan payments",
  "error": "Database connection failed"
}
```

## Business Logic

### Access Control
- Users can only access loan payments for sales within their tenant
- Payments are automatically associated with the authenticated user

### Payment Validation
- Amount must be greater than 0
- Sale must exist and be accessible to the user
- Payment date cannot be in the future

### Calculation Logic
- Remaining balance = Total sale amount - Total payments made
- Payment status: 'paid' if remaining balance ≤ 0, 'partial' otherwise

## Rate Limiting

The API follows Laravel's default rate limiting:
- 60 requests per minute for authenticated users
- Throttling headers are included in responses

## SDK Examples

### JavaScript/Fetch
```javascript
// Get all payments
const response = await fetch('/api/loan-payments', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
const data = await response.json();

// Create new payment
const newPayment = {
  sale_id: 5,
  amount: 1500.00,
  payment_date: '2024-12-15',
  notes: 'Partial payment'
};

const createResponse = await fetch('/api/loan-payments', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(newPayment)
});
```

### PHP (Guzzle)
```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://127.0.0.1:8000/api',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json'
    ]
]);

// Get payments
$response = $client->get('/loan-payments');
$data = json_decode($response->getBody(), true);

// Create payment
$paymentData = [
    'sale_id' => 5,
    'amount' => 1500.00,
    'payment_date' => '2024-12-15',
    'notes' => 'Partial payment'
];

$response = $client->post('/loan-payments', [
    'json' => $paymentData
]);
```

## Testing

### Postman Collection
Import the following collection to test the API:

```json
{
  "info": {
    "name": "Loan Payments API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "auth": {
    "type": "bearer",
    "bearer": [
      {
        "key": "token",
        "value": "{{access_token}}",
        "type": "string"
      }
    ]
  },
  "item": [
    {
      "name": "Get All Payments",
      "request": {
        "method": "GET",
        "header": [],
        "url": {
          "raw": "{{base_url}}/api/loan-payments",
          "host": ["{{base_url}}"],
          "path": ["api", "loan-payments"]
        }
      }
    },
    {
      "name": "Create Payment",
      "request": {
        "method": "POST",
        "header": [],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"sale_id\": 5,\n  \"amount\": 1500.00,\n  \"payment_date\": \"2024-12-15\",\n  \"notes\": \"Partial payment\"\n}",
          "options": {
            "raw": {
              "language": "json"
            }
          }
        },
        "url": {
          "raw": "{{base_url}}/api/loan-payments",
          "host": ["{{base_url}}"],
          "path": ["api", "loan-payments"]
        }
      }
    }
  ]
}
```

## Support

For API support and questions:
- Check the error messages for specific validation issues
- Ensure proper authentication headers are included
- Verify the sale exists and is accessible to your user
- Contact the development team for technical issues
