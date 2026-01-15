# Customer Management API Documentation

This document provides comprehensive documentation for the Customer Management API endpoints that allow officers to manage customer operations through REST API calls.

## Base URL
```
/api/officer/customers
```

## Authentication
All endpoints require authentication. Include a valid API token in the Authorization header:
```
Authorization: Bearer {your_api_token}
```

## Content Type
All requests and responses use JSON format:
```
Content-Type: application/json
```

## Endpoints Overview

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/officer/customers` | Retrieve all customers | ✅ |
| POST | `/officer/customers` | Create a new customer | ✅ |
| GET | `/officer/customers/{id}` | Retrieve specific customer details | ✅ |
| PUT | `/officer/customers/{id}` | Update customer information | ✅ |

---

## 1. Get All Customers

### Endpoint
```http
GET /api/officer/customers
```

### Description
Retrieves a list of all customers associated with the authenticated officer. Results can be filtered and paginated using query parameters.

### Request Parameters

#### Query Parameters (Optional)
| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `page` | integer | Page number for pagination | 1 |
| `per_page` | integer | Number of items per page | 15 |
| `search` | string | Search term for name, email, or phone | - |
| `duka_id` | integer | Filter customers by specific duka ID | - |
| `created_from` | date | Filter customers created from date (YYYY-MM-DD) | - |
| `created_to` | date | Filter customers created to date (YYYY-MM-DD) | - |
| `sort_by` | string | Sort field (name, email, phone, created_at) | name |
| `sort_order` | string | Sort order (asc, desc) | asc |

### Success Response
```json
{
    "success": true,
    "data": {
        "customers": [
            {
                "id": 45,
                "name": "John Doe",
                "email": "john.doe@example.com",
                "phone": "+255123456789",
                "alternative_phone": "+255987654321",
                "address": "123 Main Street, Dar es Salaam",
                "city": "Dar es Salaam",
                "postal_code": "10101",
                "country": "Tanzania",
                "date_of_birth": "1990-05-15",
                "gender": "male",
                "customer_type": "regular",
                "credit_limit": 500000.00,
                "payment_terms": 30,
                "status": "active",
                "notes": "Preferred customer, always pays on time",
                "duka_id": 10,
                "duka": {
                    "id": 10,
                    "name": "Main Store",
                    "location": "Dar es Salaam"
                },
                "total_purchases": 15,
                "total_amount_spent": 2500000.00,
                "last_purchase_date": "2024-11-28T14:30:00Z",
                "created_at": "2024-01-15T10:30:00Z",
                "updated_at": "2024-12-01T16:45:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 150,
            "total_pages": 10,
            "has_next_page": true,
            "has_previous_page": false
        },
        "summary": {
            "total_customers": 150,
            "active_customers": 145,
            "inactive_customers": 5,
            "new_this_month": 12
        }
    },
    "message": "Customers retrieved successfully"
}
```

### Error Responses

#### 401 Unauthorized
```json
{
    "success": false,
    "message": "Unauthorized access",
    "errors": ["Invalid or missing authentication token"]
}
```

#### 403 Forbidden
```json
{
    "success": false,
    "message": "Forbidden",
    "errors": ["Insufficient permissions to access customer data"]
}
```

#### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Internal server error",
    "errors": ["An error occurred while retrieving customer data"]
}
```

---

## 2. Create New Customer

### Endpoint
```http
POST /api/officer/customers
```

### Description
Creates a new customer record. This endpoint allows officers to add new customers to the system.

### Request Headers
```
Content-Type: application/json
Authorization: Bearer {your_api_token}
```

### Request Body
```json
{
    "name": "Jane Smith",
    "email": "jane.smith@example.com",
    "phone": "+255987654321",
    "alternative_phone": "+255123456789",
    "address": "456 Business Avenue",
    "city": "Dar es Salaam",
    "postal_code": "10101",
    "country": "Tanzania",
    "date_of_birth": "1985-08-22",
    "gender": "female",
    "customer_type": "premium",
    "credit_limit": 1000000.00,
    "payment_terms": 45,
    "duka_id": 10,
    "notes": "VIP customer with special discount privileges"
}
```

### Request Parameters

| Field | Type | Required | Description | Validation |
|-------|------|----------|-------------|------------|
| `name` | string | ✅ | Customer full name | Max 255 characters |
| `email` | string | ❌ | Customer email address | Valid email format, unique |
| `phone` | string | ✅ | Primary phone number | Max 20 characters |
| `alternative_phone` | string | ❌ | Alternative phone number | Max 20 characters |
| `address` | string | ❌ | Street address | Max 500 characters |
| `city` | string | ❌ | City name | Max 100 characters |
| `postal_code` | string | ❌ | Postal/ZIP code | Max 20 characters |
| `country` | string | ❌ | Country name | Max 100 characters |
| `date_of_birth` | date | ❌ | Date of birth | YYYY-MM-DD format |
| `gender` | string | ❌ | Gender | male, female, other |
| `customer_type` | string | ❌ | Customer classification | regular, premium, wholesale |
| `credit_limit` | decimal | ❌ | Credit limit amount | Min: 0 |
| `payment_terms` | integer | ❌ | Payment terms in days | Min: 0, Max: 365 |
| `duka_id` | integer | ✅ | Associated duka ID | Must exist in database |
| `notes` | string | ❌ | Additional notes | Max 1000 characters |

### Success Response
```json
{
    "success": true,
    "data": {
        "customer": {
            "id": 46,
            "name": "Jane Smith",
            "email": "jane.smith@example.com",
            "phone": "+255987654321",
            "alternative_phone": "+255123456789",
            "address": "456 Business Avenue",
            "city": "Dar es Salaam",
            "postal_code": "10101",
            "country": "Tanzania",
            "date_of_birth": "1985-08-22",
            "gender": "female",
            "customer_type": "premium",
            "credit_limit": 1000000.00,
            "payment_terms": 45,
            "status": "active",
            "duka_id": 10,
            "notes": "VIP customer with special discount privileges",
            "total_purchases": 0,
            "total_amount_spent": 0.00,
            "created_at": "2024-12-04T13:11:00Z",
            "updated_at": "2024-12-04T13:11:00Z"
        }
    },
    "message": "Customer created successfully"
}
```

### Error Responses

#### 422 Unprocessable Entity (Validation Error)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": ["The name field is required"],
        "email": ["The email must be a valid email address"],
        "phone": ["The phone field is required"],
        "duka_id": ["The selected duka ID is invalid"]
    }
}
```

#### 409 Conflict
```json
{
    "success": false,
    "message": "Customer already exists",
    "errors": ["A customer with this email address already exists"]
}
```

---

## 3. Get Specific Customer Details

### Endpoint
```http
GET /api/officer/customers/{id}
```

### Description
Retrieves detailed information about a specific customer by their ID.

### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | ✅ | The unique identifier of the customer |

### Success Response
```json
{
    "success": true,
    "data": {
        "customer": {
            "id": 45,
            "name": "John Doe",
            "email": "john.doe@example.com",
            "phone": "+255123456789",
            "alternative_phone": "+255987654321",
            "address": "123 Main Street",
            "city": "Dar es Salaam",
            "postal_code": "10101",
            "country": "Tanzania",
            "date_of_birth": "1990-05-15",
            "gender": "male",
            "customer_type": "regular",
            "credit_limit": 500000.00,
            "payment_terms": 30,
            "status": "active",
            "duka_id": 10,
            "duka": {
                "id": 10,
                "name": "Main Store",
                "location": "Dar es Salaam"
            },
            "purchase_history": [
                {
                    "id": 123,
                    "invoice_number": "INV-2024-001",
                    "amount": 150000.00,
                    "date": "2024-11-28T14:30:00Z",
                    "payment_status": "paid"
                }
            ],
            "statistics": {
                "total_purchases": 15,
                "total_amount_spent": 2500000.00,
                "average_order_value": 166666.67,
                "last_purchase_date": "2024-11-28T14:30:00Z",
                "first_purchase_date": "2024-02-15T09:15:00Z",
                "payment_score": 95
            },
            "notes": "Preferred customer, always pays on time",
            "created_at": "2024-01-15T10:30:00Z",
            "updated_at": "2024-12-01T16:45:00Z"
        }
    },
    "message": "Customer details retrieved successfully"
}
```

### Error Responses

#### 404 Not Found
```json
{
    "success": false,
    "message": "Customer not found",
    "errors": ["The requested customer could not be found"]
}
```

#### 403 Forbidden
```json
{
    "success": false,
    "message": "Access denied",
    "errors": ["You do not have permission to view this customer"]
}
```

---

## 4. Update Customer Information

### Endpoint
```http
PUT /api/officer/customers/{id}
```

### Description
Updates existing customer information. All fields are optional - only provided fields will be updated.

### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | ✅ | The unique identifier of the customer |

### Request Headers
```
Content-Type: application/json
Authorization: Bearer {your_api_token}
```

### Request Body
```json
{
    "name": "Johnathan Doe",
    "email": "johnathan.doe@example.com",
    "phone": "+255987654321",
    "credit_limit": 750000.00,
    "customer_type": "premium",
    "notes": "Updated: VIP customer with increased credit limit"
}
```

### Request Parameters
All fields are the same as in the create endpoint, but all are optional for updates.

### Success Response
```json
{
    "success": true,
    "data": {
        "customer": {
            "id": 45,
            "name": "Johnathan Doe",
            "email": "johnathan.doe@example.com",
            "phone": "+255987654321",
            "credit_limit": 750000.00,
            "customer_type": "premium",
            "status": "active",
            "duka_id": 10,
            "notes": "Updated: VIP customer with increased credit limit",
            "created_at": "2024-01-15T10:30:00Z",
            "updated_at": "2024-12-04T13:15:00Z"
        }
    },
    "message": "Customer updated successfully"
}
```

### Error Responses

#### 404 Not Found
```json
{
    "success": false,
    "message": "Customer not found",
    "errors": ["The customer you are trying to update does not exist"]
}
```

#### 422 Unprocessable Entity (Validation Error)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email must be a valid email address"],
        "credit_limit": ["The credit limit must be at least 0"]
    }
}
```

