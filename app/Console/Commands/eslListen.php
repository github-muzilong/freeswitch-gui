<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class eslListen extends Command
{
    /**
     * 允许事件：'CHANNEL_ANSWER',
                'RECORD_START',
                'RECORD_STOP',
                'CHANNEL_HANGUP_COMPLETE',
     * 多个事件以空格隔开，例：CHANNEL_ANSWER RECORD_START RECORD_STOP CHANNEL_HANGUP_COMPLETE
     * 如果指定uuid则表示只监听指定的uuid的事件
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'esl:listen {event*} {--aleg_uuid=} {--bleg_uuid=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'esl listen events';
    protected $fs_dir = '/usr/local/freeswitch';
    public $url = null;
    public $machineId = 2;
    public $asr_table = null;
    public $cdr_table = null;
    public $asr_status_key = 'asr_status_key'; //控制是否开启分段录音asr识别的redis key

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = config('app.url');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fs = new \Freeswitchesl();
        $service = config('freeswitch.esl');
        if (!$fs->connect($service['host'], $service['port'], $service['password'])){
            Log::error("asr监听ESL未连接");
            return false;
        }
        //======================  接收事件参数验证  ====================
        $eventarr = [
            'CHANNEL_CALLSTATE',
            'CHANNEL_ANSWER',
            'RECORD_START',
            'RECORD_STOP',
            'CHANNEL_HANGUP_COMPLETE',
        ];
        $argument = $this->argument('event');
        foreach ($argument as $name){
            if (!in_array($name,$eventarr)){
                $this->error('event '.$name.' not allowed');
                return false;
            }
        }
        $event = implode(" ",$argument);
        //======================  接收事件参数验证  ====================

        //====================== 是否监听指定的uuid的事件 ===============
        $aleg_uuid = $this->option('aleg_uuid');
        $bleg_uuid = $this->option('bleg_uuid');
        if ($aleg_uuid){
            $fs->filteruuid($aleg_uuid);
        }
        if ($bleg_uuid){
            $fs->filteruuid($bleg_uuid);
        }
        //====================== 是否监听指定的uuid的事件 ===============
        $fs->events('plain', $event);
        while (true) {
            //录音目录
            $filepath = $this->fs_dir . '/recordings/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
            $received_parameters = $fs->recvEvent();
            if (!empty($received_parameters)) {
                $this->setTable();
                $info                   = $fs->serialize($received_parameters, "json");
                $info                   = json_decode($info,true);
                $eventname              = Arr::get($info,"Event-Name"); //事件名称
                $uuid                   = Arr::get($info,"Unique-ID"); //UUID
                $CallerCallerIDNumber   = Arr::get($info,"Caller-Caller-ID-Number"); //主叫
                $CallerCalleeIDNumber   = Arr::get($info,"Caller-Destination-Number"); //被叫

                switch ($eventname){
                    //呼叫状态
                    case 'CHANNEL_CALLSTATE':
                        //是分机号才记录
                        if (preg_match('/\d{4,5}/',$CallerCallerIDNumber)){
                            $state = Arr::get($info,'Channel-Call-State');
                            $uniqueid = Arr::get($info,'Caller-Unique-ID');
                            Redis::setex($CallerCallerIDNumber.'_uuid',1200, $uniqueid);
                            DB::table('sip')->where('username',$CallerCallerIDNumber)->update(['state'=>$state]);
                        }
                        break;
                    //通道应答
                    case 'CHANNEL_ANSWER':
                        $otherUuid = Arr::get($info,"Other-Leg-Unique-ID");
                        $cdr_uuid = md5($uuid.Redis::incr('cdr_uuid_incr_key'));
                        Redis::setex($uuid,1800,json_encode([
                            'uuid' => $cdr_uuid,
                            'full_record_file' => null,
                        ]));
                        if ($otherUuid) { //被叫应答后
                            //开启全程录音
                            $fullfile = $filepath . 'full_' . $cdr_uuid . '.wav';
                            $fs->bgapi("uuid_record {$uuid} start {$fullfile} 7200"); //录音
                            Redis::setex($otherUuid,1800,json_encode([
                                'uuid' => $cdr_uuid,
                                'full_record_file' => $fullfile,
                            ]));
                            Redis::setex($uuid,1800,json_encode([
                                'uuid' => $cdr_uuid,
                                'full_record_file' => $fullfile,
                            ]));
                            if (Redis::get($this->asr_status_key)==1) {

                                //记录A分段录音数据
                                $halffile_a = $filepath . 'half_' . md5($otherUuid . time() . uniqid()) . '.wav';
                                $fs->bgapi("uuid_record " . $otherUuid . " start " . $halffile_a . " 18");
                                Redis::setex($otherUuid,1800,json_encode([
                                    'uuid' => $cdr_uuid,
                                    'leg_uuid' => $otherUuid,
                                    'record_file' => $halffile_a,
                                    'full_record_file' => $fullfile,
                                    'start_at' => date('Y-m-d H:i:s'),
                                    'end_at' => null,
                                ]));

                                //记录B分段录音数据
                                $halffile_b = $filepath . 'half_' . md5($uuid . time() . uniqid()) . '.wav';
                                $fs->bgapi("uuid_record " . $uuid . " start " . $halffile_b . " 18");
                                Redis::set($uuid,json_encode([
                                    'uuid' => $cdr_uuid,
                                    'leg_uuid' => $uuid,
                                    'record_file' => $halffile_b,
                                    'full_record_file' => $fullfile,
                                    'start_at' => date('Y-m-d H:i:s'),
                                    'end_at' => null,
                                ]));
                                unset($halffile_a);
                                unset($halffile_b);
                            }
                            unset($fullfile);
                        }
                        break;
                    //开始说话
                    case 'RECORD_START':
                        $channel = Redis::get($uuid);
                        if ($channel){
                            $data = array_merge(json_decode($channel,true),[
                                'start_at' => date('Y-m-d H:i:s'),
                            ]);
                            Redis::set($uuid,json_encode($data));
                        }
                        break;
                    //结束说话
                    case 'RECORD_STOP':
                        if (Redis::get($this->asr_status_key)==1) {
                            $channel = Redis::get($uuid);
                            if ($channel){
                                $data = json_decode($channel,true);
                                if (isset($data['record_file'])&&file_exists($data['record_file'])){
                                    Redis::rPush('esl_cdr_key',json_encode([
                                        'table' => $this->asr_table,
                                        'update_data' => [
                                            'uuid' => $data['uuid'],
                                            'leg_uuid' => $data['leg_uuid'],
                                            'start_at' => $data['start_at'],
                                            'end_at' => date('Y-m-d H:i:s'),
                                            'billsec' => strtotime(date('Y-m-d H:i:s'))-strtotime($data['start_at']),
                                            'record_file' => str_replace($this->fs_dir, $this->url, $data['record_file']),
                                        ],
                                        'type' => 2,
                                    ]));
                                }
                                //结束说话 后接着开启分段录音
                                $halffile = $filepath . 'half_' . md5($uuid . time() . uniqid()) . '.wav';
                                $fs->bgapi("uuid_record " . $uuid . " start " . $halffile . " 18");
                                Redis::set($uuid,json_encode(array_merge($data,[
                                    'record_file' => $halffile,
                                    'start_at' => date('Y-m-d H:i:s'),
                                    'end_at' => null,
                                ])));
                                unset($data);
                                unset($halffile);
                            }
                            unset($channel);
                        }
                        break;
                    //挂断
                    case 'CHANNEL_HANGUP_COMPLETE':
                        $channel = Redis::get($uuid);
                        if ($channel){
                            $channelData = json_decode($channel,true);
                            $record_file = Arr::get($channelData,'full_record_file',null);
                            $record_file = $record_file ? str_replace($this->fs_dir,$this->url,$record_file) : null;
                            Redis::del($uuid);
                        }else{
                            $record_file = null;
                            continue 2; //继续外层的while循环
                        }
                        $otherType = Arr::get($info,'Other-Type');
                        $otherUuid = Arr::get($info,'Other-Leg-Unique-ID');
                        $start = Arr::get($info,'variable_start_stamp');
                        $answer = Arr::get($info,'variable_answer_stamp');
                        $end = Arr::get($info,'variable_end_stamp');
                        $extend_content = Arr::get($info,'variable_user_data',null);
                        $extend_content = $extend_content ? decrypt($extend_content) : $extend_content;
                        $duration = (int)Arr::get($info,'variable_duration',0);
                        $billsec = (int)Arr::get($info,'variable_billsec',0);
                        $customer_caller = Arr::get($info,'variable_customer_caller',null);

                        if (empty($otherType) || $otherType == 'originatee') {
                            Redis::del($CallerCallerIDNumber.'_uuid');
                            $data = [
                                'table_name' => $this->cdr_table,
                                'leg_type' => 'A',
                                'uuid' => $channelData['uuid'],
                                'update_data' => [
                                    'aleg_uuid' => $uuid,
                                    'src' => $CallerCallerIDNumber,
                                    'dst' => $customer_caller?$customer_caller:$CallerCalleeIDNumber,
                                    'aleg_start_at' => $start ? urldecode($start) : null,
                                    'aleg_answer_at' => $answer ? urldecode($answer) : null,
                                    'aleg_end_at' => $end ? urldecode($end) : null,
                                    'duration' => $duration,
                                    'record_file' => $record_file,
                                    'user_data' => $extend_content,
                                ],
                                'type' => 1,
                            ];
                        }else{
                            $data = [
                                'table_name' => $this->cdr_table,
                                'leg_type' => 'B',
                                'uuid' => $channelData['uuid'],
                                'update_data' => [
                                    'bleg_uuid' => $uuid,
                                    'bleg_start_at' => $start ? urldecode($start) : null,
                                    'bleg_answer_at' => $answer ? urldecode($answer) : null,
                                    'bleg_end_at' => $end ? urldecode($end) : null,
                                    'billsec' => $billsec,
                                ],
                                'type' => 1,
                            ];
                        }
                        Redis::rPush('esl_cdr_key',json_encode($data));
                        Redis::get($uuid);
                        unset($data);
                        break;
                    default:
                        break;
                }
            }
        }
        $fs->disconnect();
    }

    public function setTable(){
        $this->cdr_table = 'cdr';
        $this->asr_table = 'asr';
    }

}