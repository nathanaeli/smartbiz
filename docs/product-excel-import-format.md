# Product Excel Import Format

This document describes the format required for importing products via Excel files in the SmartBiz system.

## Overview

The Excel import feature allows officers to bulk import products with smart validation and automatic category creation. The system will automatically:

- Create categories if they don't exist
- Generate unique SKUs for each product
- Validate prices and data integrity
- Handle initial stock assignment
- Skip duplicates and provide detailed error messages

## Excel File Requirements

- **Format**: .xlsx or .xls
- **Maximum Size**: 5MB
- **First Row**: Must contain column headers (case-sensitive)
- **Data Rows**: Start from row 2 onwards

## Required Columns

| Column Name | Type | Required | Description |
|-------------|------|----------|-------------|
| `name` | String | Yes | Product name (max 255 characters) |
| `buying_price` | Number | Yes | Purchase price (must be > 0) |
| `selling_price` | Number | Yes | Selling price (must be > buying_price) |

## Optional Columns

| Column Name | Type | Default | Description |
|-------------|------|---------|-------------|
| `unit` | String | 'pcs' | Unit of measurement. Valid values: pcs, kg, g, ltr, ml, box, bag, pack, set, pair, dozen, carton |
| `category` | String | null | Product category name. Will be auto-created if it doesn't exist |
| `description` | String | null | Product description |
| `barcode` | String/Number | null | Product barcode (must be unique, numeric values will be converted to strings) |
| `initial_stock` | Integer | 0 | Initial stock quantity to assign |
| `duka` | String | First assigned duka | Duka name for stock assignment (must be one of officer's assigned dukas) |

## Sample Excel Data

| name | buying_price | selling_price | unit | category | description | barcode | initial_stock | duka |
|------|--------------|---------------|------|----------|-------------|---------|---------------|------|
| Rice 5kg | 150.00 | 180.00 | pcs | Food & Beverages | Premium quality rice | 123456789 | 50 | Main Store |
| Sugar 1kg | 80.00 | 95.00 | pcs | Food & Beverages | White sugar | 987654321 | 100 | Branch A |
| Cooking Oil 1L | 120.00 | 140.00 | pcs | Food & Beverages | Vegetable oil | 456789123 | 75 | Main Store |
| Soap Bar | 25.00 | 35.00 | pcs | Personal Care | Bath soap | 789123456 | 200 | Branch A |

## Validation Rules

### Price Validation
- `buying_price` must be greater than 0
- `selling_price` must be greater than `buying_price`
- Both prices must be numeric

### Unit Validation
Must be one of the predefined units:
- pcs (pieces)
- kg (kilograms)
- g (grams)
- ltr (liters)
- ml (milliliters)
- box
- bag
- pack
- set
- pair
- dozen
- carton

### Category Handling
- If category doesn't exist, it will be automatically created
- Category names are matched case-insensitively
- New categories are created with "active" status

### Stock Assignment
- `initial_stock` must be a non-negative integer
- If `duka` is specified, it must match one of the officer's assigned dukas
- If `duka` is not specified, stock will be assigned to the officer's first assigned duka
- Stock movements are automatically recorded

### Uniqueness
- Product names and SKUs must be unique within the tenant
- Barcodes must be unique across all products

## Smart Features

### 1. Automatic SKU Generation
- SKUs are generated in the format: `NAME-STOCK-RANDOM`
- Example: `RICE-050-42`
- Automatically ensures uniqueness

### 2. Category Auto-Creation
- If a category doesn't exist, it's created automatically
- Prevents manual category creation for bulk imports

### 3. Intelligent Stock Assignment
- Automatically assigns stock to appropriate dukas
- Records stock movements for audit trails

### 4. Error Handling
- Detailed error messages for each failed row
- Continues processing other rows even if some fail
- Provides summary of successful imports and errors

## Error Messages

The import process provides detailed error messages for troubleshooting:

- **Missing required fields**: "Row X: Name, buying price, and selling price are required."
- **Invalid prices**: "Row X: Selling price must be greater than buying price."
- **Duplicate products**: "Row X: Product with name 'Product Name' or SKU 'SKU-123' already exists."
- **Invalid duka**: "Row X: Specified duka is not assigned to you."

## Download Template

Officers can download a pre-formatted Excel template with:
- Correct column headers
- Sample data rows
- Data validation where applicable

## Security Considerations

- Only officers with `adding_product` permission can import
- All products are created under the officer's tenant
- Stock can only be assigned to officer's assigned dukas
- File size and type validation prevents malicious uploads

## Performance Notes

- Maximum 1000 products per import (configurable)
- Transactions ensure data consistency
- Progress feedback during import process
- Automatic cleanup of failed imports
