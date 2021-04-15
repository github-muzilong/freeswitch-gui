<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\System\Admin\ChangePasswordRequest;
use App\Http\Requests\Backend\System\Admin\ResetPasswordRequest;
use App\Http\Requests\Backend\System\Admin\StoreRequest;
use App\Http\Requests\Backend\System\Admin\UpdateRequest;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class AdminController extends Controller
{
    use AuthenticatesUsers;

    /**
     * 登录成功后的跳转地址
     * @var string
     */
    public $redirectTo = '/backend';

    /**
     * 用户登录表单
     * @return \Illuminate\Contracts\View\View
     */
    public function showLoginForm()
    {
        return View::make('backend.system.admin.login');
    }

    /**
     * 验证登录字段
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            //'captcha' => 'required|captcha',
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * 退出后的动作
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function loggedOut()
    {
        Session::forget('backend_menus');
        return Redirect::route('backend.system.admin.login');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('backend');
    }

    protected function authenticated(Request $request, $user)
    {
        $user->update([
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $request->ip(),
        ]);
        //缓存后台菜单
        $menus = $user->menus();
        Session::put('backend_menus',$menus);

    }

    /**
     * 用于登录的字段
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * 更改密码
     * @return \Illuminate\Contracts\View\View
     */
    public function changeMyPasswordForm()
    {
        return View::make('backend.system.admin.changeMyPassword');
    }

    /**
     * 修改自己的密码
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeMyPassword(ChangePasswordRequest $request)
    {
        $data = $request->all(['old_password','new_password']);
        //验证原密码
        if (!Hash::check($data['old_password'],$request->user()->getAuthPassword())){
            return Response::json(['code'=>1,'msg'=>'原密码不正确']);
        }
        try{
            $request->user()->fill(['password' => bcrypt($data['new_password'])])->save();
            return Response::json(['code'=>0,'msg'=>'密码修改成功']);
        }catch (\Exception $exception){
            Log::error('修改密码异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'修改失败']);
        }
    }

    /**
     * 用户列表主页
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()){
            $res = Admin::orderByDesc('id')->paginate($request->get('limit', 30));
            $data = [
                'code' => 0,
                'msg' => '正在请求中...',
                'count' => $res->total(),
                'data' => $res->items()
            ];
            return Response::json($data);
        }
        return View::make('backend.system.admin.index');
    }

    /**
     * 添加用户
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return View::make('backend.system.admin.create');
    }

    /**
     * 添加用户
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $data = $request->all(['phone','nickname','password','username']);
        try{
            Admin::create($data);
            return Response::json(['code'=>0,'msg'=>'添加成功','url'=>route('backend.system.admin')]);
        }catch (\Exception $exception){
            Log::error('添加后台用户异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'添加失败']);
        }
    }

    /**
     * 更新用户
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $user = Admin::findOrFail($id);
        return View::make('backend.system.admin.edit',compact('user'));
    }

    /**
     * 更新用户
     * @param UpdateRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $id)
    {
        $user = Admin::findOrFail($id);
        $data = $request->only(['phone','nickname','username']);
        try{
            $user->update($data);
            return Response::json(['code'=>0,'msg'=>'更新成功','url'=>route('backend.system.admin')]);
        }catch (\Exception $exception){
            Log::error('更新后台用户信息异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'更新失败']);
        }
    }

    /**
     * 重置用户密码表单
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function resetPasswordForm($id)
    {
        $user = Admin::findOrFail($id);
        return View::make('backend.system.admin.resetPassword',compact('user'));
    }

    /**
     * 重置用户密码
     * @param ResetPasswordRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request,$id)
    {
        $user = Admin::findOrFail($id);
        $data = $request->all(['new_password']);
        try{
            $user->update(['password'=>bcrypt($data['new_password'])]);
            return Response::json(['code'=>0,'msg'=>'更新成功']);
        }catch (\Exception $exception){
            Log::error('重置后台用户密码异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'更新失败']);
        }
    }

    /**
     * 删除用户
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)){
            return Response::json(['code'=>1,'msg'=>'请选择删除项']);
        }
        try{
            Admin::destroy($ids);
            return Response::json(['code'=>0,'msg'=>'删除成功']);
        }catch (\Exception $exception){
            Log::error('删除后台用户异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'删除失败']);
        }
    }

    /**
     * 分配角色
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function role($id)
    {
        $user = Admin::findOrFail($id);
        $roles = Role::where('guard_name','=',config('freeswitch.backend_guard'))->get();
        foreach ($roles as $role){
            $role->own = $user->hasRole($role) ? true : false;
        }
        return View::make('backend.system.admin.role',compact('roles','user'));
    }

    /**
     * 分配角色
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRole(Request $request,$id)
    {
        $user = Admin::findOrFail($id);
        $roles = $request->get('roles',[]);
        try{
            $user->syncRoles($roles);
            return Response::json(['code'=>0,'msg'=>'更新成功']);
        }catch (\Exception $exception){
            Log::error('为后台用户分配角色异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'更新失败']);
        }
    }

    /**
     * 分配直接权限
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function permission($id)
    {
        $user = Admin::findOrFail($id);
        $permissions = Permission::with('childs')
            ->where('guard_name',config('freeswitch.backend_guard'))
            ->where('parent_id',0)
            ->get();
        foreach ($permissions as $p1){
            $p1->own = $user->hasDirectPermission($p1->id) ? 'checked' : '' ;
            if ($p1->childs->isNotEmpty()){
                foreach ($p1->childs as $p2){
                    $p2->own = $user->hasDirectPermission($p2->id) ? 'checked' : '' ;
                    if ($p2->childs->isNotEmpty()){
                        foreach ($p2->childs as $p3){
                            $p3->own = $user->hasDirectPermission($p3->id) ? 'checked' : '' ;
                        }
                    }
                }
            }
        }
        return View::make('backend.system.admin.permission',compact('user','permissions'));
    }

    /**
     * 分配直接权限
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPermission(Request $request,$id)
    {
        $user = Admin::findOrFail($id);
        $permissions = $request->get('permissions',[]);
        try{
            $user->syncPermissions($permissions);
            return Response::json(['code'=>0,'msg'=>'更新成功']);
        }catch (\Exception $exception){
            Log::error('为后台用户分配权限异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'更新失败']);
        }
    }

}
