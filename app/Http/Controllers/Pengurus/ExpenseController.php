<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(private ExpenseService $service) {}

    public function index(Request $request)
    {
        $expenses = $this->service->paginateExpenses(
            $request->get('search'),
            $request->get('category_id'),
            $request->get('date_from'),
            $request->get('date_to'),
        );
        $summary = $this->service->getSummary(
            $request->get('category_id'),
            $request->get('date_from'),
            $request->get('date_to'),
        );
        $categories = $this->service->activeCategories();

        return view('pengurus.expenses.index', compact('expenses', 'summary', 'categories'));
    }

    public function create()
    {
        $categories = $this->service->activeCategories();

        return view('pengurus.expenses.create', compact('categories'));
    }

    public function store(ExpenseRequest $request)
    {
        $expense = $this->service->createExpense($request->validated(), $request->user()->id);

        return redirect()->route('pengurus.expenses.index')
            ->with('success', "Pengeluaran \"{$expense->description}\" berhasil dicatat.");
    }

    public function edit(Expense $expense)
    {
        abort_if($expense->status === 'void', 404);

        $categories = $this->service->activeCategories();

        return view('pengurus.expenses.edit', compact('expense', 'categories'));
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $this->service->updateExpense($expense, $request->validated(), $request->user()->id);

        return redirect()->route('pengurus.expenses.index')
            ->with('success', "Pengeluaran \"{$expense->description}\" berhasil diperbarui.");
    }

    public function void(Request $request, Expense $expense)
    {
        $this->service->voidExpense($expense, $request->user()->id);

        return back()->with('success', 'Pengeluaran telah dibatalkan (void).');
    }

    public function categories(Request $request)
    {
        $categories = $this->service->allCategories();

        return view('pengurus.expenses.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $this->service->createCategory($validated);

        return back()->with('success', 'Kategori pengeluaran berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name,'.$category->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->service->updateCategory($category, $validated);

        return back()->with('success', 'Kategori pengeluaran berhasil diperbarui.');
    }

    public function destroyCategory(Request $request, ExpenseCategory $category)
    {
        $this->service->destroyCategory($category);

        return back()->with('success', 'Kategori pengeluaran berhasil dihapus.');
    }
}
