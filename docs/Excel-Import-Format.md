# Excel Import Format Documentation

This document explains the Excel file formats required for importing products and categories into the SmartBiz system.

## Import Options

The system supports two types of Excel imports:

1. **Products Import** - Import products with stock information
2. **Categories Import** - Import product categories only

## 1. Products Import Format

### Required Columns

The Excel file must contain the following columns in any order:

| Column Name | Required | Data Type | Description | Example |
|-------------|----------|-----------|-------------|---------|
| `name` | Yes | Text | Product name (max 255 characters) | "Samsung Galaxy S24" |
| `buying_price` | Yes | Number | Cost price (must be > 0) | 75000 |
| `selling_price` | Yes | Number | Selling price (must be > buying_price) | 95000 |
| `category` | No | Text | Product category name | "Electronics" |
| `unit` | No | Text | Unit of measurement | "pcs", "kg", "ltr" |
| `initial_stock` | No | Number | Starting stock quantity | 10 |
| `description` | No | Text | Product description | "Latest smartphone model" |
| `barcode` | No | Text | Product barcode/SKU | "123456789" |

### Valid Units

The following units are supported:
- `pcs` (pieces) - default if not specified
- `kg` (kilograms)
- `g` (grams)
- `ltr` (liters)
- `ml` (milliliters)
- `box`
- `bag`
- `pack`
- `set`
- `pair`
- `dozen`
- `carton`

### Category and Duka Handling

- **Category**: If a category is specified but doesn't exist, it will be automatically created
- **Categories** are created with "active" status
- The import will use fuzzy matching to find existing categories
- **Duka**: If a duka is specified but doesn't exist in your assigned dukas, it will use your default duka
- Each product's initial stock will be created in the specified duka

### Validation Rules

1. **Name**: Required, max 255 characters
2. **Buying Price**: Required, must be greater than 0 and be a valid number
3. **Selling Price**: Required, must be greater than buying price and be a valid number
4. **Unit**: Optional, if invalid will default to "pcs" with a warning message
5. **Initial Stock**: Optional, must be a valid non-negative number if provided
6. **Barcode**: Optional, max 255 characters if provided
7. **Duka**: Optional, specifies which duka to create initial stock in (defaults to your current duka)
8. **Duplicate Check**: Products with duplicate names or auto-generated SKUs will be skipped

### Error Handling

The import process now provides detailed error reporting:
- **Validation errors** are collected for all rows and displayed after import
- **Invalid units** are automatically converted to "pcs" with a warning
- **Duplicate products** are skipped with an error message
- **Missing required fields** are reported with specific row numbers
- **Price validation errors** include the row number and specific issue

### Graceful Error Recovery

Unlike strict validation that stops on the first error, this implementation:
- Processes all rows in the Excel file
- Skips problematic rows but continues with valid ones
- Reports all errors at the end for easy review
- Provides specific row numbers for easy debugging

### Example Products Import

```csv
name,category,buying_price,selling_price,unit,initial_stock,description,barcode,duka
Samsung Galaxy S24,Electronics,75000,95000,pcs,10,Latest smartphone model,123456789,Main Store
Rice 1kg,Food,80,120,kg,50,Premium quality rice,987654321,Main Store
Blue Jeans,Clothing,1200,1800,pcs,25,Men's denim jeans,555666777,Branch A
Cooking Oil 1L,Food,150,220,ltr,30,Pure sunflower oil,888999000,Main Store
Wireless Mouse,Electronics,800,1200,pcs,15,Ergonomic design,111222333,Branch A
```

## 2. Categories Import Format

### Required Columns

The Excel file must contain the following columns in any order:

| Column Name | Required | Data Type | Description | Example |
|-------------|----------|-----------|-------------|---------|
| `name` | Yes | Text | Category name (max 255 characters) | "Electronics" |
| `description` | No | Text | Category description | "Electronic devices and accessories" |
| `parent_category` | No | Text | Parent category name | "Technology" |
| `status` | No | Text | Category status | "active" or "inactive" |

### Status Values

- `active` - Category is active and can be used
- `inactive` - Category is inactive (default: "active")

### Category Hierarchy

- If a parent category is specified but doesn't exist, it will be automatically created
- Categories support hierarchical structure (parent-child relationships)
- Parent categories are created with "active" status