#### 409 Conflict
```json
{
    "success": false,
    "message": "Email already taken",
    "errors": ["A customer with this email address already exists"]
}
```

---

## Bulk Operations (Optional)

### Bulk Create Customers
```http
POST /api/officer/customers/bulk
```

### Bulk Update Customers
```http
PUT /api/officer/customers/bulk
```

---

## Error Handling

### HTTP Status Codes
- `200` - Success
- `201` - Created successfully
- `400` - Bad Request
- `401` - Unauthorized (missing or invalid token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `409` - Conflict (duplicate data)
- `422` - Validation failed
- `500` - Internal Server Error

### Error Response Format
```json
{
    "success": false,
    "message": "Brief error description",
    "errors": [
        "Detailed error message 1",
        "Detailed error message 2"
    ],
    "timestamp": "2024-12-04T13:11:00Z",
    "request_id": "req_123456789"
}
```

---

## Rate Limiting

API requests are rate limited to prevent abuse:
- **Authenticated requests**: 1000 requests per hour
- **Burst limit**: 10 requests per minute

Rate limit headers are included in all responses:
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1701697200
```

---

## Data Validation Rules

### Phone Numbers
- Must be in international format (+255XXXXXXXXX)
- Maximum 20 characters
- Only numbers and + symbol allowed

### Email Addresses
- Must be valid email format
- Unique across the system
- Maximum 255 characters

### Names
- Maximum 255 characters
- Cannot be empty or just whitespace

### Credit Limits
- Must be positive numbers
- Maximum 9 decimal places
- Cannot exceed system limits

### Dates
- Must be in YYYY-MM-DD format
- Date of birth cannot be in the future
- Cannot be older than 120 years

---

## SDK Examples

### JavaScript/Node.js
```javascript
const axios = require('axios');

class CustomerApiClient {
    constructor(baseUrl, apiToken) {
        this.baseUrl = baseUrl;
        this.headers = {
            'Authorization': `Bearer ${apiToken}`,
            'Content-Type': 'application/json'
        };
    }

    async getAllCustomers(params = {}) {
        try {
            const response = await axios.get(`${this.baseUrl}/customers`, {
                headers: this.headers,
                params
            });
            return response.data;
        } catch (error) {
            throw error.response.data;
        }
    }

    async createCustomer(customerData) {
        try {
            const response = await axios.post(`${this.baseUrl}/customers`, customerData, {
                headers: this.headers
            });
            return response.data;
        } catch (error) {
            throw error.response.data;
        }
    }

    async getCustomer(customerId) {
        try {
            const response = await axios.get(`${this.baseUrl}/customers/${customerId}`, {
                headers: this.headers
            });
            return response.data;
        } catch (error) {
            throw error.response.data;
        }
    }

    async updateCustomer(customerId, updateData) {
        try {
            const response = await axios.put(`${this.baseUrl}/customers/${customerId}`, updateData, {
                headers: this.headers
            });
            return response.data;
        } catch (error) {
            throw error.response.data;
        }
    }
}

// Usage example
const client = new CustomerApiClient('/api/officer', 'your-api-token');

// Get all customers with pagination
const customers = await client.getAllCustomers({ 
    page: 1, 
    per_page: 20, 
    search: 'john' 
});

// Create a new customer
const newCustomer = await client.createCustomer({
    name: 'Alice Johnson',
    email: 'alice@example.com',
    phone: '+255123456789',
    duka_id: 10
});

// Get customer details
const customer = await client.getCustomer(45);

// Update customer information
const updatedCustomer = await client.updateCustomer(45, {
    credit_limit: 1000000,
    customer_type: 'premium'
});
```

### PHP
```php
<?php

class CustomerApiClient {
    private $baseUrl;
    private $apiToken;
    private $headers;

    public function __construct($baseUrl, $apiToken) {
        $this->baseUrl = $baseUrl;
        $this->apiToken = $apiToken;
        $this->headers = [
            'Authorization: Bearer ' . $apiToken,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    }

    public function getAllCustomers($params = []) {
        $url = $this->baseUrl . '/customers?' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception('API Error: ' . $response);
        }
        
        return json_decode($response, true);
    }

    public function createCustomer($customerData) {
        $url = $this->baseUrl . '/customers';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception('API Error: ' . $response);
        }
        
        return json_decode($response, true);
    }

    public function getCustomer($customerId) {
        $url = $this->baseUrl . '/customers/' . $customerId;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception('API Error: ' . $response);
        }
        
        return json_decode($response, true);
    }

    public function updateCustomer($customerId, $updateData) {
        $url = $this->baseUrl . '/customers/' . $customerId;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception('API Error: ' . $response);
        }
        
        return json_decode($response, true);
    }
}

