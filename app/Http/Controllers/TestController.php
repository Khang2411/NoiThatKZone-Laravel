<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
  function test()
  {
    // $data = (new UploadApi())->upload('two-mb.jpg', [
    //     'folder' => 'noithatkzone/products',
    //     'format' => 'jpg',
    //     'quality' => '80',
    // ]);
    // return $data;
    // $delete = (new UploadApi())->destroy(null);
    // return $delete;    
    //return  Product::where('is_featured', '1')->update(['is_featured' => 0]);
    // Role::destroy(7);
    $user = User::find(1);
    return $user->hasPermission('module.roles');
  }
}
