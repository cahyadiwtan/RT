<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Pengurus\AspirationController as PengurusAspirationController;
use App\Http\Controllers\Pengurus\AssetCategoryController;
use App\Http\Controllers\Pengurus\AssetController;
use App\Http\Controllers\Pengurus\EventController;
use App\Http\Controllers\Pengurus\ExpenseController;
use App\Http\Controllers\Pengurus\HouseController;
use App\Http\Controllers\Pengurus\IncomeController;
use App\Http\Controllers\Pengurus\MonthlyBillController;
use App\Http\Controllers\Pengurus\MonthlyFeeSettingController;
use App\Http\Controllers\Pengurus\PaymentController;
use App\Http\Controllers\Pengurus\ReportController;
use App\Http\Controllers\Pengurus\ResidentController;
use App\Http\Controllers\Pengurus\UserController;
use App\Http\Controllers\Warga\AspirationController as WargaAspirationController;
use App\Http\Controllers\Warga\EventController as WargaEventController;
use App\Http\Controllers\Warga\WargaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pengurus Area
    Route::middleware(['role:pengurus'])->prefix('pengurus')->name('pengurus.')->group(function () {
        Route::resource('houses', HouseController::class);
        Route::post('houses/{house}/amnesty', [HouseController::class, 'amnesty'])->name('houses.amnesty');
        Route::resource('residents', ResidentController::class);
        Route::post('residents/{resident}/toggle-verify', [ResidentController::class, 'toggleVerify'])->name('residents.toggle-verify');
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        Route::get('fee-settings', [MonthlyFeeSettingController::class, 'index'])->name('fee-settings.index');
        Route::post('fee-settings', [MonthlyFeeSettingController::class, 'store'])->name('fee-settings.store');

        Route::get('bills', [MonthlyBillController::class, 'index'])->name('bills.index');
        Route::post('bills/generate', [MonthlyBillController::class, 'generate'])->name('bills.generate');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('payments/{payment}/void', [PaymentController::class, 'void'])->name('payments.void');

        // Pemasukan Kas Umum RT (selain iuran warga)
        Route::get('incomes', [IncomeController::class, 'index'])->name('incomes.index');
        Route::get('incomes/create', [IncomeController::class, 'create'])->name('incomes.create');
        Route::post('incomes', [IncomeController::class, 'store'])->name('incomes.store');
        Route::get('incomes/{income}/edit', [IncomeController::class, 'edit'])->name('incomes.edit');
        Route::put('incomes/{income}', [IncomeController::class, 'update'])->name('incomes.update');
        Route::patch('incomes/{income}/void', [IncomeController::class, 'void'])->name('incomes.void');
        Route::get('income-categories', [IncomeController::class, 'categories'])->name('income-categories.index');
        Route::post('income-categories', [IncomeController::class, 'storeCategory'])->name('income-categories.store');
        Route::put('income-categories/{category}', [IncomeController::class, 'updateCategory'])->name('income-categories.update');
        Route::delete('income-categories/{category}', [IncomeController::class, 'destroyCategory'])->name('income-categories.destroy');

        // Pengeluaran Kas Umum RT
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::patch('expenses/{expense}/void', [ExpenseController::class, 'void'])->name('expenses.void');
        Route::get('expense-categories', [ExpenseController::class, 'categories'])->name('expense-categories.index');
        Route::post('expense-categories', [ExpenseController::class, 'storeCategory'])->name('expense-categories.store');
        Route::put('expense-categories/{category}', [ExpenseController::class, 'updateCategory'])->name('expense-categories.update');
        Route::delete('expense-categories/{category}', [ExpenseController::class, 'destroyCategory'])->name('expense-categories.destroy');

        // Assets
        Route::get('categories', [AssetCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [AssetCategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [AssetCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [AssetCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::resource('assets', AssetController::class);

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/iuran', [ReportController::class, 'iuran'])->name('reports.iuran');
        Route::get('reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
        Route::get('reports/arrears', [ReportController::class, 'arrears'])->name('reports.arrears');
        Route::get('reports/deposits', [ReportController::class, 'deposits'])->name('reports.deposits');
        Route::get('reports/cashflow', [ReportController::class, 'cashflow'])->name('reports.cashflow');
        Route::get('reports/assets', [ReportController::class, 'assets'])->name('reports.assets');
        Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');

        // Aspirasi routes
        Route::get('aspirations', [PengurusAspirationController::class, 'index'])->name('aspirations.index');
        Route::get('aspirations/{aspiration}', [PengurusAspirationController::class, 'show'])->name('aspirations.show');
        Route::post('aspirations/{aspiration}/updates', [PengurusAspirationController::class, 'updateStatus'])->name('aspirations.updates.store');

        // Events
        Route::resource('events', EventController::class);
        Route::patch('events/{event}/status', [EventController::class, 'updateStatus'])->name('events.update-status');
        Route::post('events/{event}/participants', [EventController::class, 'storeParticipant'])->name('events.participants.store');
        Route::delete('events/{event}/participants/{participant}', [EventController::class, 'destroyParticipant'])->name('events.participants.destroy');
        Route::patch('events/{event}/participants/{participant}/status', [EventController::class, 'updateParticipantStatus'])->name('events.participants.update-status');
        Route::post('events/{event}/participants/bulk', [EventController::class, 'bulkParticipants'])->name('events.participants.bulk');
        Route::post('events/{event}/payments', [EventController::class, 'storePayment'])->name('events.payments.store');
        Route::patch('events/{event}/payments/{payment}/void', [EventController::class, 'voidPayment'])->name('events.payments.void');
        Route::post('events/{event}/incomes', [EventController::class, 'storeIncome'])->name('events.incomes.store');
        Route::patch('events/{event}/incomes/{income}/void', [EventController::class, 'voidIncome'])->name('events.incomes.void');
        Route::post('events/{event}/expenses', [EventController::class, 'storeExpense'])->name('events.expenses.store');
        Route::patch('events/{event}/expenses/{expense}/void', [EventController::class, 'voidExpense'])->name('events.expenses.void');
        Route::post('events/{event}/budgets', [EventController::class, 'storeBudget'])->name('events.budgets.store');
        Route::patch('events/{event}/budgets/{budget}', [EventController::class, 'updateBudget'])->name('events.budgets.update');
        Route::delete('events/{event}/budgets/{budget}', [EventController::class, 'destroyBudget'])->name('events.budgets.destroy');
    });

    // Warga Area
    Route::middleware(['role:warga'])->prefix('warga')->name('warga.')->group(function () {
        Route::get('iuran', [WargaController::class, 'iuran'])->name('iuran');
        Route::get('riwayat-pembayaran', [WargaController::class, 'riwayatPembayaran'])->name('riwayat-pembayaran');
        Route::get('change-password', [WargaController::class, 'showChangePasswordForm'])->name('change-password');
        Route::post('change-password', [WargaController::class, 'updatePassword'])->name('update-password');
        Route::get('assets', [WargaController::class, 'assets'])->name('assets');
        Route::get('arus-kas', [WargaController::class, 'cashflow'])->name('cashflow');

        // Event (warga view)
        Route::get('events', [WargaEventController::class, 'index'])->name('events.index');
        Route::get('events/{event}', [WargaEventController::class, 'show'])->name('events.show');

        // Aspirasi routes
        Route::get('aspirations', [WargaAspirationController::class, 'index'])->name('aspirations.index');
        Route::get('aspirations/create', [WargaAspirationController::class, 'create'])->name('aspirations.create');
        Route::post('aspirations', [WargaAspirationController::class, 'store'])->name('aspirations.store');
        Route::get('aspirations/{aspiration}', [WargaAspirationController::class, 'show'])->name('aspirations.show');
    });
});
