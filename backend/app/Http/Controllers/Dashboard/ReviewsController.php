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

        // Recent reviews (last 7 days)
        $recentReviewsCount = Review::where('created_at', '>=', now()->subDays(7))->count();

        // Rating distribution (1-5 stars)
        $ratingDistribution = [];
        $starRanges = [
            5 => [90, 100],
            4 => [70, 89],
            3 => [50, 69],
            2 => [30, 49],
            1 => [0, 29],
        ];

        foreach ($starRanges as $stars => $range) {
            $count = Review::whereBetween('rating', $range)->count();
            $ratingDistribution[$stars] = [
                'count' => $count,
                'pct'   => $totalReviews > 0 ? round(($count / $totalReviews) * 100, 1) : 0,
            ];
        }

        return view('dashboard.reviews.index', compact(
            'totalReviews',
            'averageRating',
            'fiveStarPct',
            'recentReviewsCount',
            'ratingDistribution',
        ));
    }
}

