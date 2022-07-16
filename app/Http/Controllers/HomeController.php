<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{

    public function index(Request $request): View
    {
        $validCategories = $this->findValidCategories();
        $randomCategory = $validCategories[array_rand($validCategories)];
        return View('home', [
            'host' => $request->getSchemeAndHttpHost(),
            'validCategories' => $validCategories,
            'randomCategory' => $randomCategory
            ]);
    }
}