// Usage example
$client = new CustomerApiClient('/api/officer', 'your-api-token');

// Get all customers
$customers = $client->getAllCustomers(['page' => 1, 'per_page' => 20]);

// Create a new customer
$newCustomer = $client->createCustomer([
    'name' => 'Alice Johnson',
    'email' => 'alice@example.com',
    'phone' => '+255123456789',
    'duka_id' => 10
]);

// Update customer
$updatedCustomer = $client->updateCustomer(45, [
    'credit_limit' => 1000000,
    'customer_type' => 'premium'
]);
```

---

## Testing

### cURL Examples

#### Get All Customers
```bash
curl -X GET "https://your-domain.com/api/officer/customers?page=1&per_page=10&search=john" \
     -H "Authorization: Bearer your-api-token" \
     -H "Content-Type: application/json"
```

#### Create New Customer
```bash
curl -X POST "https://your-domain.com/api/officer/customers" \
     -H "Authorization: Bearer your-api-token" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Alice Johnson",
       "email": "alice@example.com",
       "phone": "+255123456789",
       "duka_id": 10
     }'
```

#### Get Specific Customer
```bash
curl -X GET "https://your-domain.com/api/officer/customers/45" \
     -H "Authorization: Bearer your-api-token" \
     -H "Content-Type: application/json"
