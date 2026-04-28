<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function balance()
    {
        $user = Auth::user();

        // Members see institution balance
        $balanceOwner = $user->institution_id ? $user->institution : $user;

        return new PriceResource([$balanceOwner->balance]);
    }
}
