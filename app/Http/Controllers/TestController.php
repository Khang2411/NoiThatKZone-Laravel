<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmed;
use App\Mail\CouponMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Test;
use App\Models\User;
use Illuminate\Http\Request;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendMail;

class TestController extends Controller
{

  function test()
  {
    $users = User::whereIn('id', [1])->get();
    foreach ($users as $user) {
      SendMail::dispatch($user);
    }
  }
}