```

#### Update Customer
```bash
curl -X PUT "https://your-domain.com/api/officer/customers/45" \
     -H "Authorization: Bearer your-api-token" \
     -H "Content-Type: application/json" \
     -d '{
       "credit_limit": 1000000,
       "customer_type": "premium"
     }'
```

---

## Best Practices

### 1. Data Validation
- Always validate phone numbers before sending
- Ensure email addresses are in correct format
- Check credit limits are within acceptable ranges

### 2. Error Handling
- Implement proper error handling for all API calls
- Check response status codes and handle them appropriately
- Log errors for debugging purposes

### 3. Performance
- Use pagination for large customer lists
- Cache customer data when appropriate
- Use search parameters to filter results

### 4. Security
- Never log sensitive customer information
- Use HTTPS for all API communications
- Implement proper authentication and authorization

### 5. Data Consistency
- Use consistent phone number formats (+255XXXXXXXXX)
- Validate email addresses are unique
- Ensure duka_id references exist before creating customers

---

## Webhook Integration

Customers API supports webhooks for real-time updates:

### Available Webhook Events
- `customer.created` - New customer added
- `customer.updated` - Customer information modified
- `customer.deleted` - Customer removed (if applicable)

### Webhook Payload Example
```json
{
    "event": "customer.created",
    "data": {
        "customer": {
            "id": 46,
            "name": "Jane Smith",
            "email": "jane.smith@example.com",
            "phone": "+255987654321",
            "duka_id": 10,
            "created_at": "2024-12-04T13:11:00Z"
        }
    },
    "timestamp": "2024-12-04T13:11:00Z"
}
```

---

## Changelog

### Version 1.0 (Current)
- Initial release
- Basic CRUD operations for customer management
- Search and filtering capabilities
- Pagination support
- Comprehensive validation
- Authentication and rate limiting

---

## Support

For API support and questions:
- **Email**: api-support@smartbiz.co.tz
- **Documentation**: https://docs.smartbiz.co.tz
- **Status Page**: https://status.smartbiz.co.tz

---

*This documentation is maintained by the SMARTBIZ Development Team. Last updated: December 4, 2024*
