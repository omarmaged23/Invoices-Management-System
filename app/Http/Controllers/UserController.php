<?php

namespace App\Http\Controllers;

use App\Notifications\Add_User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:قائمة المستخدمين|المستخدمين', ['only' => ['index']]);
        $this->middleware('permission:اضافة مستخدم', ['only' => ['create','store']]);
        $this->middleware('permission:تعديل مستخدم', ['only' => ['edit','update']]);
        $this->middleware('permission:حذف مستخدم', ['only' => ['destroy']]);

    }

    public function index(Request $request)
    {
        $data = User::orderBy('id','DESC')->paginate(5);
        return view('users.show_users',compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        return view('users.Add_user',compact('roles'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles_name' => 'required'
        ],[
            'name.required' => 'يجب ادخال اسم المستخدم',
            'email.required' => 'يجب ادخال البريد الاليكترونى',
            'email.email' => 'برجاء ادخال بريد اليكترونى صحيح',
            'email.unique' =>'البريد الاليكترونى مسجل بالفعل',
            'password.required' => 'يجب ادخال كلمة المرور',
            'password.same' => 'كلمة السر لا تتطابق في الخانتين',
            'roles_name.required' => 'يرجى تحديد صلاحية المستخدم'
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles_name'));

        Notification::route('mail', $request->email)->notify(new Add_User($request->Status));

        return redirect()->route('users.index')
            ->with('success','تم اضافة المستخدم بنجاح');
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('users.show',compact('user'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();

        return view('users.edit',compact('user','roles','userRole'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles_name' => 'required'
        ],[
            'name.required' => 'يجب ادخال اسم المستخدم',
            'email.required' => 'يجب ادخال البريد الاليكترونى',
            'email.email' => 'برجاء ادخال بريد اليكترونى صحيح',
            'email.unique' =>'البريد الاليكترونى مسجل بالفعل',
            'password.required' => 'يجب ادخال كلمة المرور',
            'password.same' => 'كلمة السر لا تتطابق في الخانتين',
            'roles_name.required' => 'يرجى تحديد صلاحية المستخدم'
        ]);

        $input = $request->all();
        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input,array('password'));
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();

        $user->assignRole($request->input('roles_name'));

        return redirect()->route('users.index')
            ->with('success','تم تحديث معلومات المستخدم بنجاح');
    }

    public function destroy(Request $request)
    {
        $id = $request->user_id;
        User::find($id)->delete();
        return redirect()->route('users.index')
            ->with('success','تم حذف المستخدم بنجاح');
    }
}
