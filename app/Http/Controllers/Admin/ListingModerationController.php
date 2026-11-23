<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Services\Listing\ListingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * PBI-09 (SYM-13): moderasi listing oleh Admin. Admin melihat semua listing
 * dan dapat menurunkan listing yang melanggar dari katalog (status DIARSIPKAN).
 */
class ListingModerationController extends Controller
{
    public function __construct(
        protected ListingService $listingService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('moderate', Listing::class);

        $listings = $this->listingService->getModerationListings($request->only(['q', 'kategori', 'status']));
        $categories = Category::orderBy('nama')->get();

        return view('admin.listings.index', compact('listings', 'categories'));
    }

    public function archive(Listing $listing): RedirectResponse
    {
        Gate::authorize('moderate', Listing::class);

        if ($listing->status === 'DIARSIPKAN') {
            return back()->with('error', 'Listing ini sudah diarsipkan.');
        }

        $this->listingService->archiveListing($listing);

        return back()->with('status', "Listing \"{$listing->judul}\" diturunkan dari katalog.");
    }
}
