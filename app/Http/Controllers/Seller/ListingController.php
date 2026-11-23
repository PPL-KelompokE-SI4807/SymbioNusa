<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Services\Listing\ListingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ListingService $listingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Listing::class);

        /** @var User $user */
        $user = $request->user();
        $categories = Category::where('status', 'AKTIF')->orderBy('nama')->get();
        $listings = $this->listingService->getSellerListings((int) $user->id_company, $request->all());

        return view('seller.listings.index', compact('listings', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Listing::class);

        $categories = Category::where('status', 'AKTIF')->orderBy('nama')->get();

        return view('seller.listings.create', compact('categories'));
    }

    public function store(StoreListingRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->listingService->createListing($request->validated(), $request->allFiles(), $user);

        return redirect()
            ->route('seller.listings.index')
            ->with('status', 'Listing sisa produksi berhasil disimpan.');
    }

    public function show(Listing $listing): View
    {
        $this->authorize('view', $listing);

        $listing->load(['category', 'attachments', 'company']);

        return view('seller.listings.show', compact('listing'));
    }

    public function edit(Listing $listing): View
    {
        $this->authorize('update', $listing);

        $listing->load('attachments');
        $categories = Category::where('status', 'AKTIF')->orderBy('nama')->get();

        return view('seller.listings.edit', compact('listing', 'categories'));
    }

    public function update(UpdateListingRequest $request, Listing $listing): RedirectResponse
    {
        $this->listingService->updateListing($listing, $request->validated(), $request->allFiles());

        return redirect()
            ->route('seller.listings.index')
            ->with('status', 'Listing sisa produksi berhasil diperbarui.');
    }

    public function archive(Listing $listing): RedirectResponse
    {
        $this->authorize('delete', $listing);

        $this->listingService->archiveListing($listing);

        return redirect()
            ->route('seller.listings.index')
            ->with('status', 'Listing berhasil diarsipkan.');
    }
}
