<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminReviewController extends Controller
{
    function list()
    { {
            $list_action = ['delete' => 'Xóa tạm thời'];
            if (request()->status === 'trash') {
                $review = Review::onlyTrashed()->orderBy("id", "DESC")->with('replies', 'user', 'product:id,slug,name')
                    ->when(request()->search, function ($q) {
                        return $q->where('reviews.user.name', 'LIKE', '%' . 'Dũng Lê' . '%');
                    })->paginate(10);
                $review->appends(['status' => 'trash']); // khi nhấn qua page 2 vẫn ở status=trash
                $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
            } else {
                $review = Review::orderBy("id", "DESC")->with('replies', 'user', 'product:id,slug,name')->when(request()->search, function ($q) {
                    return $q->where('review.user.name', 'LIKE', '%' . 'Dũng Lê' . '%');
                })->paginate(10);
                $review->appends(['status' => 'active']);
            }
            $count_order_active = Review::count();
            $count_order_trash = Review::onlyTrashed()->count();
            $count = [$count_order_active, $count_order_trash];
            // return $review;
            return Inertia::render('Review/ReviewList', ['reviews' => $review, 'list_action' => $list_action, 'count' => $count]);
        }
    }
    function update()
    {
        $user = Auth::user();

        Validator::make(
            request()->all(),
            [
                'content' => 'required',
            ],
            [
                'content.required' => 'Nội dung đánh giá là bắt buộc',
            ]
        )->validate();

        $review = Review::find(request()->id);
        $review->content = request()->content;
        $review->status = request()->status;

        Review::create([
            'product_id' => request()->product_id,
            'user_id' => $user->id,
            'review_id' => request()->review_id,
            'content' => request()->reply,
            'rating' => '',
            'status' => 'confirmed'
        ]);

        $review->save();
    }

    function action()
    {
        $list_check = request()->list_check;
        if (!$list_check) {
            return to_route('admin.collection.list')->with(['status' => 'Bạn cần chọn phần tử để thực hiện']);
        } else {
            switch (request()->action) {
                case 'delete':
                    Review::destroy($list_check);
                    return redirect()->back()->with(['status' => 'Xóa thành công']);
                    break;
                case 'restore':
                    Review::whereIn('id', $list_check)->restore();
                    return redirect()->back()->with(['status' => 'Khôi phục thành công']);
                    break;
                case 'force_delete':
                    Review::withTrashed()->whereIn('id', $list_check)->forceDelete();
                    return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->with(['status' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
