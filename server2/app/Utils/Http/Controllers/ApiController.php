<?php

namespace App\Utils\Http\Controllers;

use App\Http\Controllers\Controller;

abstract class ApiController extends Controller
{
    protected int $paginationLimit = 10;
}
