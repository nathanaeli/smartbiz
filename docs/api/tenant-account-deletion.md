# Tenant Account Deletion API

## Overview

This API endpoint allows tenants to permanently delete their account and all associated data from the system. This is an irreversible operation that will remove all business data including sales records, products, customers, and financial information.

## Endpoint

```
DELETE /api/tenant/account
```

## Authentication

This endpoint requires authentication using Laravel Sanctum. The authenticated user must have the `tenant` role.

**Headers:**
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

## Request

### Method
`DELETE`

### URL Parameters
None

### Body Parameters
None required

### Example Request
```bash
curl -X DELETE \
  https://api.smartbiz.com/api/tenant/account \
  -H 'Authorization: Bearer your_sanctum_token_here' \
  -H 'Accept: application/json'
```

## Response

### Success Response

**Status Code:** `200 OK`

**Content:**
```json
{
  "success": true,
  "message": "Tenant account and all related data deleted successfully."
}
```

### Error Responses

#### 403 Forbidden - Unauthorized Access
```json
{
  "success": false,
  "message": "Unauthorized. Only tenants can delete their account."
}
```

#### 404 Not Found - Tenant Not Found
```json
{
  "success": false,
  "message": "Tenant not found."
}
```

#### 500 Internal Server Error - Deletion Failed
```json
{
  "success": false,
  "message": "Failed to delete tenant account.",
  "error": "Detailed error message here"
}
```

## What Gets Deleted

When a tenant account is deleted, the following data is permanently removed:

### Business Structure
- All Dukas (shops) belonging to the tenant
- All Duka subscriptions

### Product Data
- All products and product categories
- All stock records and inventory
- All stock movements and transfers
- All product items and variants

### Sales Data
- All sales records and transactions
- All sale items and order details
- All loan payments and debt records

### Customer Data
- All customer records and contact information
- All customer purchase history

### Financial Data
- All financial transactions (income and expenses)
- All cash flow records
- Tenant account information

### Staff Data
- All officer accounts assigned to the tenant
- All officer permissions and assignments
- All staff permission records

### Communication Data
- All messages and notifications
- All communication history

## Important Notes

1. **Irreversible Operation**: Once deleted, the account and all data cannot be recovered. There is no trash/recycle bin functionality.

2. **Data Backup**: It is recommended to export all important data before deleting the account.

3. **Subscription Impact**: Any active subscriptions will be immediately terminated. No refunds will be provided for prepaid periods.

4. **Legal Compliance**: The deletion process complies with data protection regulations. All personal and business data is permanently removed from the system.

5. **Transaction Safety**: The deletion process uses database transactions to ensure data consistency. If any part of the deletion fails, the entire operation is rolled back.

## Security Considerations

- Only authenticated users with the `tenant` role can delete their own account
- The endpoint uses HTTPS for secure communication
- The operation requires explicit confirmation (DELETE method)
- All deletion operations are logged in the system audit trail

## Rate Limiting

This endpoint may be subject to rate limiting to prevent abuse. Typical limits are:
- Maximum 5 requests per minute per account
- Contact support if you need to perform bulk operations

## Support

If you encounter any issues with account deletion:

1. **Before Deletion**: Contact support if you need help exporting data
2. **During Deletion**: If the process fails, check the error message and try again
3. **After Deletion**: If you accidentally deleted your account, contact support immediately (within 24 hours) for possible recovery options

**Support Email**: support@smartbiz.com
**Support Phone**: +254 700 123456

## Example Workflow

### Step 1: Export Data (Recommended)
Before deleting, export your important data:
```bash
GET /api/tenant/reports/consolidated-pl
GET /api/tenant/sales
GET /api/tenant/products
```

### Step 2: Delete Account
```bash
DELETE /api/tenant/account
```

### Step 3: Confirmation
You will receive a confirmation email at your registered email address confirming the account deletion.

## Troubleshooting

### "Unauthorized" Error
- Ensure you are logged in with a tenant account
- Verify your authentication token is valid
- Check that your user has the `tenant` role

### "Tenant not found" Error
- This typically shouldn't happen if you're authenticated
- Contact support if you receive this error

### "Failed to delete" Error
- There may be a temporary system issue
- Wait a few minutes and try again
- If the problem persists, contact support with the error details

## API Versioning

This endpoint is part of API v1.0. The version is indicated in the URL path.

## Changelog

- **v1.0 (2026-01-27)**: Initial release of tenant account deletion API
- Comprehensive data deletion across all related entities
- Transaction safety and error handling
- Proper authorization and security measures
