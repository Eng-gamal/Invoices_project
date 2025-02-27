<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index(Request $request)
    {
    $data = User::orderBy('id','DESC')->paginate(5);
    return view('users.show_user',compact('data'))
    ->with('i', ($request->input('page', 1) - 1) * 5);
    }


    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        $roles = Role::pluck('name', 'name')->toArray(); // الحصول على الأدوار
        return view('users.Add_user', compact('roles'));

    }
    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {

        /*
    $this->validate($request, [
    'name' => 'required',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|same:confirm-password',
    'roles_name' => 'required'
    ]);
*/
    $input = $request->all();


    $input['password'] = Hash::make($input['password']);
    $input = $request->except('password_confirmation');

    $user = User::create($input);
    $roles = Role::pluck('name','name')->all();
    $userRole = $user->roles->pluck('name','name')->all();
    $user->assignRole($request->input('roles_name'));
    return redirect()->route('users.index',compact('user','roles','userRole'))
    ->with('success','تم اضافة المستخدم بنجاح');
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
    $user = User::find($id);
    return view('users.show',compact('user'));
    }
    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
    $user = User::find($id);
    $roles = Role::pluck('name', 'name')->toArray(); // الحصول على الأدوار المتاحة كزوج من القيم
    $userRole = $user->roles->pluck('name')->toArray();
    return view('users.edit',compact('user','roles','userRole'));
    }
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        /*
    $this->validate($request, [
    'name' => 'required',
    'email' => 'required|email|unique:users,email,'.$id,
    'password' => 'same:confirm-password',
    'roles' => 'required'
    ]);

    */
    $input = $request->all();

       // استخراج البيانات مع استثناء password_confirmation
       $input = $request->except('password_confirmation');

       if(!empty($input['password'])){
        $input['password'] = Hash::make($input['password']);
        }
        else{
        $input = array_except($input,array('password'));
        }

    $user = User::find($id);
    $user->update($input);
    DB::table('model_has_roles')->where('model_id',$id)->delete();
    $user->assignRole($request->input('roles_name'));
    return redirect()->route('users.index')
    ->with('success','تم تحديث معلومات المستخدم بنجاح');
    }
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy(Request $request)
    {
    User::find($request->user_id)->delete();
    return redirect()->route('users.index')->with('success','تم حذف المستخدم بنجاح');
    }
}
