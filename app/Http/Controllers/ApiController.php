<?php

namespace App\Http\Controllers;

use App\Models\Cdr;
use App\Models\CustomerRemark;
use App\Models\Department;
use App\Models\Node;
use App\Models\Order;
use App\Models\OrderPay;
use App\Models\OrderRemark;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    public function getPermissionByRoleId(Request $request)
    {
        $role_id = $request->input('role_id');
        $role = null;
        $checkedIds = [];
        if ($role_id) {
            $role = Role::query()->where('id', $role_id)->first();
        }
        $permissions = Permission::query()->orderByDesc('id')->get();
        foreach ($permissions as $permission) {
            if ($role != null) {
                if ($role->hasPermissionTo($permission)) {
                    array_push($checkedIds, $permission->id);
                }
            }
        }
        return $this->success('ok', ['trees' => $permissions, 'checkedId' => $checkedIds]);
    }

    public function getRoleByUserId(Request $request)
    {
        $user_id = $request->input('user_id');
        $user = null;
        if ($user_id) {
            $user = User::query()->where('id', $user_id)->first();
        }
        $roles = Role::query()->orderByDesc('id')->get();
        foreach ($roles as $role) {
            $role->selected = $user != null && $user->hasRole($role);
        }
        return $this->success('ok', $roles);
    }

    public function getDepartmentByUserId(Request $request)
    {
        $user_id = $request->input('user_id');
        $user = null;
        if ($user_id) {
            $user = User::query()->where('id', $user_id)->first();
        }
        $departments = Department::query()->orderByDesc('id')->get();
        foreach ($departments as $d) {
            $d->selected = $user != null && $user->department_id == $d->id;
        }
        $data = recursive($departments);
        return $this->success('ok', $data);
    }

    public function getUser(Request $request)
    {
        $user_id = $request->input('user_id');
        $users = User::query()->get();
        foreach ($users as $user){
            $user->selected = $user_id == $user->id;
        }
        return $this->success('ok',$users);
    }

    public function getNode(Request $request)
    {
        $node_id = $request->input('node_id');
        $type = $request->input('type');
        $nodes = Node::query()
            ->whereIn('type',[1,$type])
            ->orderBy('sort','asc')
            ->orderBy('id','asc')
            ->get();
        foreach ($nodes as $node){
            $node->selected = $node_id == $node->id;
        }
        return $this->success('ok',$nodes);
    }


    public function remarkList(Request $request)
    {
        $node_type = $request->input('type');
        $id = $request->input('id');
        if ($node_type == 2){
            $res = CustomerRemark::query()->where('customer_id','=',$id)->orderByDesc('id')->paginate($request->get('limit', 2));
        }elseif ($node_type == 3){
            $res = OrderRemark::query()->where('order_id','=',$id)->orderByDesc('id')->paginate($request->get('limit', 2));
        }
        return $this->success('ok',['list'=>$res->items(),'lastPage'=>$res->lastPage()]);
    }


    /**
     * ????????????
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function call(Request $request)
    {
        $user_id = $request->input('user_id');
        $caller = $request->input('caller');
        $callee = $request->input('callee');
        $user_data = $request->input('user_data');
        if ($caller != null){
            $sip = Sip::query()->where('username',$caller)->first();
            if ($sip == null){
                return $this->error('??????????????????');
            }
            if ($sip->status != 1) {
                return $this->error('??????????????????');
            }
            $user = User::query()->where('sip_id', '=', $sip->id)->first();
        }else{
            $user = User::query()->with('sip')->where('id', '=', $user_id)->first();
            if ($user == null){
                return $this->error('??????ID?????????');
            }
            if ($user->sip == null) {
                return $this->error('????????????????????????');
            }
            if ($user->sip->status != 1) {
                return $this->error('????????????????????????');
            }
            $sip = $user->sip;
        }

        try {
            $cdr = Cdr::create([
                'uuid' => uuid_generate(),
                'aleg_uuid' => uuid_generate(),
                'bleg_uuid' => uuid_generate(),
                'caller' => $sip->username,
                'callee' => $callee,
                'department_id' => $user->department_id??0,
                'user_id' => $user->id??0,
                'user_nickname' => $user->nickname??null,
                'sip_id' => $sip->id,
                'user_data' => $user_data,
                'gateway_id' => $sip->gateway_id ?? 0,
            ]);
            Redis::rpush(config('freeswitch.redis_key.dial'), $cdr->uuid);
            return $this->success('????????????', [
                'uuid' => $cdr->uuid,
                'call_time' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $exception) {
            Log::error('???????????????' . $exception->getMessage());
            return $this->error('????????????');
        }
    }

    public function hangup(Request $request)
    {
        $uuid = $request->input('uuid');
        if($uuid == null){
            return $this->error('????????????');
        }
        $cdr = Cdr::query()->where('uuid',$uuid)->first();
        if ($cdr==null){
            return $this->error('????????????');
        }
        if (!$cdr->end_time){
            Redis::rpush(config('freeswitch.redis_key.api'),'uuid_kill '.$cdr->aleg_uuid);
        }
        return $this->success('?????????');
    }

    public function chanspy(Request $request)
    {
        $data = $request->all(['fromExten','toExten','type']);

        $toSip = Sip::where('username',$data['toExten'])->first();
        if ($toSip->status == 0){
            return $this->error('?????????????????????');
        }
        //???????????????????????????
        $fromSip = Sip::where('username',$data['fromExten'])->first();
        if ($fromSip->status == 0){
            return $this->error('????????????????????????');
        }
        $cdr = Cdr::query()
            ->where('caller',$toSip)
            ->whereNotNull('answer_time')
            ->whereNull('end_time')
            ->orderByDesc('id')
            ->first();
        if ($cdr == null){
            return $this->error('??????????????????????????????');
        }
        $dailStr = "originate {origination_caller_id_number=".$data['fromExten']."}";
        $dailStr .= "{origination_caller_id_name=".$data['fromExten']."}";
        $dailStr .= "user/".$data['fromExten'];
        if ($data['type']==3){
            $dailStr .= " &three_way(".$cdr->aleg_uuid.")";
        }elseif ($data['type']==2){
            $dailStr .= " &{eavesdrop_whisper_aleg=false}{eavesdrop_whisper_bleg=false}eavesdrop(".$cdr->aleg_uuid.")";
        }elseif ($data['type']==1){
            $dailStr .= " &{eavesdrop_whisper_aleg=true}{eavesdrop_whisper_bleg=false}eavesdrop(".$cdr->aleg_uuid.")";
        }else{
            return $this->error('??????????????????');
        }
        Redis::rpush(config('freeswitch.redis_key.api'),$dailStr);
        return $this->success('????????????');
    }

    //????????????
    public function upload(Request $request)
    {

        //????????????????????????,??????M
        $maxSize = 10;
        //???????????????????????????
        $allowed_extensions = ["png", "jpg", "gif", "xlsx", "xls"];
        $file = $request->file('file');
        //??????????????????????????????
        if ($file->isValid()) {
            //??????????????????
            $ext = $file->getClientOriginalExtension();
            if (!in_array(strtolower($ext), $allowed_extensions)) {
                return $this->success("?????????" . implode(",", $allowed_extensions) . "???????????????");
            }
            //??????????????????
            if ($file->getSize() > $maxSize * 1024 * 1024) {
                return $this->success("??????????????????" . $maxSize . "M");
            }
        } else {
            return $this->error('???????????????');
        }
        try {
            $newFile = date('Y/m/d/') . uuid_generate() . "." . $file->getClientOriginalExtension();
            $disk = Storage::disk('uploads');
            $res = $disk->put($newFile, file_get_contents($file->getRealPath()));
            if ($res) {
                $data = [
                    'url' => '/uploads/' . $newFile,
                ];
                return $this->success('????????????', $data);
            } else {
                Log::error('?????????????????????' . $file->getErrorMessage());
                $this->error('????????????');
            }
        }catch (\Exception $exception){
            Log::error('?????????????????????' . $exception->getMessage());
            $this->error('????????????');
        }

    }

    public function payList(Request $request)
    {
        $id = $request->input('id');
        $res = OrderPay::query()->where('order_id','=',$id)->orderByDesc('id')->paginate($request->get('limit', 2));
        return $this->success('ok',['list'=>$res->items(),'lastPage'=>$res->lastPage()]);
    }

    public function getSipsByQueueId(Request $request)
    {
        $queueId = $request->input('queue_id');
        $lists = Sip::with('user')->get();
        $values = [];
        if ($queueId){
            $values = DB::table('queue_sip')->where('queue_id',$queueId)->pluck('sip_id')->toArray();
        }
        foreach ($lists as $item){
            $item->checked = in_array($item->id,$values) ? true : false;
        }
        return $this->success('ok',['lists'=>$lists,'values'=>$values]);
    }


}
