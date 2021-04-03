<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index(Request $request)
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
