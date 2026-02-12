<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApiPasswordResetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailablePermissionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DukaController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\LoanPaymentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\OfficerLoanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProformaInvoiceController;
use App\Http\Controllers\ReportAnalysisController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesofficerControllers;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementTrendController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TenantCashFlowController;
use App\Http\Controllers\TestProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==============================================================================
// PUBLIC & STATIC PAGES
// ==============================================================================
Route::get('/', [LandingPageController::class, 'index'])->name('welcome');

// Language Switcher
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, config('app.locales'))) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('lang.switch');

// Legal & Static Pages
Route::view('/policies', 'policies')->name('policies');
Route::view('/about', 'about')->name('about');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/terms', 'terms')->name('terms');

// Contact Form
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Ping (Used by Officer App check)
Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});


// ==============================================================================
// AUTHENTICATION
// ==============================================================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/coming-soon', function () {
    return view('coming-soon');
})->name('coming-soon');

Route::get('/register/{plan?}', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Web-based Password Reset
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// API-based Password Reset (Email Links)
Route::get('/api/reset-password', [ApiPasswordResetController::class, 'showResetForm'])->name('api.password.reset.form');
Route::post('/api/reset-password', [ApiPasswordResetController::class, 'reset'])->name('api.password.reset.update');

// Debug Route (Registration Testing)
Route::get('/debug-registration', function () {
    return view('debug.registration-test');
})->name('debug.registration');


// ==============================================================================
// SUPER ADMIN ROUTES
// ==============================================================================
Route::prefix('super-admin')->middleware(['auth', 'super-admin'])->as('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // Contact Management
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::match(['post', 'patch'], '/contacts/bulk-action', [ContactController::class, 'bulkAction'])->name('contacts.bulk-action');
    Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::post('/contacts/{contact}/reply', [ContactController::class, 'reply'])->name('contacts.reply');
    Route::post('/contacts/{contact}/mark-read', [ContactController::class, 'markAsRead'])->name('contacts.mark-read');

    // User Management
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users.index');
    Route::get('/users/{userId}/edit', [SuperAdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{userId}', [SuperAdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{userId}', [SuperAdminController::class, 'deleteUser'])->name('users.destroy');
    Route::delete('/users', [SuperAdminController::class, 'bulkDeleteUsers'])->name('users.bulk-delete');
    Route::post('/users/{userId}/toggle-status', [SuperAdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::post('/users/{userId}/reset-password', [SuperAdminController::class, 'resetUserPassword'])->name('users.reset-password');

    // Tenants & Dukas
    Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('tenants.index');
    Route::get('/tenants/{id}', [SuperAdminController::class, 'showTenant'])->name('tenants.show');
    Route::get('/dukas', [SuperAdminController::class, 'dukas'])->name('dukas.index');
    Route::get('/dukas/{id}', [SuperAdminController::class, 'showDuka'])->name('dukas.show');
    Route::delete('/dukas/{id}', [SuperAdminController::class, 'destroyDuka'])->name('dukas.destroy');

    // Features & Plans
    Route::resource('available-permissions', AvailablePermissionController::class, ['except' => ['show']]);
    Route::patch('/available-permissions/{id}/assign-feature', [AvailablePermissionController::class, 'assignFeature'])->name('available-permissions.assign-feature');
    Route::get('/available-permissions/download-sample', [AvailablePermissionController::class, 'downloadSample'])->name('available-permissions.download-sample');

    Route::resource('features', SuperAdminController::class)->names('features')->except(['index', 'show']);
    Route::get('/features', [SuperAdminController::class, 'features'])->name('features.index'); // Custom method map?
    Route::get('/features/{id}', [SuperAdminController::class, 'showFeature'])->name('features.show');
    Route::get('/features/create', [SuperAdminController::class, 'createFeature'])->name('features.create'); // Explicit
    Route::post('/features/{id}/assign-plans', [SuperAdminController::class, 'assignFeatureToPlans'])->name('features.assign-plans');

    Route::resource('plans', SuperAdminController::class)->names('plans')->except(['index', 'show']);
    Route::get('/plans', [SuperAdminController::class, 'plans'])->name('plans.index');
    Route::get('/plans/{id}', [SuperAdminController::class, 'showPlan'])->name('plans.show');

    // Subscriptions/Messages/Backups/Telescope
    Route::get('/subscriptions', [SuperAdminController::class, 'subscriptions'])->name('subscriptions.index');
    Route::get('/subscriptions/analytics', [SuperAdminController::class, 'subscriptionAnalytics'])->name('subscriptions.analytics');

    Route::resource('messages', SuperAdminController::class)->names('messages')->except(['index', 'show']);
    Route::get('/messages', [SuperAdminController::class, 'messages'])->name('messages.index');
    Route::get('/messages/{id}', [SuperAdminController::class, 'showMessage'])->name('messages.show');
    Route::post('/messages/{id}/mark-read', [SuperAdminController::class, 'markMessageAsRead'])->name('messages.mark-read');
    Route::post('/messages/{id}/reply', [SuperAdminController::class, 'replyToMessage'])->name('messages.reply');

    Route::get('/customers', [SuperAdminController::class, 'customers'])->name('customers.index');
    Route::get('/customers/{id}', [SuperAdminController::class, 'showCustomer'])->name('customers.show');

    Route::get('/telescope', [SuperAdminController::class, 'telescope'])->name('telescope.index');
    Route::get('/telescope/{id}', [SuperAdminController::class, 'showTelescopeEntry'])->name('telescope.show');
    Route::post('/telescope/clear', [SuperAdminController::class, 'clearTelescopeEntries'])->name('telescope.clear');
    Route::post('/telescope/bulk-delete', [SuperAdminController::class, 'bulkDeleteTelescopeEntries'])->name('telescope.bulk-delete');
    Route::get('/telescope/export/json', [SuperAdminController::class, 'exportTelescopeEntries'])->name('telescope.export');
    Route::get('/telescope/stats/live', [SuperAdminController::class, 'getTelescopeStats'])->name('telescope.stats');

    Route::get('/backups', [SuperAdminController::class, 'backups'])->name('backups.index');
    Route::post('/backups/create', [SuperAdminController::class, 'createBackup'])->name('backups.create');
    Route::get('/backups/download/{filename}', [SuperAdminController::class, 'downloadBackup'])->name('backups.download');
    Route::delete('/backups/delete/{filename}', [SuperAdminController::class, 'deleteBackup'])->name('backups.delete');

    // Settings & Profile
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings.index');
    Route::post('/settings/bulk-set-password', [SuperAdminController::class, 'bulkSetPassword'])->name('settings.bulk-set-password');
    Route::put('/settings/tenant/{tenantId}/set-password', [SuperAdminController::class, 'setTenantPassword'])->name('settings.set-tenant-password');
    Route::get('/profile', [SuperAdminController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [SuperAdminController::class, 'updateProfile'])->name('profile.update');
});


// ==============================================================================
// TENANT ROUTES
// ==============================================================================
Route::middleware(['auth', 'tenant'])->group(function () {

    // Dashboard & Profile
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('tenant.dashboard');
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.update');

    // Profile
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AccountController::class, 'updateProfile'])->name('profile.update');

    // Account & Settings
    Route::get('/accountsetup', [AccountController::class, 'index'])->name('accountsetup');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/create', [AccountController::class, 'create'])->name('create');
        Route::post('/store', [AccountController::class, 'store'])->name('store');
        Route::get('/edit', [AccountController::class, 'edit'])->name('edit');
        Route::put('/update', [AccountController::class, 'update'])->name('update');
        Route::put('/update-settings', [AccountController::class, 'updateSettings'])->name('update-settings');
        Route::delete('/delete', [AccountController::class, 'destroy'])->name('destroy');
    });

    // Duka Management
    Route::prefix('duka')->name('duka.')->group(function () {
        Route::get('/create-plan', [DukaController::class, 'createWithPlan'])->name('create.plan');
        Route::post('/create-with-plan', [DukaController::class, 'storecreateduka'])->name('store.with.plan');
        Route::post('/store', [DukaController::class, 'store'])->name('store');
        Route::get('/{encrypted_id}/change-plan', [DukaController::class, 'changePlan'])->name('change.plan');
        Route::post('/{encrypted_id}/update-plan', [DukaController::class, 'updatePlan'])->name('update.plan');

        // Single Duka Handling (Using both ID and Encrypted ID in controller, harmonized here)
        Route::get('/{id}', [DukaController::class, 'showduka'])->name('show');
        Route::get('/{id}/edit', [DukaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DukaController::class, 'update'])->name('update');
        Route::get('/{id}/aging-analysis', [DukaController::class, 'loanAgingAnalysis'])->name('aging.analysis');
        Route::get('/{duka_id}/send-loan-reminder', [DukaController::class, 'sendLoanReminder'])->name('send.loan.reminder');
        Route::post('/{duka_id}/send-bulk-reminders', [DukaController::class, 'sendBulkLoanReminders'])->name('send.bulk.reminders');

        // Duka Specific Management Pages
        Route::get('/{id}/inventory', [DukaController::class, 'inventory'])->name('inventory');
        Route::get('/{id}/inventory/export/excel', [DukaController::class, 'exportInventoryExcel'])->name('inventory.export.excel');
        Route::get('/{id}/inventory/export/pdf', [DukaController::class, 'exportInventoryPdf'])->name('inventory.export.pdf');
        Route::get('/{id}/customers', [DukaController::class, 'customers'])->name('customers');
    });

    Route::get('/tenant/dukas', [DukaController::class, 'alldukas'])->name('tenant.dukas.index');

    // Products & Stocks
    Route::get('/categories', [ProductCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/import-template', [ProductCategoryController::class, 'downloadImportTemplate'])->name('categories.import-template');
    Route::post('/categories/import', [ProductCategoryController::class, 'import'])->name('categories.import');
    Route::resource('categories', ProductCategoryController::class)->except(['index']);

    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::prefix('tenant/products')->name('tenant.product.')->group(function () {
        Route::get('/{encrypted}', [ProductController::class, 'manage'])->name('manage');
        Route::put('/{encrypted}', [ProductController::class, 'update'])->name('update');
    });

    Route::post('/stocks/store', [StockController::class, 'store'])->name('stocks.store');
    Route::put('/stocks/{id}', [StockController::class, 'update'])->name('stocks.update');

    // Customers
    Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/import-template', [CustomerController::class, 'downloadImportTemplate'])->name('customers.import-template');

    // Sales & Transactions
    Route::get('/sales/history', [SaleController::class, 'index'])->name('sales.index'); // Renamed original index to history or kept distinct
    Route::get('/sales/history/export', [SaleController::class, 'exportSales'])->name('sales.export');
    Route::get('/sales/history/summary/excel', [SaleController::class, 'exportSummaryExcel'])->name('sales.summary.excel');
    Route::get('/sales/history/summary/pdf', [SaleController::class, 'exportSummaryPdf'])->name('sales.summary.pdf');

    // Import Rollback & History
    Route::get('/imports/history', [\App\Http\Controllers\ImportHistoryController::class, 'index'])->name('imports.index');
    Route::delete('/imports/rollback/{batchId}', [\App\Http\Controllers\ImportHistoryController::class, 'rollback'])->name('imports.rollback');
    Route::post('/imports/cleanup-legacy', [\App\Http\Controllers\ImportHistoryController::class, 'cleanupLegacy'])->name('imports.cleanup_legacy');

    Route::get('/sales-pos', [SaleController::class, 'smartIndex'])->name('tenant.sales.index');
    // Standard Blade POS Routes
    Route::get('/sale/process/{dukaId}', [SaleController::class, 'process'])->name('sale.process');
    Route::post('/sale/process/{dukaId}/add-to-cart', [SaleController::class, 'addToCart'])->name('sale.add_to_cart');
    Route::post('/sale/process/{dukaId}/remove-from-cart/{productId}', [SaleController::class, 'removeFromCart'])->name('sale.remove_from_cart');
    Route::post('/sale/process/{dukaId}/clear-cart', [SaleController::class, 'clearCart'])->name('sale.clear_cart');
    Route::post('/sale/process/{dukaId}/checkout', [SaleController::class, 'checkout'])->name('sale.checkout');
    Route::post('/sale/process/{dukaId}/import', [SaleController::class, 'importSales'])->name('sale.import');
    Route::get('/sale/process/{dukaId}/template', [SaleController::class, 'downloadTemplate'])->name('sale.download_template');
    Route::get('/sale/import/instructions', [SaleController::class, 'downloadImportInstructions'])->name('sale.import_instructions');

    // Specific Sale with Logic
    Route::get('/sales/{id}', function ($id) {
        $sale = \App\Models\Sale::with(['customer', 'duka', 'saleItems.product', 'loanPayments.user'])->findOrFail($id);
        if ($sale->tenant_id != auth()->user()->tenant->id) abort(403);
        return view('sales.show', compact('sale'));
    })->name('sales.show');

    Route::get('/sales/{id}/invoice', function ($id) {
        $sale = \App\Models\Sale::with(['customer', 'duka', 'saleItems.product', 'tenant'])->findOrFail($id);
        $user = auth()->user();
        if ($sale->tenant_id != $user->tenant->id) abort(403);
        $tenantAccount = \App\Models\TenantAccount::where('tenant_id', $user->tenant->id)->first();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.invoice', compact('sale', 'tenantAccount'));
        return $pdf->download('invoice_' . $sale->id . '.pdf');
    })->name('sales.invoice');

    Route::get('/sales/{id}/edit', [SaleController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{id}', [SaleController::class, 'update'])->name('sales.update');
    Route::post('/sales/{saleId}/loan-payments', [LoanPaymentController::class, 'store'])->name('loan.payments.store');

    // Proforma Invoices
    Route::prefix('proforma-invoices')->as('proforma.')->group(function () {
        Route::get('/duka', [ProformaInvoiceController::class, 'index'])->name('index');
        Route::get('/create/{dukaId}', [ProformaInvoiceController::class, 'createForDuka'])->name('create.for.duka');
        Route::post('/store', [ProformaInvoiceController::class, 'store'])->name('store');
        Route::get('/{id}/pdf', [ProformaInvoiceController::class, 'generatePdf'])->name('pdf');
        Route::get('/preview/temp', [ProformaInvoiceController::class, 'previewTemp'])->name('preview.temp');
        Route::get('/sale-now', [ProformaInvoiceController::class, 'salenow'])->name('salenow');
    });

    // Cashflow & Reports
    Route::get('/cashflow', [TenantCashFlowController::class, 'index'])->name('tenant.cashflow.index');
    Route::get('/cashflow/consolidated', [TenantCashFlowController::class, 'consolidatedCashFlow'])->name('tenant.reports.consolidated');
    Route::post('/cashflow/store', [TenantCashFlowController::class, 'store'])->name('tenant.cashflow.store');
    Route::get('/reports/profit-loss', [TenantCashFlowController::class, 'profitAndLoss'])->name('tenant.reports.pl');
    Route::get('/reports/consolidated-profit-loss', [TenantCashFlowController::class, 'consolidatedProfitLoss'])->name('tenant.reports.consolidated_pl');
    Route::get('/report-analysis', [ReportAnalysisController::class, 'index'])->name('report.analysis');

    // Audit Logs
    Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit.index');

    // Stock Movement Trends
    Route::prefix('tenant/stock-trends')->name('tenant.stock-trends.')->group(function () {
        Route::get('/', [StockMovementTrendController::class, 'index'])->name('index');
        Route::get('/product/{encrypted}', [StockMovementTrendController::class, 'showProduct'])->name('product');
        Route::get('/duka/{encrypted}', [StockMovementTrendController::class, 'showDuka'])->name('duka');
    });

    // Stock Transfer
    Route::get('/tenant/stock-transfers/create', [StockTransferController::class, 'create'])->name('tenant.stock-transfers.create');
    Route::post('/tenant/stock-transfers', [StockTransferController::class, 'store'])->name('tenant.stock-transfers.store');
    Route::put('/officers/{user}/reset-password', [OfficerController::class, 'resetPassword'])
    ->name('officers.reset-password');

    // Officers Management
    Route::resource('officers', OfficerController::class);
    Route::put('/officers/{id}/set-default-password', [OfficerController::class, 'setDefaultPassword'])->name('officers.set-default-password');
    Route::post('/officers/assign', [OfficerController::class, 'assign'])->name('officers.assign');
    Route::delete('/officers/unassign/{id}', [OfficerController::class, 'unassign'])->name('officers.unassign');
    Route::put('/officers/update-role/{id}', [OfficerController::class, 'updateRole'])->name('officers.update-role');
    Route::patch('/officers/reassign/{id}', [OfficerController::class, 'reassign'])->name('officers.reassign');

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/officer/{officerId}', [PermissionController::class, 'showOfficerPermissions'])->name('permissions.officer.show');
    Route::put('/permissions/officer/{officerId}', [PermissionController::class, 'updateOfficerPermissions'])->name('permissions.officer.update');
    Route::get('/permissions/duka/{dukaId}/plan', [PermissionController::class, 'checkDukaPlan'])->name('permissions.check-duka-plan');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::get('/messages/{message}/download', [MessageController::class, 'downloadAttachment'])->name('messages.download');

    // Debugging
    Route::get('/test/product', [TestProductController::class, 'test'])->name('test.product');
    Route::get('/test/product-create', [TestProductController::class, 'testProductCreation'])->name('test.product.create');
});


// ==============================================================================
// OFFICER ROUTES
// ==============================================================================
Route::prefix('officer')->name('officer.')->middleware(['auth', 'officer'])->group(function () {

    // Dashboard & Profile
    Route::get('/dashboard', [OfficerController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [OfficerController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [OfficerController::class, 'updateProfile'])->name('profile.update');

    // Sales
    Route::get('/sales', [SalesofficerControllers::class, 'officersalesstocks'])->name('sales');
    Route::get('/sales/{id}/invoice', function ($id) {
        $sale = \App\Models\Sale::with(['customer', 'duka', 'saleItems.product', 'tenant'])->findOrFail($id);
        $user = auth()->user();

        $dukaIds = \App\Models\TenantOfficer::where('officer_id', $user->id)->where('status', true)->pluck('duka_id');
        if (!$dukaIds->contains($sale->duka_id)) abort(403, 'Unauthorized access to this sale.');

        $tenantAccount = \App\Models\TenantAccount::where('tenant_id', $sale->tenant_id)->first();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.invoice', compact('sale', 'tenantAccount'));
        return $pdf->download('invoice_' . $sale->id . '.pdf');
    })->name('sales.invoice');

    // Products & Stocks
    Route::get('/allproduct', [OfficerController::class, 'manageproduct'])->name('manageproduct'); // Consider renaming route

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/import', [OfficerController::class, 'importProducts'])->name('import');
        Route::post('/import', [OfficerController::class, 'processImport'])->name('import.process');
        Route::post('/store', [OfficerController::class, 'storeProduct'])->name('store');
        Route::get('/export', [OfficerController::class, 'exportProducts'])->name('export');

        // Product Items / Specifics
        Route::put('/{id}', [OfficerController::class, 'updateProduct'])->name('update');
        Route::delete('/{id}', [OfficerController::class, 'destroyProduct'])->name('destroy');
        Route::get('/{id}/edit', [OfficerController::class, 'editProduct'])->name('edit');
    });

    Route::get('/products/create', [OfficerController::class, 'showCreateProduct'])->name('product.create');
    Route::get('/products/{product}/edit', [OfficerController::class, 'showEditProduct'])->name('product.edit');
    Route::get('/products/{product}/stock', [OfficerController::class, 'manageStock'])->name('product.stock');
    Route::get('/products/{product}/items', [OfficerController::class, 'viewProductItems'])->name('product.items');

    Route::post('/product-items/update-status', [OfficerController::class, 'updateProductItemStatus'])->name('product-items.update-status');

    Route::post('/stocks/add', [OfficerController::class, 'addStock'])->name('stocks.add');
    Route::post('/stocks/reduce', [OfficerController::class, 'reduceStock'])->name('stocks.reduce');
    Route::put('/stocks/{id}', [OfficerController::class, 'updateStock'])->name('stocks.update');


    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [OfficerController::class, 'manageCustomers'])->name('manage');
        Route::get('/import', [OfficerController::class, 'importCustomers'])->name('import');
        Route::post('/import', [OfficerController::class, 'processCustomerImport'])->name('import.process');
        Route::post('/', [OfficerController::class, 'storeCustomer'])->name('store');
        Route::put('/{id}', [OfficerController::class, 'updateCustomer'])->name('update');
        Route::delete('/{id}', [OfficerController::class, 'destroyCustomer'])->name('destroy');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [OfficerController::class, 'manageCategories'])->name('manage');
        Route::get('/import', [OfficerController::class, 'importCategories'])->name('import');
        Route::post('/import', [OfficerController::class, 'processCategoryImport'])->name('import.process');
        Route::post('/', [OfficerController::class, 'storeCategory'])->name('store');
        Route::put('/{id}', [OfficerController::class, 'updateCategory'])->name('update');
        Route::delete('/{id}', [OfficerController::class, 'destroyCategory'])->name('destroy');
    });

    // Loans
    Route::get('/loanmanagement', [OfficerLoanController::class, 'index'])->name('loanmanagement');
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/{id}', [OfficerLoanController::class, 'show'])->name('show');
        Route::post('/{loanId}/payments', [OfficerLoanController::class, 'storePayment'])->name('payments.store');
    });
    Route::get('/loan-aging-analysis', [OfficerLoanController::class, 'agingAnalysis'])->name('loan.aging.analysis');
});

// ==============================================================================
// PAYMENTS & EXTERNAL
// ==============================================================================
Route::get('/payment/checkout/{tenant}/{subscription}', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');



// QR Code Scanning
Route::post('/scan-qr', [ProductController::class, 'scanQr'])->name('scan.qr');
