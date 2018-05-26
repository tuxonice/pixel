<?php

namespace App\Http\Controllers;

use Intervention\Image\Facades\Image;

class IndexController extends Controller
{

    protected $validCategories = [
        "animals",
        "backgrounds",
        "buildings",
        "business",
        "computer",
        "education",
        "fashion",
        "feelings",
        "food",
        "health",
        "industry",
        "music",
        "nature",
        "people",
        "places",
        "religion",
        "science",
        "sports",
        "transportation",
        "travel"
    ];

    public function index($category)
    {

        if(!in_array($category,$this->validCategories)){
            return 'invalid category';
        }

        $storagePath = storage_path('media/' . $category);
        $imageList = array_diff(scandir($storagePath), array('.', '..'));
		$key = array_rand($imageList, 1);
		$imagePath = $storagePath.'/'.$imageList[$key];
		
		return Image::make($imagePath)->response();
    }
}