### Validation Rules

1. **Name**: Required, max 255 characters, must be unique within tenant
2. **Description**: Optional, any text
3. **Parent Category**: Optional, will be created if doesn't exist
4. **Status**: Optional, must be "active" or "inactive" (defaults to "active")
5. **Duplicate Check**: Categories with duplicate names within the same tenant will be skipped

### Example Categories Import

```csv
name,description,parent_category,status
Electronics,Electronic devices and accessories,,active
Computers,Desktop and laptop computers,Electronics,active
Mobile Phones,Smartphones and tablets,Electronics,active
Accessories,Electronic accessories,Electronics,active
Clothing,Apparel and fashion items,,active
Men Clothing,Clothing for men,Clothing,active
Women Clothing,Clothing for women,Clothing,active
Footwear,Shoes and boots,,active
Sports Shoes,Athletic footwear,Footwear,active
```

## File Format Requirements

### Supported File Formats
- **.xlsx** (Excel 2007+)
- **.xls** (Excel 97-2003)
- **.csv** (Comma Separated Values)

### File Size Limit
- Maximum file size: 10MB

### Character Encoding
- Use UTF-8 encoding for special characters
- CSV files will automatically include BOM (Byte Order Mark) for Excel compatibility

## Import Process

### Step-by-Step Guide

1. **Download Sample**: Use the "Download Sample" buttons to get template files
2. **Prepare Data**: Fill in your data according to the format requirements
3. **Select Import Type**: Choose between Products or Categories import
4. **Upload File**: Select your Excel/CSV file
5. **Review Instructions**: Check the import requirements and validation rules
6. **Import**: Click the import button to process the file
7. **Review Results**: Check the import summary and any error messages

### Import Results

After import, you will see:
- **Success Count**: Number of successfully imported records
- **Skipped Count**: Number of records skipped due to validation errors
- **Error Details**: Expandable list of specific errors encountered

### Common Error Messages

1. **"Product name is required"** - Missing required product name in row X
2. **"Buying price must be a valid number"** - Non-numeric value in buying price column
3. **"Buying price must be greater than 0"** - Zero or negative buying price
4. **"Selling price must be a valid number"** - Non-numeric value in selling price column
5. **"Selling price must be greater than buying price"** - Price validation failed
6. **"Invalid unit 'X'. Using 'pcs' instead"** - Unit not in supported list, auto-corrected
7. **"Product with name 'X' or SKU 'Y' already exists"** - Duplicate detection
8. **"Initial stock must be a valid non-negative number"** - Invalid stock quantity
9. **"Barcode must not exceed 255 characters"** - Barcode too long
10. **"Duka 'X' not found, using default duka"** - Specified duka doesn't exist
11. **"Import failed: [error message]"** - System-level errors during import

## Best Practices

### For Products Import
1. **Start with sample data** to understand the format
2. **Validate prices** before import (selling > buying)
3. **Use consistent category names** to avoid duplicate categories
4. **Check unit spelling** against the supported units list
5. **Remove empty rows** from your Excel file

### For Categories Import
1. **Import categories first** before importing products
2. **Use clear, descriptive names** for categories
3. **Maintain consistent hierarchy** if using parent categories
4. **Review parent-child relationships** before import

### Data Preparation Tips
1. **Clean your data**: Remove extra spaces and special characters
2. **Standardize formats**: Use consistent date and number formats
3. **Backup original data**: Keep a copy of your source data
4. **Test with small batches**: Start with a few records to verify the process
5. **Use UTF-8 encoding**: Ensure proper character support

## Troubleshooting

### Import Fails to Start
- Check file format (must be .xlsx, .xls, or .csv)
- Verify file size is under 10MB
- Ensure file is not corrupted

### Validation Errors
- Review column names must match exactly (case-sensitive)
- Check data types match requirements
- Verify required fields are not empty

### Partial Import Success
- Review error messages for skipped records
- Fix data issues and re-import failed records
- Check for duplicate entries

### Performance Issues
- Large files (>1000 records) may take longer to process
- Consider splitting large imports into smaller batches
- Ensure stable internet connection during import

## Support

If you encounter issues with Excel import:
1. Check this documentation for format requirements
2. Download and use the sample files as templates
3. Verify your data against the validation rules
4. Contact system administrator for technical support
