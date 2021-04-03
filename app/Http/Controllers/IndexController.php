<?php

namespace App\Http\Controllers;

use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;

class IndexController extends Controller
{

    /**
     * @param string|null $category
     * @return mixed
     */
    public function index($category = null)
    {

        $validCategories = $this->findValidCategories();
        if (is_null($category)) {
            $category = $validCategories[array_rand($validCategories, 1)];
        }

        if (!in_array($category, $validCategories)) {
            return Image::make(public_path('media') . '/invalid-category.png')->response();
        }

        $mediaPath = public_path('media/' . $category);
        $imageList = array_filter(scandir($mediaPath), function ($file) use($mediaPath) {
            return is_file($mediaPath . DIRECTORY_SEPARATOR . $file);
        });

        $imagePath = $mediaPath . '/' . $imageList[array_rand($imageList)];

        return Image::make($imagePath)->response();
    }



    /**
     * @param Request $request
     * @param string|null $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function json(Request $request, $category = null)
    {
        $httpHost = $request->getSchemeAndHttpHost();
        $validCategories = $this->findValidCategories();
        if (!in_array($category, $validCategories)) {
            return response()->json([
                'validCategories' => $validCategories,
                'usage' => $httpHost . '/json/travel'
            ]);
        }

        $mediaPath = public_path('media/' . $category);
        $imageList = array_filter(scandir($mediaPath), function ($file) use($mediaPath) {
            return is_file($mediaPath . DIRECTORY_SEPARATOR . $file);
        });
        
        $imageList = array_map(function ($file) use($category, $httpHost){
            return $httpHost . '/media/' . $category . '/' . $file;
        }, $imageList);

        return response()->json($imageList);
    }
}
