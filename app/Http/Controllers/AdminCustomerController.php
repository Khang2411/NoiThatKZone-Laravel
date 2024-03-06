<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminCustomerController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời'];
        if (request()->status === 'trash') {
            $users = User::onlyTrashed()->where('role_id', null)->with('role')
                ->when(request()->search, function ($q) {
                    return $q->where('name', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);

            $users->appends(['status' => 'trash']); // page 2 continue status=trash
            $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
        } else {
            $users = User::where('role_id', null)->with('role')
                ->when(request()->search, function ($q) {
                    return $q->where('name', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);

            $users->appends(['status' => 'active']);
        }
        $count_order_active = User::where('role_id', null)->count();
        $count_order_trash = User::where('role_id', null)->onlyTrashed()->count();
        $count = [$count_order_active, $count_order_trash];
        $roles = Role::all();
        return Inertia::render('User/CustomerList', ['users' => $users, 'list_action' => $list_action, 'roles' => $roles, 'count' => $count]);
    }

    function update()
    {
        Validator::make(request()->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['min:10', 'max:10'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required'
        ], [
            'name.required' => 'Tên không được trống',
            'phone.min' => 'SĐT phải là 10 số',
            'phone.max' => 'SĐT phải là 10 số',
            'role_id.required' => 'Phải cấp quyền'
        ])->validate();

        $user = User::find(request()->id);
        $user->name = request()->name;
        $user->phone = request()->phone;
        $user->role_id = request()->role_id;
        if (request()->password) {
            $user->password = Hash::make(request()->password);
        }

        $user->save();
    }

    function delete($id)
    {
        $customer = User::withTrashed()->find($id);

        if ($customer->deleted_at) {
            $customer->forceDelete();
        } else {
            $customer->delete();
        }
    }

    function action()
    {
        $list_check = request()->list_check; // $list_check là 1 mảng có mảng thì phải duyệt
        if (!$list_check) {
            return redirect()->back()->with(['status' => 'Bạn cần chọn phần tử để thực hiện']);
        }
        foreach ($list_check as $key => $values) { // lấy $key is index   
            if ($values == Auth::id()) {
                unset($list_check[$key]);
                return redirect()->back()->with(['status' => 'Không thể thao tác trên chính bản thân']);
            } else if (request()->action == 'delete') {
                User::destroy($list_check);
                return redirect()->back()->with(['status' => 'Xóa thành công']);
            } else if (request()->action == 'restore') {
                User::wherein('id', $list_check)->restore(); // where in là dk chứa mảng
                return redirect()->back()->with(['status' => 'Khôi phục thành công']);
            } else if (request()->action == 'force_delete') {
                User::withTrashed()->wherein('id', $list_check)->forceDelete();
                return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
            } else if (request()->action == '') {
                return redirect()->back()->with(['status' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
