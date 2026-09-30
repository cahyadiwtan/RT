<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncomeRequest;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Services\IncomeService;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct(private IncomeService $service) {}

    public function index(Request $request)
    {
        $incomes = $this->service->paginateIncomes(
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

        return view('pengurus.incomes.index', compact('incomes', 'summary', 'categories'));
    }

    public function create()
    {
        $categories = $this->service->activeCategories();

        return view('pengurus.incomes.create', compact('categories'));
    }

    public function store(IncomeRequest $request)
    {
        $income = $this->service->createIncome($request->validated(), $request->user()->id);

        return redirect()->route('pengurus.incomes.index')
            ->with('success', "Pemasukan \"{$income->description}\" berhasil dicatat.");
    }

    public function edit(Income $income)
    {
        abort_if($income->status === 'void', 404);

        $categories = $this->service->activeCategories();

        return view('pengurus.incomes.edit', compact('income', 'categories'));
    }

    public function update(IncomeRequest $request, Income $income)
    {
        $this->service->updateIncome($income, $request->validated(), $request->user()->id);

        return redirect()->route('pengurus.incomes.index')
            ->with('success', "Pemasukan \"{$income->description}\" berhasil diperbarui.");
    }

    public function void(Request $request, Income $income)
    {
        $this->service->voidIncome($income, $request->user()->id);

        return back()->with('success', 'Pemasukan telah dibatalkan (void).');
    }

    public function categories(Request $request)
    {
        $categories = $this->service->allCategories();

        return view('pengurus.incomes.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $this->service->createCategory($validated);

        return back()->with('success', 'Kategori pemasukan berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, IncomeCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name,'.$category->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->service->updateCategory($category, $validated);

        return back()->with('success', 'Kategori pemasukan berhasil diperbarui.');
    }

    public function destroyCategory(Request $request, IncomeCategory $category)
    {
        $this->service->destroyCategory($category);

        return back()->with('success', 'Kategori pemasukan berhasil dihapus.');
    }
}
