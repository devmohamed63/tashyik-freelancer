<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Review;
use App\Http\Requests\ReviewRequest;
use App\Utils\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Auth;

class ReviewController extends ApiController
{
    public function store(ReviewRequest $request)
    {
        $user = Auth::user();

        $review = new Review([
            'body' => $request->body,
            'rating' => $request->rating * 20,
        ]);

        $review->user_id = $user->id;

        $serviceProvider = User::find($request->service_provider);

        $serviceProvider->reviews()->save($review);

        return response('', 201);
    }
}
