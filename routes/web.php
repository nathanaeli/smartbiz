<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DukaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\OfficerLoanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProformaInvoiceController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\TenantCashFlowController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ReportAnalysisController;




Route::get('/', [LandingPageController::class, 'index'])->name('welcome');

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, config('app.locales'))) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('lang.switch');

Route::get('/policies', function () {
    return view('policies');
})->name('policies');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/contact', [App\Http\Controllers\ContactController::class, 'index'])->name('contact');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit'])->name('contact.submit');

// Admin routes for viewing contacts
Route::middleware(['auth', 'super-admin'])->group(function () {
    Route::get('/super-admin/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('super-admin.contacts.index');
    Route::match(['post', 'patch'], '/super-admin/contacts/bulk-action', [App\Http\Controllers\ContactController::class, 'bulkAction'])->name('super-admin.contacts.bulk-action');
    Route::get('/super-admin/contacts/{contact}', [App\Http\Controllers\ContactController::class, 'show'])->name('super-admin.contacts.show');
    Route::post('/super-admin/contacts/{contact}/reply', [App\Http\Controllers\ContactController::class, 'reply'])->name('super-admin.contacts.reply');
    Route::post('/super-admin/contacts/{contact}/mark-read', [App\Http\Controllers\ContactController::class, 'markAsRead'])->name('super-admin.contacts.mark-read');
});
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// routes/web.php
Route::get('/register/{plan?}', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// API-based Password Reset Routes (for email links)
Route::get('/reset-password', [App\Http\Controllers\ApiPasswordResetController::class, 'showResetForm'])->name('api.password.reset.form');
Route::post('/reset-password', [App\Http\Controllers\ApiPasswordResetController::class, 'reset'])->name('api.password.reset.update');

// Debug Route for Registration Testing
Route::get('/debug-registration', function() {
    return view('debug.registration-test');
})->name('debug.registration');

Route::middleware(['auth', 'super-admin'])->group(function () {
    Route::patch('/super-admin/available-permissions/{id}/assign-feature', [App\Http\Controllers\AvailablePermissionController::class, 'assignFeature'])
        ->name('super-admin.available-permissions.assign-feature');
    Route::get('/super-admin/dashboard', [App\Http\Controllers\SuperAdminController::class, 'dashboard'])->name('super-admin.dashboard');
    Route::resource('super-admin/available-permissions', App\Http\Controllers\AvailablePermissionController::class, ['as' => 'super-admin']);
    Route::get('/super-admin/dashboard', [App\Http\Controllers\SuperAdminController::class, 'dashboard'])->name('super-admin.dashboard');
    Route::get('/super-admin/tenants', [App\Http\Controllers\SuperAdminController::class, 'tenants'])->name('super-admin.tenants.index');
    Route::get('/super-admin/tenants/{id}', [App\Http\Controllers\SuperAdminController::class, 'showTenant'])->name('super-admin.tenants.show');
    Route::get('/super-admin/users', [App\Http\Controllers\SuperAdminController::class, 'users'])->name('super-admin.users.index');
    Route::get('/super-admin/users/{userId}/edit', [App\Http\Controllers\SuperAdminController::class, 'editUser'])->name('super-admin.users.edit');
    Route::put('/super-admin/users/{userId}', [App\Http\Controllers\SuperAdminController::class, 'updateUser'])->name('super-admin.users.update');
    Route::delete('/super-admin/users/{userId}', [App\Http\Controllers\SuperAdminController::class, 'deleteUser'])->name('super-admin.users.destroy');
    Route::delete('/super-admin/users', [App\Http\Controllers\SuperAdminController::class, 'bulkDeleteUsers'])->name('super-admin.users.bulk-delete');
    Route::post('/super-admin/users/{userId}/toggle-status', [App\Http\Controllers\SuperAdminController::class, 'toggleUserStatus'])->name('super-admin.users.toggle-status');
    Route::post('/super-admin/users/{userId}/reset-password', [App\Http\Controllers\SuperAdminController::class, 'resetUserPassword'])->name('super-admin.users.reset-password');

    // Features Management
    Route::get('/super-admin/features', [App\Http\Controllers\SuperAdminController::class, 'features'])->name('super-admin.features.index');
    Route::get('/super-admin/features/create', [App\Http\Controllers\SuperAdminController::class, 'createFeature'])->name('super-admin.features.create');
    Route::post('/super-admin/features', [App\Http\Controllers\SuperAdminController::class, 'storeFeature'])->name('super-admin.features.store');
    Route::get('/super-admin/features/{id}', [App\Http\Controllers\SuperAdminController::class, 'showFeature'])->name('super-admin.features.show');
    Route::get('/super-admin/features/{id}/edit', [App\Http\Controllers\SuperAdminController::class, 'editFeature'])->name('super-admin.features.edit');
    Route::put('/super-admin/features/{id}', [App\Http\Controllers\SuperAdminController::class, 'updateFeature'])->name('super-admin.features.update');
    Route::post('/super-admin/features/{id}/assign-plans', [App\Http\Controllers\SuperAdminController::class, 'assignFeatureToPlans'])->name('super-admin.features.assign-plans');
    Route::delete('/super-admin/features/{id}', [App\Http\Controllers\SuperAdminController::class, 'destroyFeature'])->name('super-admin.features.destroy');

    // Plans Management
    Route::get('/super-admin/plans', [App\Http\Controllers\SuperAdminController::class, 'plans'])->name('super-admin.plans.index');
    Route::get('/super-admin/plans/create', [App\Http\Controllers\SuperAdminController::class, 'createPlan'])->name('super-admin.plans.create');
    Route::post('/super-admin/plans', [App\Http\Controllers\SuperAdminController::class, 'storePlan'])->name('super-admin.plans.store');
    Route::get('/super-admin/plans/{id}', [App\Http\Controllers\SuperAdminController::class, 'showPlan'])->name('super-admin.plans.show');
    Route::get('/super-admin/plans/{id}/edit', [App\Http\Controllers\SuperAdminController::class, 'editPlan'])->name('super-admin.plans.edit');
    Route::put('/super-admin/plans/{id}', [App\Http\Controllers\SuperAdminController::class, 'updatePlan'])->name('super-admin.plans.update');
    Route::delete('/super-admin/plans/{id}', [App\Http\Controllers\SuperAdminController::class, 'destroyPlan'])->name('super-admin.plans.destroy');

    // Dukas Management
    Route::get('/super-admin/dukas', [App\Http\Controllers\SuperAdminController::class, 'dukas'])->name('super-admin.dukas.index');
    Route::get('/super-admin/dukas/{id}', [App\Http\Controllers\SuperAdminController::class, 'showDuka'])->name('super-admin.dukas.show');
    Route::delete('/super-admin/dukas/{id}', [App\Http\Controllers\SuperAdminController::class, 'destroyDuka'])->name('super-admin.dukas.destroy');

    // Subscriptions Analytics
    Route::get('/super-admin/subscriptions', [App\Http\Controllers\SuperAdminController::class, 'subscriptions'])->name('super-admin.subscriptions.index');
    Route::get('/super-admin/subscriptions/analytics', [App\Http\Controllers\SuperAdminController::class, 'subscriptionAnalytics'])->name('super-admin.subscriptions.analytics');

    // Messages Management
    Route::get('/super-admin/messages', [App\Http\Controllers\SuperAdminController::class, 'messages'])->name('super-admin.messages.index');
    Route::get('/super-admin/messages/create', [App\Http\Controllers\SuperAdminController::class, 'createMessage'])->name('super-admin.messages.create');
    Route::post('/super-admin/messages', [App\Http\Controllers\SuperAdminController::class, 'storeMessage'])->name('super-admin.messages.store');
    Route::get('/super-admin/messages/{id}', [App\Http\Controllers\SuperAdminController::class, 'showMessage'])->name('super-admin.messages.show');
    Route::post('/super-admin/messages/{id}/mark-read', [App\Http\Controllers\SuperAdminController::class, 'markMessageAsRead'])->name('super-admin.messages.mark-read');
    Route::post('/super-admin/messages/{id}/reply', [App\Http\Controllers\SuperAdminController::class, 'replyToMessage'])->name('super-admin.messages.reply');

    // Customers Overview
    Route::get('/super-admin/customers', [App\Http\Controllers\SuperAdminController::class, 'customers'])->name('super-admin.customers.index');
    Route::get('/super-admin/customers/{id}', [App\Http\Controllers\SuperAdminController::class, 'showCustomer'])->name('super-admin.customers.show');

    // Telescope Management
    Route::get('/super-admin/telescope', [App\Http\Controllers\SuperAdminController::class, 'telescope'])->name('super-admin.telescope.index');
    Route::get('/super-admin/telescope/{id}', [App\Http\Controllers\SuperAdminController::class, 'showTelescopeEntry'])->name('super-admin.telescope.show');
    Route::post('/super-admin/telescope/clear', [App\Http\Controllers\SuperAdminController::class, 'clearTelescopeEntries'])->name('super-admin.telescope.clear');
    Route::post('/super-admin/telescope/bulk-delete', [App\Http\Controllers\SuperAdminController::class, 'bulkDeleteTelescopeEntries'])->name('super-admin.telescope.bulk-delete');
    Route::get('/super-admin/telescope/export/json', [App\Http\Controllers\SuperAdminController::class, 'exportTelescopeEntries'])->name('super-admin.telescope.export');
    Route::get('/super-admin/telescope/stats/live', [App\Http\Controllers\SuperAdminController::class, 'getTelescopeStats'])->name('super-admin.telescope.stats');

    // Backup Management
    Route::get('/super-admin/backups', [App\Http\Controllers\SuperAdminController::class, 'backups'])->name('super-admin.backups.index');
    Route::post('/super-admin/backups/create', [App\Http\Controllers\SuperAdminController::class, 'createBackup'])->name('super-admin.backups.create');
    Route::get('/super-admin/backups/download/{filename}', [App\Http\Controllers\SuperAdminController::class, 'downloadBackup'])->name('super-admin.backups.download');
    Route::delete('/super-admin/backups/delete/{filename}', [App\Http\Controllers\SuperAdminController::class, 'deleteBackup'])->name('super-admin.backups.delete');

    // Available Permissions Management
    Route::resource('super-admin/available-permissions', App\Http\Controllers\AvailablePermissionController::class, ['as' => 'super-admin']);

    Route::get('super-admin/available-permissions/download-sample', [App\Http\Controllers\AvailablePermissionController::class, 'downloadSample'])->name('super-admin.available-permissions.download-sample');

    // Settings Management
    Route::get('/super-admin/settings', [App\Http\Controllers\SuperAdminController::class, 'settings'])->name('super-admin.settings.index');
    Route::post('/super-admin/settings/bulk-set-password', [App\Http\Controllers\SuperAdminController::class, 'bulkSetPassword'])->name('super-admin.settings.bulk-set-password');
    Route::put('/super-admin/settings/tenant/{tenantId}/set-password', [App\Http\Controllers\SuperAdminController::class, 'setTenantPassword'])->name('super-admin.settings.set-tenant-password');

    // Profile Management
    Route::get('/super-admin/profile', [App\Http\Controllers\SuperAdminController::class, 'profile'])->name('super-admin.profile');
    Route::post('/super-admin/profile/update', [App\Http\Controllers\SuperAdminController::class, 'updateProfile'])->name('super-admin.profile.update');
});

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.update');
    Route::get('/duka/create-plan', [DukaController::class, 'createWithPlan'])->name('duka.create.plan');
    Route::post('/duka/create-with-plan', [DukaController::class, 'storecreateduka'])->name('duka.store.with.plan');
    Route::get('/duka/{encrypted_id}/change-plan', [DukaController::class, 'changePlan'])->name('duka.change.plan');
    Route::post('/duka/{encrypted_id}/update-plan', [DukaController::class, 'updatePlan'])->name('duka.update.plan');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('tenant.dashboard');
    Route::post('/duka/store', [DukaController::class, 'store'])->name('duka.store');
    Route::get('/duka/{id}', [App\Http\Controllers\DukaController::class, 'showduka'])->name('duka.show');
    Route::get('/duka/{id}/edit', [App\Http\Controllers\DukaController::class, 'edit'])->name('duka.edit');
    Route::put('/duka/{id}', [App\Http\Controllers\DukaController::class, 'update'])->name('duka.update');
    Route::get('/duka/{id}/aging-analysis', [DukaController::class, 'loanAgingAnalysis'])->name('duka.aging.analysis');
    Route::post('/duka/{duka_id}/send-loan-reminder', [DukaController::class, 'sendLoanReminder'])->name('duka.send.loan.reminder');
    Route::post('/duka/{duka_id}/send-bulk-reminders', [DukaController::class, 'sendBulkLoanReminders'])->name('duka.send.bulk.reminders');
    Route::get('/duka/{encrypted_id}', [DukaController::class, 'showduka'])->name('duka.show');
    Route::get('/tenant/dukas', [App\Http\Controllers\DukaController::class, 'alldukas'])->name('tenant.dukas.index');
    Route::get('/categories', [ProductCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [ProductCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories/store', [ProductCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [ProductCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [ProductCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [ProductCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::put('/tenant/products/{encrypted}', [ProductController::class, 'update'])->name('tenant.product.update');
    Route::post('/stocks/store', [StockController::class, 'store'])->name('stocks.store');
    Route::put('/stocks/{id}', [StockController::class, 'update'])->name('stocks.update');
    Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/tenant/products/{encrypted}', [ProductController::class, 'manage'])->name('tenant.product.manage');
    Route::put('/tenant/products/{encrypted}', [ProductController::class, 'update'])->name('tenant.product.update');
    Route::get('/accountsetup', [App\Http\Controllers\AccountController::class, 'index'])->name('accountsetup');
    Route::get('/cashflow', [TenantCashFlowController::class, 'index'])->name('tenant.cashflow.index');
    Route::post('/cashflow/store', [TenantCashFlowController::class, 'store'])->name('tenant.cashflow.store');
    Route::get('/reports/profit-loss', [TenantCashFlowController::class, 'profitAndLoss'])->name('tenant.reports.pl');


    // Test routes for debugging
    Route::get('/test/product', [App\Http\Controllers\TestProductController::class, 'test'])->name('test.product');
    Route::get('/test/product-create', [App\Http\Controllers\TestProductController::class, 'testProductCreation'])->name('test.product.create');
    Route::get('/reports/consolidated-profit-loss', [TenantCashFlowController::class, 'consolidatedProfitLoss'])->name('tenant.reports.consolidated_pl');

    // Stock Movement Trends
    Route::get('/tenant/stock-trends', [App\Http\Controllers\StockMovementTrendController::class, 'index'])->name('tenant.stock-trends.index');
    Route::get('/tenant/stock-trends/product/{encrypted}', [App\Http\Controllers\StockMovementTrendController::class, 'showProduct'])->name('tenant.stock-trends.product');
    Route::get('/tenant/stock-trends/duka/{encrypted}', [App\Http\Controllers\StockMovementTrendController::class, 'showDuka'])->name('tenant.stock-trends.duka');
    Route::get('/account/create', [App\Http\Controllers\AccountController::class, 'create'])->name('account.create');
    Route::post('/account/store', [App\Http\Controllers\AccountController::class, 'store'])->name('account.store');
    Route::get('/account/edit', [App\Http\Controllers\AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account/update', [App\Http\Controllers\AccountController::class, 'update'])->name('account.update');
    Route::put('/account/update-settings', [App\Http\Controllers\AccountController::class, 'updateSettings'])->name('account.update-settings');
    Route::delete('/account/delete', [App\Http\Controllers\AccountController::class, 'destroy'])->name('account.destroy');
    Route::get('/permissions', [App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/officer/{officerId}', [App\Http\Controllers\PermissionController::class, 'showOfficerPermissions'])->name('permissions.officer.show');
    Route::put('/permissions/officer/{officerId}', [App\Http\Controllers\PermissionController::class, 'updateOfficerPermissions'])->name('permissions.officer.update');
    Route::get('/permissions/duka/{dukaId}/plan', [App\Http\Controllers\PermissionController::class, 'checkDukaPlan'])->name('permissions.check-duka-plan');
    Route::get('/report-analysis', [App\Http\Controllers\ReportAnalysisController::class, 'index'])->name('report.analysis');
    Route::get('/profile',[App\Http\Controllers\AccountController::class, 'profile'])->name('profile');
    Route::post('/profile/update',[App\Http\Controllers\AccountController::class, 'updateProfile'])->name('profile.update');
    // Message Routes
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::get('/messages/{message}/download', [MessageController::class, 'downloadAttachment'])->name('messages.download');

    // Officer Management Routes
    Route::get('/officers', [OfficerController::class, 'index'])->name('officers.index');
    Route::get('/officers/create', [OfficerController::class, 'create'])->name('officers.create');
    Route::post('/officers', [OfficerController::class, 'store'])->name('officers.store');
    Route::get('/officers/{id}', [OfficerController::class, 'show'])->name('officers.show');
    Route::get('/officers/{id}/edit', [OfficerController::class, 'edit'])->name('officers.edit');
    Route::put('/officers/{id}', [OfficerController::class, 'update'])->name('officers.update');
    Route::put('/officers/{id}/set-default-password', [OfficerController::class, 'setDefaultPassword'])->name('officers.set-default-password');
    Route::delete('/officers/{id}', [OfficerController::class, 'destroy'])->name('officers.destroy');
    Route::get('/cashflow/consolidated', [TenantCashFlowController::class, 'consolidatedCashFlow'])->name('tenant.reports.consolidated');


    Route::get('/sales', [App\Http\Controllers\SaleController::class, 'index'])->name('sales.index');



    Route::post('/officers/assign', [OfficerController::class, 'assign'])->name('officers.assign');
    Route::delete('/officers/unassign/{id}', [OfficerController::class, 'unassign'])->name('officers.unassign');
    Route::put('/officers/update-role/{id}', [OfficerController::class, 'updateRole'])->name('officers.update-role');

    Route::get('/salenow',[ProformaInvoiceController::class,'salenow'])->name('sale.now');
    Route::get('/sale/process/{dukaId}', function ($dukaId) {
        return view('sale.process', ['dukaId' => $dukaId]);
    })->name('sale.process');


    Route::get('/sales/{id}', function ($id) {
        $sale = \App\Models\Sale::with(['customer', 'duka', 'saleItems.product', 'loanPayments.user'])->findOrFail($id);
        $user = auth()->user();
        if ($sale->tenant_id != $user->tenant->id) {
            abort(403, 'Unauthorized access.');
        }
        return view('sales.show', compact('sale'));
    })->name('sales.show');
    Route::get('/sales/{id}/invoice', function ($id) {
        $sale = \App\Models\Sale::with(['customer', 'duka', 'saleItems.product', 'tenant'])->findOrFail($id);
        $user = auth()->user();
        if ($sale->tenant_id != $user->tenant->id) {
            abort(403, 'Unauthorized access.');
        }
        $tenantAccount = \App\Models\TenantAccount::where('tenant_id', $user->tenant->id)->first();
        return view('sales.invoice', compact('sale', 'tenantAccount'));
    })->name('sales.invoice');
    Route::get('/sales/{id}/edit', [App\Http\Controllers\SaleController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{id}', [App\Http\Controllers\SaleController::class, 'update'])->name('sales.update');
    Route::post('/sales/{saleId}/loan-payments', [App\Http\Controllers\LoanPaymentController::class, 'store'])->name('loan.payments.store');
    // Proforma Invoice Routes
    Route::group(['prefix' => 'proforma-invoices', 'as' => 'proforma.'], function () {
        Route::get('/duka', [ProformaInvoiceController::class, 'index'])->name('index');
        Route::get('/create/{dukaId}', [ProformaInvoiceController::class, 'createForDuka'])->name('create.for.duka');
        Route::post('/store', [ProformaInvoiceController::class, 'store'])->name('store');
        Route::get('/{id}/pdf', [ProformaInvoiceController::class, 'generatePdf'])->name('pdf');
        Route::get('/preview/temp', [ProformaInvoiceController::class, 'previewTemp'])->name('preview.temp');
        Route::get('/sale-now', [ProformaInvoiceController::class, 'salenow'])->name('salenow');
    });

    // Stock Transfer Routes
    Route::get('/tenant/stock-transfers/create', [App\Http\Controllers\StockTransferController::class, 'create'])->name('tenant.stock-transfers.create');
    Route::post('/tenant/stock-transfers', [App\Http\Controllers\StockTransferController::class, 'store'])->name('tenant.stock-transfers.store');





});

// Officer Routes
Route::middleware(['auth', 'officer'])->group(function () {
    Route::get('/ping', function () {
        return response()->json(['status' => 'ok']);
    });
    Route::get('/officer/dashboard', [App\Http\Controllers\OfficerController::class, 'dashboard'])->name('officer.dashboard');
    Route::get('/officer/profile', [App\Http\Controllers\OfficerController::class, 'profile'])->name('officer.profile');
    Route::post('/officer/profile/update', [App\Http\Controllers\OfficerController::class, 'updateProfile'])->name('officer.profile.update');
    Route::get('/officer/proformainvoice', [App\Http\Controllers\OfficerController::class, 'proformaInvoice'])->name('officer.proformainvoice');
    Route::get('/officer/proformainvoice/preview', [App\Http\Controllers\OfficerController::class, 'proformaInvoicePreview'])->name('officer.proforma-invoice.preview');


    Route::get('/allproduct',[App\Http\Controllers\OfficerController::class,'manageproduct'])->name('manageproduct');
    Route::get('/products/import', [App\Http\Controllers\OfficerController::class, 'importProducts'])->name('officer.products.import');
    Route::post('/products/import', [App\Http\Controllers\OfficerController::class, 'processImport'])->name('officer.products.import.process');
    Route::get('/products/create', [App\Http\Controllers\OfficerController::class, 'showCreateProduct'])->name('officer.product.create');
    Route::post('/products/store', [App\Http\Controllers\OfficerController::class, 'storeProduct'])->name('officer.products.store');
    Route::get('/products/{product}/edit', [App\Http\Controllers\OfficerController::class, 'showEditProduct'])->name('officer.product.edit');
    Route::get('/products/{product}/stock', [App\Http\Controllers\OfficerController::class, 'manageStock'])->name('officer.product.stock');
    Route::get('/products/{product}/items', [App\Http\Controllers\OfficerController::class, 'viewProductItems'])->name('officer.product.items');
    Route::post('/officer/product-items/update-status', [App\Http\Controllers\OfficerController::class, 'updateProductItemStatus'])->name('officer.product-items.update-status');
    Route::get('/officer/products/export', [App\Http\Controllers\OfficerController::class, 'exportProducts'])->name('officer.products.export');
    // Customer Management for Officers
    Route::get('/officer/customers', [App\Http\Controllers\OfficerController::class, 'manageCustomers'])->name('officer.customers.manage');
    Route::get('/officer/customers/import', [App\Http\Controllers\OfficerController::class, 'importCustomers'])->name('officer.customers.import');
    Route::post('/officer/customers/import', [App\Http\Controllers\OfficerController::class, 'processCustomerImport'])->name('officer.customers.import.process');
    Route::post('/officer/customers', [App\Http\Controllers\OfficerController::class, 'storeCustomer'])->name('officer.customers.store');
    Route::put('/officer/customers/{id}', [App\Http\Controllers\OfficerController::class, 'updateCustomer'])->name('officer.customers.update');
    Route::delete('/officer/customers/{id}', [App\Http\Controllers\OfficerController::class, 'destroyCustomer'])->name('officer.customers.destroy');

    // Category Management for Officers
    Route::get('/officer/categories', [App\Http\Controllers\OfficerController::class, 'manageCategories'])->name('officer.categories.manage');
    Route::get('/officer/categories/import', [App\Http\Controllers\OfficerController::class, 'importCategories'])->name('officer.categories.import');
    Route::post('/officer/categories/import', [App\Http\Controllers\OfficerController::class, 'processCategoryImport'])->name('officer.categories.import.process');
    Route::post('/officer/categories', [App\Http\Controllers\OfficerController::class, 'storeCategory'])->name('officer.categories.store');
    Route::put('/officer/categories/{id}', [App\Http\Controllers\OfficerController::class, 'updateCategory'])->name('officer.categories.update');
    Route::delete('/officer/categories/{id}', [App\Http\Controllers\OfficerController::class, 'destroyCategory'])->name('officer.categories.destroy');
    Route::get('/officer/products/{id}/edit', [App\Http\Controllers\OfficerController::class, 'editProduct'])->name('officer.products.edit');
    Route::put('/officer/products/{id}', [App\Http\Controllers\OfficerController::class, 'updateProduct'])->name('officer.products.update');
    Route::delete('/officer/products/{id}', [App\Http\Controllers\OfficerController::class, 'destroyProduct'])->name('officer.products.destroy');

    //sales manaegnment
    Route::get('/officer/sales',[App\Http\Controllers\SalesofficerControllers::class, 'officersalesstocks'])->name('officer.sales');
    Route::get('/officer/sales/{id}/invoice', function ($id) {
        $sale = \App\Models\Sale::with(['customer', 'duka', 'saleItems.product', 'tenant'])->findOrFail($id);
        $user = auth()->user();

        // Check if officer has access to this sale
        $dukaIds = \App\Models\TenantOfficer::where('officer_id', $user->id)->where('status', true)->pluck('duka_id');
        if (!$dukaIds->contains($sale->duka_id)) {
            abort(403, 'Unauthorized access to this sale.');
        }

        $tenantAccount = \App\Models\TenantAccount::where('tenant_id', $sale->tenant_id)->first();
        return view('sales.invoice', compact('sale', 'tenantAccount'));
    })->name('officer.sales.invoice');

    // Loan Management
    Route::get('/officer/loanmanagement', [OfficerLoanController::class, 'index'])->name('officer.loanmanagement');
    Route::get('/officer/loans/{id}', [OfficerLoanController::class, 'show'])->name('officer.loans.show');
    Route::post('/officer/loans/{loanId}/payments', [OfficerLoanController::class, 'storePayment'])->name('officer.loans.payments.store');
    Route::get('/officer/loan-aging-analysis', [OfficerLoanController::class, 'agingAnalysis'])->name('officer.loan.aging.analysis');

    // Stock Management for Officers
    Route::post('/officer/stocks/add', [App\Http\Controllers\OfficerController::class, 'addStock'])->name('officer.stocks.add');
    Route::post('/officer/stocks/reduce', [App\Http\Controllers\OfficerController::class, 'reduceStock'])->name('officer.stocks.reduce');
    Route::put('/officer/stocks/{id}', [App\Http\Controllers\OfficerController::class, 'updateStock'])->name('officer.stocks.update');

    // QR Code Scanning
    Route::post('/scan-qr', [ProductController::class, 'scanQr'])->name('scan.qr');

    // Report Analysis
    Route::get('/report-analysis', [App\Http\Controllers\ReportAnalysisController::class, 'index'])->name('report.analysis');



});

Route::get('/payment/checkout/{tenant}/{subscription}', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');
