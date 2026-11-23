<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Services\Listing\ListingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        protected ListingService $listingService
    ) {}

    public function index(Request $request): View
    {
        $categories = Category::where('status', 'AKTIF')
            ->where('is_b3_prohibited', 0)
            ->orderBy('nama')
            ->get();

        $listings = $this->listingService->getCatalogListings($request->all());

        return view('catalog.index', compact('listings', 'categories'));
    }

    public function show(Listing $listing): View
    {
        if (! $listing->isPublished()) {
            abort(404);
        }

        $listing->load(['category', 'company', 'attachments']);

        return view('catalog.show', compact('listing'));
    }
}
