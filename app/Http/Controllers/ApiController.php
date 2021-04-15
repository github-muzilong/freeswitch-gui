<?php

namespace App\Http\Controllers;

use App\Models\Cdr;
use App\Models\Department;
use App\Models\Freeswitch;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\Sip;
use Illuminate\Support\Facades\Redis;

class ApiController extends Controller
{

    /**
     * 通过商户ID获取网关信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGatewayByMerchantId(Request $request)
    {
        $merchant_id = $request->input('merchant_id');
        $data = Gateway::where('merchant_id',$merchant_id)
            ->select([
                'id',
                'merchant_id',
                'name',
            ])
            ->get();
        return Response::json(['code'=>0,'msg'=>'请求成功','data'=>$data]);
    }


    /**
     * 通过商户ID获取部门信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartmentByMerchantId(Request $request)
    {
        $merchant_id = $request->input('merchant_id');
        $data = Department::with('childs')
            ->where('parent_id',0)
            ->where('merchant_id',$merchant_id)
            ->select([
                'id',
                'merchant_id',
                'name',
            ])
            ->get();
        return Response::json(['code'=>0,'msg'=>'请求成功','data'=>$data]);
    }


    //文件上传
    public function upload(Request $request)
    {
        //上传文件最大大小,单位M
        $maxSize = 10;
        //支持的上传图片类型
        $allowed_extensions = ["png", "jpg", "gif"];
        //返回信息json
        $data = ['code'=>1, 'msg'=>'上传失败', 'data'=>''];
        $file = $request->file('file');

        //检查文件是否上传完成
        if ($file->isValid()){
            //检测图片类型
            $ext = $file->getClientOriginalExtension();
            if (!in_array(strtolower($ext),$allowed_extensions)){
                $data['msg'] = "请上传".implode(",",$allowed_extensions)."格式的图片";
                return Response::json($data);
            }
            //检测图片大小
            if ($file->getSize() > $maxSize*1024*1024){
                $data['msg'] = "图片大小限制".$maxSize."M";
                return Response::json($data);
            }
        }else{
            $data['msg'] = $file->getErrorMessage();
            return Response::json($data);
        }
        $newFile = md5(time().uniqid(mt_rand())).".".$file->getClientOriginalExtension();
        try{
            $res = Storage::disk('uploads')->put($newFile,file_get_contents($file->getRealPath()));
        }catch (\Exception $exception){
            Log::info('上传文件失败：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'文件上传失败']);
        }
        $url = public_path('uploads').date('/Y/m/d/').$newFile;
        if($res){
            $data = [
                'code'  => 0,
                'msg'   => '上传成功',
                'data'  => $newFile,
                'url'   => $url,
            ];
        }else{
            $data['data'] = $file->getErrorMessage();
        }
        return Response::json($data);
    }


    /**
     * 拨打接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function dial(Request $request)
    {
        $data = $request->all(['exten','phone','user_data','user_id','callback_url']);
        if ($data['user_id']!=null){
            $sip = Sip::with(['freeswitch','staff'])->where('staff_id',$data['user_id'])->first();
        }else{
            //验证分机信息
            $sip = Sip::with(['freeswitch','staff'])->where('username',$data['exten'])->first();
        }
        if ($sip == null) {
            return Response::json(['code'=>1,'msg'=>' 外呼号未配置']);
        }
        //验证分机是否登录
        if ($sip->status == 0){
            return Response::json(['code'=>1,'msg'=>'当前外呼号未登录']);
        }
        $data['exten'] = $sip->username;
        //验证手机号码
        if (!preg_match('/\d{4,12}/', $data['phone'])) {
            return Response::json(['code'=>1,'msg'=>'客户电话号码格式不正确']);
        }
        //检测10秒重复请求
        if(Redis::get($data['exten'].'_check')!=null){
            return Response::json(['code'=>1,'msg'=>'重复请求，请稍后再试']);
        }else{
            Redis::setex($data['exten'].'_check',10,'exist');
        }
        //呼叫字符串
        $aleg_uuid = md5(\Snowflake::nextId(1).$data['exten'].$data['phone'].Redis::incr('fs_id'));
        $bleg_uuid = md5(\Snowflake::nextId(2).$data['phone'].$data['exten'].Redis::incr('fs_id'));
        $dialStr  = "originate {origination_uuid=".$aleg_uuid."}";
        $dialStr .= "{origination_caller_id_number=".$sip->username."}";
        $dialStr .= "{origination_caller_id_name=".$sip->username."}";

        //验证内部呼叫还是外部呼叫
        $res = Sip::where('username',$data['phone'])->first();

        if ($res == null) { //外部呼叫
            //查询分机的网关信息
            $gateway = Gateway::where('id',$sip->gateway_id)->first();
            if ($gateway == null) {
                return Response::json(['code'=>1,'msg'=>'外呼号无可用的网关']);
            }
            //获取网关出局号码
            if ($gateway->outbound_caller_id) {
                $dialStr .= "{effective_caller_id_number=".$gateway->outbound_caller_id."}";
                $dialStr .= "{effective_caller_id_name=".$gateway->outbound_caller_id."}";
            }
            $dialStr .= "user/".$sip->username." gw".$gateway->id."_";
            //网关后缀
            if ($gateway->prefix){
                $dialStr .=$gateway->prefix;
            }
            $dialStr .= $data['phone']."_".$bleg_uuid;
        }else{ //内部呼叫
            $dialStr .="user/".$sip->username." ".$data["phone"]."_".$bleg_uuid;
        }
        $dialStr .=" XML default";
        try{
            Cdr::create([
                'uuid' => $aleg_uuid,
                'aleg_uuid' => $aleg_uuid,
                'bleg_uuid' => $bleg_uuid,
                'merchant_id' => $sip->merchant_id,
                'department_id' => $sip->staff->department_id,
                'staff_id' => $sip->staff_id,
                'caller' => $sip->username,
                'callee' => $data['phone'],
                'call_time' => date('Y-m-d H:i:s'),
                'user_data' => $data['user_data'],
                'callback_url' => $data['callback_url'],
            ]);
            $fs = new \Freeswitchesl();
            $fs->connect($sip->freeswitch->internal_ip,$sip->freeswitch->esl_port,$sip->freeswitch->esl_password);
            $fs->bgapi($dialStr);
            $fs->disconnect();
            Redis::setex($data['exten'].'_uuid',1200, $aleg_uuid);
            return Response::json(['code'=>0,'msg'=>'呼叫成功','data'=>['uuid'=>$aleg_uuid,'time'=>date('Y-m-d H:i:s')]]);
        }catch (\Exception $exception){
            Log::info("呼叫错误：".$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'呼叫失败']);
        }

    }

    /**
     * 挂断
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function hangup(Request $request)
    {
        $exten = $request->get('exten');
        $uuid = Redis::get($exten.'_uuid');
        if($uuid == null){
            return Response::json(['code'=>0,'msg'=>'已挂断']);
        }
        $sip = Sip::with('freeswitch')->where('username',$exten)->first();
        if ($sip == null) {
            return Response::json(['code'=>1,'msg'=>' 外呼号不存在']);
        }
        try{
            $fs = new \Freeswitchesl();
            $fs->connect($sip->freeswitch->internal_ip,$sip->freeswitch->esl_port,$sip->freeswitch->esl_password);
            $fs->bgapi("uuid_kill",$uuid);
            $fs->disconnect();
            Redis::del($exten.'_uuid');
            return Response::json(['code'=>0,'msg'=>'已挂断']);

        }catch (\Exception $exception){
            Log::info('ESL连接异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'连接异常']);
        }
    }

    /**
     * 语音消息接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function voice(Request $request)
    {
        $data = $request->all(['phone','text','gateway_id']);
        //验证参数
        if (!preg_match('/^1[34578][0-9]{9}$/',$data['phone'])){
            return Response::json(['code'=>1,'msg'=>'号码格式不正确']);
        }
        //验证网关信息
        $gw = Gateway::find($data['gateway_id']);
        if ($gw == null){
            return Response::json(['code'=>1,'msg'=>'网关不存在']);
        }
        //合成语音
        $res = (new Audio())->tts($data['text']);
        if ($res['code']!=0){
            return Response::json(['code'=>1,'msg'=>'语音合成失败']);
        }
        //呼叫
        try{
            $fs = new \Freeswitchesl();
            $service = config('freeswitch.esl');
            $fs->connect($service['host'],$service['port'],$service['password']);
            $dialStr = "originate {ignore_early_media=true}";
            if ($gw->outbound_caller_id){
                $dialStr .= "{effective_caller_id_number=".$gw->outbound_caller_id."}";
                $dialStr .= "{effective_caller_id_name=".$gw->outbound_caller_id."}";
            }
            $dialStr .= "sofia/gateway/gw".$gw->id."/";
            if ($gw->prefix){
                $dialStr .= $gw->prefix.$data['phone'];
            }
            $dialStr .= " &playback(".$res['path'].")";
                $fs->bgapi($dialStr);
            $fs->disconnect();
            return Response::json(['code'=>0,'msg'=>'呼叫成功']);
        }catch (\Exception $exception){
            Log::info('ESL连接异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'呼叫异常']);
        }
    }

    /**
     * fromExten 监听分机
     * toExten 被监听分机
     * type 监听模式
     * type: 1 客户听不到监听者说话
     * type: 2 只能听
     * type: 3 三方正常通话
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chanspy(Request $request)
    {
        $data = $request->all(['fromExten','toExten','type']);
        //验证被监听
        $uuid = Redis::get($data['toExten'].'_uuid');

        $toSip = Sip::where('username',$data['toExten'])->first();
        if ($uuid == null || $toSip->state != 'ACTIVE'){
            return Response::json(['code'=>1,'msg'=>'被监听分机未在通话中']);
        }
        //验证监听，是否登录
        $fromSip = Sip::where('username',$data['fromExten'])->first();
        if ($fromSip->status == 0){
            return Response::json(['code'=>1,'msg'=>'监听分机未注册']);
        }

        $fs = new \Freeswitchesl();
        $service = config('freeswitch.esl');
        try{
            $fs->connect($service['host'],$service['port'],$service['password']);
            $dailStr  = "originate ";
            $dailStr .= "{origination_caller_id_number=".$data['fromExten']."}";
            $dailStr .= "{origination_caller_id_name=".$data['fromExten']."}";
            $dailStr .= "user/".$data['fromExten'];
            if ($data['type']==3){
                $dailStr .= " &three_way(".$uuid.")";
            }elseif ($data['type']==2){
                $dailStr .= " &{eavesdrop_whisper_aleg=false}{eavesdrop_whisper_bleg=false}eavesdrop(".$uuid.")";
            }elseif ($data['type']==1){
                $dailStr .= " &{eavesdrop_whisper_aleg=true}{eavesdrop_whisper_bleg=false}eavesdrop(".$uuid.")";
            }else{
                return Response::json(['code'=>1,'msg'=>'监听模式错误']);
            }
            $fs->bgapi($dailStr);
            return Response::json(['code'=>0,'msg'=>'监听成功']);
        }catch (\Exception $exception){
            Log::info('监听ESL连接异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'连接异常']);
        }

    }


}
