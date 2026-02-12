<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\OfficerApiController;
use App\Http\Controllers\Api\LoanPaymentController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\Advnacedinventory;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==============================================================================
// PUBLIC ROUTES
// ==============================================================================
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/register', [AuthController::class, 'registerApi']);
Route::post('/forgot-password', [AuthController::class, 'apiForgotPassword']);
Route::post('/reset-password', [AuthController::class, 'apiResetPassword']);

Route::get('/tenant/plans', [TenantController::class, 'apiGetPlans']);

// ==============================================================================
// PROTECTED ROUTES (Sanctum)
// ==============================================================================
Route::middleware('auth:sanctum')->group(function () {

    // ----------------------------------------------------------------------
    // OFFICER / POS ROUTES
    // ----------------------------------------------------------------------
    Route::prefix('officer')->group(function () {
        // Dashboard
        Route::get('/dashboard/{officer}', [OfficerApiController::class, 'officerdashboardinformation']);
        Route::post('/sync', [OfficerApiController::class, 'sync']);

        // Products & Stock
        Route::get('/products', [OfficerApiController::class, 'apiListProducts']);
        Route::post('/products', [Advnacedinventory::class, 'store']); // Advanced Inventory Create
        Route::get('/products/{productId}', [OfficerApiController::class, 'apiGetProduct']);
        Route::put('/products/{productId}', [OfficerApiController::class, 'apiUpdateProduct']);
        Route::delete('/products/{productId}', [OfficerApiController::class, 'apiDeleteProduct']);

        // Stock Management
        Route::post('/stock', [OfficerApiController::class, 'apiAddStock']);
        Route::put('/stock', [OfficerApiController::class, 'apiUpdateStock']);

        // Sales Management
        Route::get('/sales', [OfficerApiController::class, 'apiGetSales']);
        Route::post('/sales', [Advnacedinventory::class, 'apiCreateSale']); // Advanced Inventory Sale
        Route::get('/sales-with-items', [OfficerApiController::class, 'apiGetSalesWithItems']);
        Route::get('/sales/{id}', [OfficerApiController::class, 'apiGetSale']);
        Route::get('/sales/{id}/invoice', [OfficerApiController::class, 'apiGetSaleInvoice']);

        // Sale Items
        Route::get('/sale-items', [OfficerApiController::class, 'apiGetSaleItems']);
        Route::get('/sale-items/{id}', [OfficerApiController::class, 'apiGetSaleItem']);
        Route::put('/sale-items/{id}', [OfficerApiController::class, 'apiUpdateSaleItem']);
        Route::delete('/sale-items/{id}', [OfficerApiController::class, 'apiDeleteSaleItem']);
        Route::get('/sales/{sale_id}/items', [OfficerApiController::class, 'apiGetSaleItemsBySale']);

        // Product Items (Variants)
        Route::get('/product-items/by-product/{productId}', [OfficerApiController::class, 'apiGetProductItemsByProductId']);
        Route::post('/product-items', [OfficerApiController::class, 'apiStoreProductItem']);

        // Categories
        Route::get('/categories', [OfficerApiController::class, 'apiGetCategories']);
        Route::post('/categories', [OfficerApiController::class, 'apiStoreCategory']);
        Route::put('/categories/{id}', [OfficerApiController::class, 'apiUpdateCategory']);
        Route::delete('/categories/{id}', [OfficerApiController::class, 'apiDestroyCategory']);

        // Customers
        Route::get('/customers', [OfficerApiController::class, 'apiGetCustomers']);
        Route::post('/customers', [OfficerApiController::class, 'apiCreateCustomer']);
        Route::get('/customers/{id}', [OfficerApiController::class, 'apiGetCustomer']);
        Route::put('/customers/{id}', [OfficerApiController::class, 'apiUpdateCustomer']);
        Route::delete('/customers/{customer}', [OfficerApiController::class, 'apiDeleteCustomer']);

        // Tenant Account Interaction
        Route::get('/tenant-account', [OfficerApiController::class, 'apiGetTenantAccount']);
        Route::put('/tenant-account', [OfficerApiController::class, 'apiUpdateTenantAccount']);
        Route::get('/tenant-account/logo', [OfficerApiController::class, 'apiGetTenantLogo']);
        Route::post('/tenant-account/logo', [OfficerApiController::class, 'apiUploadTenantLogo']);

        // Shop Management (from AdvancedInventory)
        Route::post('/dukas', [Advnacedinventory::class, 'storeApi']);
    });

    // ----------------------------------------------------------------------
    // TENANT MANAGEMENT ROUTES
    // ----------------------------------------------------------------------
    Route::prefix('tenant')->group(function () {
        // Overview & Account
        Route::get('/details', [TenantController::class, 'apiGetDetails']);
        Route::get('/tenant-account', [TenantController::class, 'tenantaccount']);
        Route::get('/account', [TenantController::class, 'apiGetTenantAccount']);
        Route::post('/account', [TenantController::class, 'apiCreateOrUpdateTenantAccount']);

        // Duka / Shop Management
        Route::get('/dukas', [TenantController::class, 'apiGetDukas']);
        Route::post('/duka', [TenantController::class, 'apiCreateDuka']);
        Route::get('/duka/{dukaId}/overview', [TenantController::class, 'apiGetDukaOverview']);
        Route::get('/duka/{duka_id}/products', [TenantController::class, 'apiGetDukaProducts']);
        Route::get('/duka/{duka_id}/plan', [TenantController::class, 'apiGetDukaPlan']);
        Route::get('/dukasproducts/{productId}', [TenantController::class, 'getproudctinfindetails']);

        // Workforce / Officers
        Route::get('/officers', [TenantController::class, 'apiGetOfficers']);
        Route::post('/officer', [TenantController::class, 'apiCreateOfficer']);
        Route::put('/officer/{officer_id}', [TenantController::class, 'apiUpdateOfficer']);
        Route::delete('/officer/{officer_id}', [TenantController::class, 'apiDeleteOfficer']);
        Route::get('/officers/{id}/permissions', [TenantController::class, 'apiShowOfficerPermissions']);
        Route::post('/officers/{id}/permissions/update', [TenantController::class, 'apiUpdateOfficerPermissions']);

        // Features
        Route::get('/features', [TenantController::class, 'apiGetFeatures']);
        Route::post('/features', [TenantController::class, 'apiCreateFeature']);
        Route::put('/features/{feature_id}', [TenantController::class, 'apiUpdateFeature']);
        Route::delete('/features/{feature_id}', [TenantController::class, 'apiDeleteFeature']);

        // Subscriptions
        Route::get('/duka-subscriptions', [TenantController::class, 'apiGetDukaSubscriptions']);
        Route::post('/duka-subscription', [TenantController::class, 'apiCreateDukaSubscription']);
        Route::prefix('duka-subscription/{subscription_id}')->group(function () {
            Route::get('/', [TenantController::class, 'apiGetDukaSubscription']);
            Route::put('/', [TenantController::class, 'apiUpdateDukaSubscription']);
            Route::delete('/', [TenantController::class, 'apiDeleteDukaSubscription']);
        });

        // Tenant Product Management
        Route::prefix('products')->group(function () {
            Route::get('/', [TenantController::class, 'apiListProducts']);
            Route::post('/', [TenantController::class, 'apiCreateProduct']);
            Route::get('/{productId}', [TenantController::class, 'apiShowProduct']);
            Route::put('/{productId}', [TenantController::class, 'apiUpdateProduct']);
            Route::delete('/{productId}', [TenantController::class, 'apiDeleteProduct']);
        });

        // Tenant Category Management
        Route::prefix('categories')->group(function () {
            Route::get('/', [TenantController::class, 'apiListCategories']);
            Route::post('/', [TenantController::class, 'apiCreateCategory']);
            Route::get('/{categoryId}', [TenantController::class, 'apiShowCategory']);
            Route::put('/{categoryId}', [TenantController::class, 'apiUpdateCategory']);
            Route::delete('/{categoryId}', [TenantController::class, 'apiDeleteCategory']);
            Route::get('/{categoryId}/product-count', [TenantController::class, 'apiGetCategoryProductCount']);
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/consolidated-pl', [TenantController::class, 'apiConsolidatedProfitLoss']);
            Route::get('/low-stock', [TenantController::class, 'apiGetLowStockProducts']);
            Route::get('/transactions', [TenantController::class, 'apiTransactionReport']);
            Route::get('/aging-and-stock', [TenantController::class, 'apiInventoryAndLoanAnalysis']);
        });

        // Tenant Sales View
        Route::get('/sales', [TenantController::class, 'apiGetSales']);

        // Delete Tenant Account
        Route::delete('/account', [TenantController::class, 'apiDeleteTenantAccount']);
    });

    // ----------------------------------------------------------------------
    // LOAN PAYMENTS
    // ----------------------------------------------------------------------
    Route::prefix('loan-payments')->group(function () {
        Route::get('/', [LoanPaymentController::class, 'index']);
        Route::post('/', [LoanPaymentController::class, 'store']);
        Route::get('/statistics', [LoanPaymentController::class, 'statistics']);
        Route::get('/sale/{saleId}', [LoanPaymentController::class, 'bySale']);
        Route::get('/{id}', [LoanPaymentController::class, 'show']);
        Route::put('/{id}', [LoanPaymentController::class, 'update']);
        Route::delete('/{id}', [LoanPaymentController::class, 'destroy']);
    });



    Route::get('/user/permissions', [UserPermissionController::class, 'index']);

    // ----------------------------------------------------------------------
    // ADVANCED INVENTORY / UTILITIES
    // ----------------------------------------------------------------------
    // These seemed to be floating at the bottom

    // Updates and Deletions for Sales (AdvancedInventory logic)
    Route::put('/sales/{id}', [Advnacedinventory::class, 'updateSale']);
    Route::get('/sales/{id}', [Advnacedinventory::class, 'destroySale']); // Note: GET for destroy as per original
    Route::post('/recordpaymentforsales', [OfficerApiController::class, 'storeApiforSales']); // Seemed orphaned in original
    Route::post('/stocks/reduce', [Advnacedinventory::class, 'apiReduceStock']);
    Route::post('/product-items', [Advnacedinventory::class, 'apiStoreProductItem']);
    Route::get('/sales/{id}/update-date', [Advnacedinventory::class, 'updateSaleDate']);
    Route::get('/deleteaccount', [Advnacedinventory::class, 'apiDeleteAccount']);
});
