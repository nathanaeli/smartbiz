# Permissions Excel Import Format

This document explains the Excel file format required for importing permissions into the SmartBiz system.

## Import Format

The Excel file should contain the following columns in any order:

| Column Name | Required | Data Type | Description | Example |
|-------------|----------|-----------|-------------|---------|
| `name` | Yes | Text | Permission name (lowercase with hyphens) | `manage-users` |
| `display_name` | Yes | Text | Human-readable permission name | `Manage Users` |
| `description` | No | Text | Permission description | `Allows creating, editing and deleting users` |
| `is_active` | No | Text/Boolean | Active status | `true`, `1`, `yes`, `active` |

## Validation Rules

### Required Fields
- **name**: Must be unique, lowercase with hyphens only (e.g., `manage-users`, `view-reports`)
- **display_name**: Required, human-readable name (e.g., `Manage Users`)

### Optional Fields
- **description**: Any text describing what the permission allows
- **is_active**: 
  - Accepts: `true`, `1`, `yes`, `active` → sets as active
  - Accepts: `false`, `0`, `no`, `inactive` → sets as inactive
  - Default: `true` if not specified or empty

### Validation Constraints
1. **Name**: Must match pattern `/^[a-z-]+$/` (lowercase letters and hyphens only)
2. **Duplicate Check**: Permissions with duplicate names will be skipped
3. **Case Sensitivity**: Permission names are case-sensitive

## Sample Data

```csv
name,display_name,description,is_active
manage-users,Manage Users,Allows creating, editing and deleting users,true
view-reports,View Reports,Allows viewing system reports and analytics,true
manage-settings,Manage Settings,Allows modifying system settings,false
create-sales,Create Sales,Allows creating new sales transactions,true
view-inventory,View Inventory,Allows viewing inventory and stock levels,true
delete-sales,Delete Sales,Allows deleting sales records,false
manage-customers,Manage Customers,Allows managing customer information,true
```

## How to Use

### Step 1: Download Sample Format
1. Go to: `http://127.0.0.1:8000/super-admin/available-permissions/create`
2. Click "⬇ Download Sample Format" button
3. This downloads an Excel file with existing permissions as examples

### Step 2: Prepare Your Data
1. Open the downloaded sample file
2. Replace the sample data with your permissions
3. Follow the column requirements and validation rules
4. Save as `.xlsx`, `.xls`, or `.csv` format

### Step 3: Import
1. Go to the create permissions page
2. Upload your prepared Excel file
3. Click "Create Permission" to process
4. Review the import results and any error messages

## File Requirements

### Supported Formats
- `.xlsx` (Excel 2007+)
- `.xls` (Excel 97-2003)
- `.csv` (Comma Separated Values)

### File Size
- Maximum file size: 10MB

### Character Encoding
- Use UTF-8 encoding for special characters
- CSV files will automatically include BOM for Excel compatibility

## Import Results

After importing, you'll see:
- **Success Count**: Number of successfully imported permissions
- **Skipped Count**: Number of permissions skipped due to errors
- **Error Details**: List of specific errors encountered

## Common Error Messages

1. **"Permission name is required"** - Missing name in row X
2. **"Permission name must be lowercase with hyphens only"** - Invalid name format
3. **"Display name is required"** - Missing display_name in row X
4. **"Permission with name 'X' already exists"** - Duplicate permission name
5. **"Import failed: [error message]"** - System-level errors

## Best Practices

1. **Use the Download Sample button** to get the correct format
2. **Follow naming conventions** - lowercase with hyphens
3. **Keep descriptions concise** but informative
4. **Test with small batches** first to verify the process
5. **Check for duplicates** before importing

## Support

If you encounter issues:
1. Download the sample format first
2. Verify your data against the validation rules
3. Check error messages for specific issues
4. Contact system administrator for technical support
