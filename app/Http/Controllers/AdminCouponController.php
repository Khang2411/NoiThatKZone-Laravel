<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời'];
        switch (request()->status) {
            case ('trash'):
                $coupons = Coupon::onlyTrashed()
                    ->when(request()->search, function ($q) {
                        return $q->where('name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);

                $coupons->appends(['status' => 'trash', 'search' => request()->search]); // khi nhấn qua page 2 vẫn ở status=trash
                $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
                break;
            default:
                $coupons = Coupon::when(request()->search, function ($q) {
                    return $q->where('name', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);
                $coupons->appends(['status' => 'active', 'search' => request()->search]);
                break;
        }

        $count_coupon_active = Coupon::count();
        $count_coupon_trash = Coupon::onlyTrashed()->count();
        $count = [$count_coupon_active, $count_coupon_trash];
        return Inertia::render('Coupon/CouponList', ['coupons' => $coupons, 'list_action' => $list_action, 'count' => $count]);
    }

    function add()
    {
        return Inertia::render('Coupon/CouponAdd');
    }

    function store()
    {
        $input = request()->all();
        $input['minimum_spend'] = Str::replace(['.', ','], '', request()->minimum_spend);
        $input['amount'] = Str::replace(['.', ','], '', request()->amount);

        $validator = Validator::make(
            $input,
            [
                'code' => 'required|unique:coupons,code,',
                'name' => 'required',
                'type' => 'required',
                'amount' => 'required',
                'minimum_spend' => 'nullable|integer'
            ],
            [
                'name.required' => 'Tên là bắt buộc',
                'code.required' => 'Mã là bắt buộc',
                'code.unique' => 'Mã đã tồn tại',
                'type.required' => 'Loại giảm giá bắt buộc',
                'amount.required' => 'Số tiền giảm bắt buộc',
                'minimum_spend.integer' => 'Chi tiêu tối thiểu phải là số'
            ]
        );

        if ($input['type'] === 'percent') {
            if ($input['amount'] > 100) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('amount', 'Phần trăm từ 0 -> 100');
                });
            }
        }
        $validator->validate();

        Coupon::create([
            'name' =>  $input['name'],
            'code' =>  $input['code'],
            'limit' => $input['limit'],
            'amount' => $input['amount'],
            'type' => $input['type'],
            'minimum_spend' => $input['minimum_spend'] ? $input['minimum_spend'] : null
        ]);
        return to_route('admin.coupon.list');
    }

    function update()
    {
        $input = request()->all();
        $input['minimum_spend'] = Str::replace(['.', ','], '', request()->minimum_spend);
        $input['amount'] = Str::replace(['.', ','], '', request()->amount);

        $validator = Validator::make(
            $input,
            [
                'code' => 'required|unique:coupons,code,' . $input['id'],
                'name' => 'required',
                'type' => 'required',
                'amount' => 'required',
                'minimum_spend' => 'nullable|integer',
                'limit' => 'nullable|integer'
            ],
            [
                'name.required' => 'Tên là bắt buộc',
                'code.required' => 'Mã là bắt buộc',
                'code.unique' => 'Mã đã tồn tại',
                'type.required' => 'Loại giảm giá bắt buộc',
                'amount.required' => 'Số tiền giảm bắt buộc',
                'minimum_spend.integer' => 'Chi tiêu tối thiểu phải là số'
            ]
        );

        if ($input['type'] === 'percent') {
            if ($input['amount'] > 100) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('amount', 'Phần trăm từ 0 -> 100');
                });
            }
        }
        $validator->validate();
        $coupon = Coupon::find(request()->id);
        $coupon->name = $input['name'];
        $coupon->code = $input['code'];
        $coupon->limit = $input['limit'];
        $coupon->amount = $input['amount'];
        $coupon->type = $input['type'];
        $coupon->minimum_spend = $input['minimum_spend'] ? $input['minimum_spend'] : null;
        $coupon->save();
    }

    function delete($id)
    {
        Coupon::destroy($id);
    }
    function action()
    {
        $list_check = request()->list_check;
        if (!$list_check) {
            return redirect()->back()->with(['status' => 'Bạn cần chọn phần tử để thực hiện']);
        } else {
            switch (request()->action) {
                case 'delete':
                    Coupon::destroy($list_check);
                    return redirect()->back()->with(['status' => 'Xóa thành công']);
                    break;
                case 'restore':
                    Coupon::whereIn('id', $list_check)->restore();
                    return redirect()->back()->with(['status' => 'Khôi phục thành công']);
                    break;
                case 'force_delete':
                    Coupon::withTrashed()->whereIn('id', $list_check)->forceDelete();
                    return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->with(['status' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
