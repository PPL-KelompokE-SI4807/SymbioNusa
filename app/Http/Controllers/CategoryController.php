<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CategoryService $categoryService
    ) {}

    /**
     * Tampilkan daftar master kategori material dan hierarki.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $query = Category::with(['parent', 'children'])
            ->withCount(['children', 'listings', 'demandRequests']);

        // Filter pencarian nama
        if ($search = $request->input('q')) {
            $query->where('nama', 'like', "%{$search}%");
        }

        // Filter status (default: tampilkan semua atau filter pilihan)
        if ($status = $request->input('status')) {
            if (in_array($status, ['AKTIF', 'ARSIP'], true)) {
                $query->where('status', $status);
            }
        }

        // Filter risiko
        if ($risiko = $request->input('tingkat_risiko')) {
            if (in_array($risiko, ['RENDAH', 'TINGGI'], true)) {
                $query->where('tingkat_risiko', $risiko);
            }
        }

        // Filter B3
        if ($request->has('is_b3_prohibited') && $request->input('is_b3_prohibited') !== '') {
            $query->where('is_b3_prohibited', (int) $request->input('is_b3_prohibited'));
        }

        $categories = $query->orderBy('categories_id_categories')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Category::count(),
            'aktif' => Category::where('status', 'AKTIF')->count(),
            'arsip' => Category::where('status', 'ARSIP')->count(),
            'b3_prohibited' => Category::where('is_b3_prohibited', 1)->count(),
        ];

        return view('categories.index', compact('categories', 'stats'));
    }

    /**
     * Tampilkan form pembuatan kategori baru.
     */
    public function create(): View
    {
        $this->authorize('create', Category::class);

        $parentCategories = Category::active()
            ->orderBy('nama')
            ->get();

        return view('categories.create', compact('parentCategories'));
    }

    /**
     * Simpan kategori baru ke database.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = $this->categoryService->create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori '{$category->nama}' berhasil ditambahkan.");
    }

    /**
     * Tampilkan detail kategori, templat spesifikasi, dan toleransi.
     */
    public function show(Category $category): View
    {
        $this->authorize('view', $category);

        $category->load(['parent', 'children', 'listings', 'demandRequests']);

        $canArchive = $this->categoryService->canArchive($category);
        $archiveBlockReasons = $this->categoryService->getArchiveBlockReasons($category);

        return view('categories.show', compact('category', 'canArchive', 'archiveBlockReasons'));
    }

    /**
     * Tampilkan form edit kategori.
     */
    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        $parentCategories = Category::where('id_categories', '!=', $category->id_categories)
            ->active()
            ->orderBy('nama')
            ->get();

        return view('categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Perbarui data kategori.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categoryService->update($category, $request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori '{$category->nama}' berhasil diperbarui.");
    }

    /**
     * Arsipkan kategori jika tidak sedang digunakan.
     */
    public function archive(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('archive', $category);

        if (! $this->categoryService->canArchive($category)) {
            $reasons = $this->categoryService->getArchiveBlockReasons($category);

            return redirect()
                ->back()
                ->with('error', 'Kategori tidak dapat diarsipkan: '.implode(' ', $reasons));
        }

        $this->categoryService->archive($category);

        return redirect()
            ->back()
            ->with('success', "Kategori '{$category->nama}' berhasil diarsipkan.");
    }

    /**
     * Pulihkan kategori dari arsip ke aktif.
     */
    public function unarchive(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->categoryService->unarchive($category);

        return redirect()
            ->back()
            ->with('success', "Kategori '{$category->nama}' berhasil diaktifkan kembali.");
    }
}
