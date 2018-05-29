<?php

namespace App\Http\Controllers;

use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;

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

    /**
     * @param null $category
     * @return mixed
     */
    public function index($category = null)
    {

        if (is_null($category)) {
            $category = $this->validCategories[array_rand($this->validCategories, 1)];
        }

        if (!in_array($category, $this->validCategories)) {
            return Image::make(public_path('media') . '/invalid-category.png')->response();
        }

        $mediaPath = public_path('media/' . $category);
        $imageList = array_diff(scandir($mediaPath), array('.', '..'));
        $key = array_rand($imageList, 1);
        $imagePath = $mediaPath . '/' . $imageList[$key];

        return Image::make($imagePath)->response();
    }


    /**
     * @param Request $request
     * @param null $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function json(Request $request, $category = null)
    {
        $httpHost = $request->getSchemeAndHttpHost();

        if (!in_array($category, $this->validCategories)) {
            return response()->json([
                'validCategories' => $this->validCategories,
                'usage' => $httpHost . '/json/travel'
            ]);
        }

        $mediaPath = public_path('media/' . $category);
        $imageList = array_diff(scandir($mediaPath), array('.', '..'));

        foreach ($imageList as $key => $image) {
            $imageList[$key] = $httpHost . '/media/' . $category . '/' . $imageList[$key];
        }

        return response()->json($imageList);
    }
}
