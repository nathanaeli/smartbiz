<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [App\Http\Controllers\AuthController::class, 'apiLogin']);
Route::get('/tenant/plans', [App\Http\Controllers\TenantController::class, 'apiGetPlans']);
Route::post('/forgot-password', [App\Http\Controllers\AuthController::class, 'apiForgotPassword']);
Route::post('/reset-password', [App\Http\Controllers\AuthController::class, 'apiResetPassword']);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'registerApi']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/officer/dashboard/{officer}', [App\Http\Controllers\OfficerApiController::class, 'officerdashboardinformation']);
    Route::post('/officer/products', [App\Http\Controllers\OfficerApiController::class, 'apiAddProduct']);
    Route::get('/officer/products/{productId}', [App\Http\Controllers\OfficerApiController::class, 'apiGetProduct']);
    Route::put('/officer/products/{productId}', [App\Http\Controllers\OfficerApiController::class, 'apiUpdateProduct']);
    Route::put('/officer/stock', [App\Http\Controllers\OfficerApiController::class, 'apiUpdateStock']);
    Route::delete('/officer/products/{productId}', [App\Http\Controllers\OfficerApiController::class, 'apiDeleteProduct']);
    Route::post('/officer/stock', [App\Http\Controllers\OfficerApiController::class, 'apiAddStock']);
    Route::post('/recordpaymentforsales', [App\Http\Controllers\OfficerApiController::class, 'storeApiforSales']);
    Route::get('/officer/product-items/by-product/{productId}', [App\Http\Controllers\OfficerApiController::class, 'apiGetProductItemsByProductId']);
    Route::post('/officer/product-items', [App\Http\Controllers\OfficerApiController::class, 'apiStoreProductItem']);
    Route::post('/officer/sync', [App\Http\Controllers\OfficerApiController::class, 'sync']);
    Route::get('/officer/categories', [App\Http\Controllers\OfficerApiController::class, 'apiGetCategories']);
    Route::post('/officer/categories', [App\Http\Controllers\OfficerApiController::class, 'apiStoreCategory']);
    Route::put('/officer/categories/{id}', [App\Http\Controllers\OfficerApiController::class, 'apiUpdateCategory']);
    Route::delete('/officer/categories/{id}', [App\Http\Controllers\OfficerApiController::class, 'apiDestroyCategory']);
    // // Sales management routes
    // Sales management routes
    Route::get('/officer/sales', [App\Http\Controllers\OfficerApiController::class, 'apiGetSales']);
    Route::get('/officer/sales-with-items', [App\Http\Controllers\OfficerApiController::class, 'apiGetSalesWithItems']);
    Route::post('/officer/sales', [App\Http\Controllers\OfficerApiController::class, 'apiCreateSale']);
    Route::get('/officer/sales/{id}', [App\Http\Controllers\OfficerApiController::class, 'apiGetSale']);
    Route::get('/officer/sales/{id}/invoice', [App\Http\Controllers\OfficerApiController::class, 'apiGetSaleInvoice']);

    // Sale Item management routes
    Route::get('/officer/sale-items', [App\Http\Controllers\OfficerApiController::class, 'apiGetSaleItems']);
    Route::get('/officer/sale-items/{id}', [App\Http\Controllers\OfficerApiController::class, 'apiGetSaleItem']);
    Route::get('/officer/sales/{sale_id}/items', [App\Http\Controllers\OfficerApiController::class, 'apiGetSaleItemsBySale']);
    Route::put('/officer/sale-items/{id}', [App\Http\Controllers\OfficerApiController::class, 'apiUpdateSaleItem']);
    Route::delete('/officer/sale-items/{id}', [App\Http\Controllers\OfficerApiController::class, 'apiDeleteSaleItem']);

    // Loan Payments management routes
    Route::get('/loan-payments', [App\Http\Controllers\Api\LoanPaymentController::class, 'index']);
    Route::post('/loan-payments', [App\Http\Controllers\Api\LoanPaymentController::class, 'store']);
    Route::get('/loan-payments/{id}', [App\Http\Controllers\Api\LoanPaymentController::class, 'show']);
    Route::put('/loan-payments/{id}', [App\Http\Controllers\Api\LoanPaymentController::class, 'update']);
    Route::delete('/loan-payments/{id}', [App\Http\Controllers\Api\LoanPaymentController::class, 'destroy']);
    Route::get('/loan-payments/statistics', [App\Http\Controllers\Api\LoanPaymentController::class, 'statistics']);
    Route::get('/loan-payments/sale/{saleId}', [App\Http\Controllers\Api\LoanPaymentController::class, 'bySale']);

    Route::get('/officer/customers', [App\Http\Controllers\OfficerApiController::class, 'apiGetCustomers']);
    Route::post('/officer/customers', [App\Http\Controllers\OfficerApiController::class, 'apiCreateCustomer']);
    Route::get('/officer/customers/{id}', [App\Http\Controllers\OfficerApiController::class, 'apiGetCustomer']);
    Route::put('/officer/customers/{id}', [App\Http\Controllers\OfficerApiController::class, 'apiUpdateCustomer']);

    // Tenant Account management routes
    Route::get('/officer/tenant-account', [App\Http\Controllers\OfficerApiController::class, 'apiGetTenantAccount']);
    Route::put('/officer/tenant-account', [App\Http\Controllers\OfficerApiController::class, 'apiUpdateTenantAccount']);
    Route::get('/officer/tenant-account/logo', [App\Http\Controllers\OfficerApiController::class, 'apiGetTenantLogo']);
    Route::post('/officer/tenant-account/logo', [App\Http\Controllers\OfficerApiController::class, 'apiUploadTenantLogo']);

    // Tenant details API
    Route::get('/tenant/details', [App\Http\Controllers\TenantController::class, 'apiGetDetails']);
    Route::get('/tenant/duka/{duka_id}/products', [App\Http\Controllers\TenantController::class, 'apiGetDukaProducts']);
    Route::post('/tenant/duka', [App\Http\Controllers\TenantController::class, 'apiCreateDuka']);
    Route::get('/tenant/duka/{duka_id}/plan', [App\Http\Controllers\TenantController::class, 'apiGetDukaPlan']);
    Route::get('/tenant/officers', [App\Http\Controllers\TenantController::class, 'apiGetOfficers']);
    Route::post('/tenant/officer', [App\Http\Controllers\TenantController::class, 'apiCreateOfficer']);
    Route::put('/tenant/officer/{officer_id}', [App\Http\Controllers\TenantController::class, 'apiUpdateOfficer']);
    Route::delete('/tenant/officer/{officer_id}', [App\Http\Controllers\TenantController::class, 'apiDeleteOfficer']);
    Route::get('/tenant/account', [App\Http\Controllers\TenantController::class, 'apiGetTenantAccount']);
    Route::post('/tenant/account', [App\Http\Controllers\TenantController::class, 'apiCreateOrUpdateTenantAccount']);

    // Plans management routes

    // Route::post('/tenant/plans', [App\Http\Controllers\TenantController::class, 'apiCreatePlan']);
    // Route::put('/tenant/plans/{plan_id}', [App\Http\Controllers\TenantController::class, 'apiUpdatePlan']);
    // Route::delete('/tenant/plans/{plan_id}', [App\Http\Controllers\TenantController::class, 'apiDeletePlan']);

    // Features management routes
    Route::get('/tenant/features', [App\Http\Controllers\TenantController::class, 'apiGetFeatures']);
    Route::post('/tenant/features', [App\Http\Controllers\TenantController::class, 'apiCreateFeature']);
    Route::put('/tenant/features/{feature_id}', [App\Http\Controllers\TenantController::class, 'apiUpdateFeature']);
    Route::delete('/tenant/features/{feature_id}', [App\Http\Controllers\TenantController::class, 'apiDeleteFeature']);


    Route::get('/tenant/tenant-account', [App\Http\Controllers\TenantController::class, 'tenantaccount']);
    Route::get('/tenant/dukasproducts/{productId}', [App\Http\Controllers\TenantController::class, 'getproudctinfindetails']);

    // Product CRUD operations for tenants
    Route::get('/tenant/products', [App\Http\Controllers\TenantController::class, 'apiListProducts']);
    Route::get('/tenant/products/{productId}', [App\Http\Controllers\TenantController::class, 'apiShowProduct']);
    Route::post('/tenant/products', [App\Http\Controllers\TenantController::class, 'apiCreateProduct']);
    Route::put('/tenant/products/{productId}', [App\Http\Controllers\TenantController::class, 'apiUpdateProduct']);
    Route::delete('/tenant/products/{productId}', [App\Http\Controllers\TenantController::class, 'apiDeleteProduct']);

    // Duka Overview and Analytics
    Route::get('/tenant/duka/{dukaId}/overview', [App\Http\Controllers\TenantController::class, 'apiGetDukaOverview']);

    // Duka Subscriptions management routes
    Route::get('/tenant/duka-subscriptions', [App\Http\Controllers\TenantController::class, 'apiGetDukaSubscriptions']);
    Route::post('/tenant/duka-subscription', [App\Http\Controllers\TenantController::class, 'apiCreateDukaSubscription']);
    Route::get('/tenant/duka-subscription/{subscription_id}', [App\Http\Controllers\TenantController::class, 'apiGetDukaSubscription']);
    Route::put('/tenant/duka-subscription/{subscription_id}', [App\Http\Controllers\TenantController::class, 'apiUpdateDukaSubscription']);
    Route::delete('/tenant/duka-subscription/{subscription_id}', [App\Http\Controllers\TenantController::class, 'apiDeleteDukaSubscription']);
    Route::get('/user/permissions', [App\Http\Controllers\UserPermissionController::class, 'index']);

    // Cash Flow routes
    Route::get('/cash-flow/product-stock', [App\Http\Controllers\CashFlowController::class, 'getProductStock']);
    Route::get('/cash-flow/product-sales', [App\Http\Controllers\CashFlowController::class, 'getProductSales']);
});
