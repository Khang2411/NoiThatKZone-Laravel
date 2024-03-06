<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Api\Upload\UploadApi;

class AdminBannerController extends Controller
{
    function list()
    {
        $banners = Banner::all();
        return Inertia::render('Banner/BannerAdd', ['banners' => $banners]);
    }

    function update()
    {
        Validator::make(
            request()->all(),
            [
                'title' => 'required',
            ],
            [
                'title.required' => 'Tiêu đề là bắt buộc',
            ]
        )->validate();
      
        $banner = Banner::find(request()->id);
        $banner->title = request()->title;
        $banner->url = request()->url;
        if (request()->hasFile('thumbnail')) {
            $thumbnail = (new UploadApi())->upload($_FILES['thumbnail']['tmp_name'], [
                'folder' => 'noithatkzone/banners',
                'quality' => '80',
            ]);
            $input['thumbnail'] = $thumbnail['secure_url'];
            if ($banner->public_id_thumbnail !== null) {
                (new UploadApi())->destroy($banner->public_id_thumbnail);
            }
            $banner->thumbnail =  $input['thumbnail'];
            $banner->public_id_thumbnail = $thumbnail['public_id'];
        }
        $banner->save();
    }
}
