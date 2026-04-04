<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Support\Facades\Gate;

class ReviewsController extends Controller
{
    public function index()
    {
        Gate::authorize('manage reviews');

        $totalReviews  = Review::count();
        $averageRating = number_format((Review::avg('rating') ?? 0) / 20, 1);
        $fiveStarPct   = $totalReviews > 0
            ? number_format((Review::where('rating', '>=', 90)->count() / $totalReviews) * 100, 1)
            : 0;
        $oneStarPct = $totalReviews > 0
            ? number_format((Review::where('rating', '<=', 20)->count() / $totalReviews) * 100, 1)
            : 0;

        return view('dashboard.reviews.index', compact(
            'totalReviews',
            'averageRating',
            'fiveStarPct',
            'oneStarPct',
        ));
    }
}
