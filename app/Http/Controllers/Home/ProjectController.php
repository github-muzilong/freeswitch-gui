<?php

namespace App\Http\Controllers\Home;

use App\Exports\ProjectExport;
use App\Http\Requests\ProjectRequest;
use App\Imports\ProjectImport;
use App\Models\Merchant;
use App\Models\Node;
use App\Models\Project;
use App\Models\ProjectDesign;
use App\Models\ProjectNode;
use App\Models\ProjectRemark;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{

    /**
     * 项目列表
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $user = Auth::guard('merchant')->user();
        if ($user->merchant_id==0){
            $merchant_id = $user->id;
        }else{
            $merchant_id = $user->merchant_id;
        }
        $nodes = Node::where('merchant_id',$merchant_id)
            ->orderBy('sort','asc')
            ->get();
        $merchants = Merchant::where('merchant_id',$merchant_id)->get();
        return View::make('home.project.index',compact('nodes','merchants'));
    }

    public function data(Request $request)
    {
        $user = Auth::guard('merchant')->user();
        $data = $request->all([
            'name',
            'phone',
            'follow_merchant_id',
            'created_merchant_id',
            'node_id',
            'follow_at_start',
            'follow_at_end',
            'next_follow_at_start',
            'next_follow_at_end',
            'created_at_start',
            'created_at_end',
        ]);
        $res = Project::with(['node','followMerchant'])
            ->where(function ($query) use($user){
                return $query->where('follow_merchant_id',$user->id)->orWhere('created_merchant_id',$user->id);
            })
            //姓名
            ->when($data['name'],function ($query) use($data){
                return $query->where('name',$data['name']);
            })
            //联系电话
            ->when($data['phone'],function ($query) use($data){
                return $query->where('phone',$data['phone']);
            })
            //节点
            ->when($data['node_id'],function ($query) use($data){
                return $query->where('node_id',$data['node_id']);
            })
            //跟进时间
            ->when($data['follow_at_start']&&!$data['follow_at_end'],function ($query) use($data){
                return $query->where('follow_at','>=',$data['follow_at_start']);
            })
            ->when(!$data['follow_at_start']&&$data['follow_at_end'],function ($query) use($data){
                return $query->where('follow_at','<=',$data['follow_at_end']);
            })
            ->when($data['follow_at_start']&&$data['follow_at_end'],function ($query) use($data){
                return $query->whereBetween('follow_at',[$data['follow_at_start'],$data['follow_at_end']]);
            })
            //下次跟进时间
            ->when($data['next_follow_at_start']&&!$data['next_follow_at_end'],function ($query) use($data){
                return $query->where('next_follow_at','>=',$data['next_follow_at_start']);
            })
            ->when(!$data['next_follow_at_start']&&$data['next_follow_at_end'],function ($query) use($data){
                return $query->where('next_follow_at','<=',$data['next_follow_at_end']);
            })
            ->when($data['next_follow_at_start']&&$data['next_follow_at_end'],function ($query) use($data){
                return $query->whereBetween('next_follow_at',[$data['next_follow_at_start'],$data['next_follow_at_end']]);
            })
            //创建时间
            ->when($data['created_at_start']&&!$data['created_at_end'],function ($query) use($data){
                return $query->where('created_at','>=',$data['created_at_start']);
            })
            ->when(!$data['created_at_start']&&$data['created_at_end'],function ($query) use($data){
                return $query->where('created_at','<=',$data['created_at_end']);
            })
            ->when($data['created_at_start']&&$data['created_at_end'],function ($query) use($data){
                return $query->whereBetween('created_at',[$data['created_at_start'],$data['created_at_end']]);
            })
            ->paginate($request->get('limit', 30));

        $data = [
            'code' => 0,
            'msg' => '正在请求中...',
            'count' => $res->total(),
            'data' => $res->items()
        ];
        return Response::json($data);
    }

    /**
     * 添加项目
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $user = Auth::guard('merchant')->user();
        if ($user->merchant_id==0){
            $merchant_id = $user->id;
        }else{
            $merchant_id = $user->merchant_id;
        }
        $designs = ProjectDesign::where('merchant_id',$merchant_id)
            ->where('visiable',1)
            ->orderBy('sort','asc')
            ->get();
        return View::make('home.project.create',compact('designs'));
    }

    /**
     * 添加项目
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProjectRequest $request)
    {
        $user = Auth::guard('merchant')->user();
        if ($user->merchant_id==0){
            $merchant_id = $user->id;
        }else{
            $merchant_id = $user->merchant_id;
        }
        $data = $request->all(['company_name','name','phone']);
        $dataInfo = [];
        $fields = ProjectDesign::where('merchant_id',$merchant_id)->where('visiable',1)->get();

        foreach ($fields as $d){
            $items = [
                'project_design_id' => $d->id,
                'data' => $request->get($d->field_key),
            ];
            if ($d->field_type=='checkbox'){
                if (!empty($items['data'])){
                    $items['data'] = implode(',',$items['data']);
                }else{
                    $items['data'] = null;
                }
            }
            array_push($dataInfo,$items);
        }

        try{
            $project = Project::create([
                'company_name' => $data['company_name'],
                'name' => $data['name'],
                'phone' => $data['phone'],
                'created_merchant_id' => $user->id,
            ]);
            if ($project){
                foreach ($dataInfo as $d){
                    DB::table('project_design_value')->insert([
                        'project_id' => $project->id,
                        'project_design_id' => $d['project_design_id'],
                        'data' => $d['data'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
            return Redirect::route('home.project')->with(['success'=>'添加成功']);
        }catch (\Exception $exception){
            Log::info('添加项目异常：'.$exception->getMessage());
            return Redirect::back()->withInput()->withErrors('添加失败');
        }

    }

    /**
     * 更新项目
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $model = Project::with('designs')->findOrFail($id);
        return View::make('home.project.edit',compact('model'));
    }

    /**
     * 更新项目
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProjectRequest $request,$id)
    {
        $user = Auth::guard('merchant')->user();
        $data = $request->all(['company_name','name','phone']);
        $dataInfo = [];
        $model = Project::with('designs')->findOrFail($id);
        foreach ($model->designs as $d){
            $items = [
                'id' => $d->pivot->id,
                'data' => $request->get($d->field_key),
            ];
            if ($d->field_type=='checkbox'){
                if (!empty($items['data'])){
                    $items['data'] = implode(',',$items['data']);
                }else{
                    $items['data'] = null;
                }
            }
            array_push($dataInfo,$items);
        }

        DB::beginTransaction();
        try{
            DB::table('project')->where('id',$id)->update([
                'company_name' => $data['company_name'],
                'name' => $data['name'],
                'phone' => $data['phone'],
                'updated_merchant_id' => $user->id,
                'updated_at' => Carbon::now(),
            ]);
            foreach ($dataInfo as $d){
                DB::table('project_design_value')->where('id',$d['id'])->update(['data'=>$d['data']]);
            }
            DB::commit();
            return Redirect::route('home.project')->with(['success'=>'更新成功']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info('更新项目异常：'.$exception->getMessage());
            return Redirect::back()->withInput()->withErrors('更新失败');
        }
    }

    /**
     * 删除项目
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $ids = $request->get('ids',[]);
        $id = $ids[0];
        $project = Project::findOrFail($id);
        DB::beginTransaction();
        try{
            DB::table('project')->where('id',$id)->update([
                'deleted_merchant_id' => Auth::guard('merchant')->user()->id,
                'deleted_at' => Carbon::now(),
            ]);
            DB::commit();
            return Response::json(['code'=>0,'msg'=>'删除成功']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info('删除项目异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'删除失败']);
        }
    }

    /**
     * 项目详情
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $model = Project::with('designs')->findOrFail($id);
        return View::make('home.project.show',compact('model'));
    }

    /**
     * 更新节点
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function node($id)
    {
        $model = Project::findOrFail($id);
        $nodes = Node::where('merchant_id',Auth::guard('merchant')->user()->merchant_id)->orderBy('sort','asc')->get();
        return View::make('home.project.node',compact('model','nodes'));
    }

    /**
     * 更新节点
     * @param ProjectRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function nodeStore(ProjectRequest $request,$id)
    {
        $model = Project::findOrFail($id);
        $data = $request->all(['node_id','content']);
        $old = $model->node_id;
        $user = Auth::guard('merchant')->user();
        DB::beginTransaction();
        try{
            DB::table('project_node')->insert([
                'project_id' => $id,
                'old' => $old,
                'new' => $data['node_id'],
                'content' => $data['content'],
                'merchant_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            DB::table('project')->where('id',$id)->update([
                'node_id' => $data['node_id'],
                'updated_merchant_id' => $user->id,
                'updated_at' => Carbon::now()
            ]);
            DB::commit();
            return Redirect::route('home.project.show',['id'=>$id])->with(['success'=>'更新成功']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info('更新节点异常：'.$exception->getMessage());
            return Redirect::back()->withInput()->withErrors('更新失败');
        }
    }

    /**
     * 项目的节点变更记录
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function nodeList(Request $request,$id)
    {
        $res = ProjectNode::with(['oldNode','newNode','merchant'])
            ->where('project_id',$id)
            ->orderByDesc('id')
            ->paginate($request->get('limit', 30));
        $data = [
            'code' => 0,
            'msg' => '正在请求中...',
            'count' => $res->total(),
            'data' => $res->items()
        ];
        return Response::json($data);
    }

    /**
     * 更新备注
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function remark($id)
    {
        $model = Project::findOrFail($id);
        return View::make('home.project.remark',compact('model'));
    }

    /**
     * 更新备注
     * @param ProjectRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remarkStore(ProjectRequest $request,$id)
    {
        $model = Project::findOrFail($id);
        $data = $request->all(['next_follow_at','content']);
        $user = Auth::guard('merchant')->user();
        DB::beginTransaction();
        try{
            DB::table('project_remark')->insert([
                'project_id' => $id,
                'content' => $data['content'],
                'next_follow_at' => $data['next_follow_at'],
                'merchant_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            DB::table('project')->where('id',$id)->update([
                'next_follow_at' => $data['next_follow_at'],
                'follow_at' => Carbon::now(),
                'follow_merchant_id' => $user->id,
                'updated_merchant_id' => $user->id,
                'updated_at' => Carbon::now()
            ]);
            DB::commit();
            return Redirect::route('home.project.show',['id'=>$id])->with(['success'=>'更新成功']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info('更新备注异常：'.$exception->getMessage());
            return Redirect::back()->withInput()->withErrors('更新失败');
        }

    }

    /**
     * 备注记录
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function remarkList(Request $request,$id)
    {
        $res = ProjectRemark::with(['merchant'])
            ->where('project_id',$id)
            ->orderByDesc('id')
            ->paginate($request->get('limit', 30));
        $data = [
            'code' => 0,
            'msg' => '正在请求中...',
            'count' => $res->total(),
            'data' => $res->items()
        ];
        return Response::json($data);
    }

    /**
     * 下载模板
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTemplate()
    {
        return Excel::download(new ProjectExport(),'project.xlsx');
    }

    /**
     * 导入项目
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        //上传文件最大大小,单位M
        $maxSize = 10;
        //支持的上传类型
        $allowed_extensions = ["xls", "xlsx"];
        $file = $request->file('file');
        //检查文件是否上传完成
        if ($file->isValid()){
            //检测类型
            $ext = $file->getClientOriginalExtension();
            if (!in_array(strtolower($ext),$allowed_extensions)){
                return Response::json(['code'=>1,'msg'=>"请上传".implode(",",$allowed_extensions)."格式的图片"]);
            }
            //检测大小
            if ($file->getSize() > $maxSize*1024*1024){
                return Response::json(['code'=>1,'msg'=>"图片大小限制".$maxSize."M"]);
            }
        }else{
            Log::info('导入项目是文件上传不完整:'.$file->getErrorMessage());
            return Response::json(['code'=>1,'msg'=>'文件上传不完整']);
        }
        $newFile = date('Y-m-d')."_".time()."_".uniqid().".".$file->getClientOriginalExtension();
        try{
            $res = Storage::disk('uploads')->put($newFile,file_get_contents($file->getRealPath()));
        }catch (\Exception $exception){
            Log::info('上传文件失败：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'文件上传失败']);
        }
        $xlsFile = public_path('uploads/local').'/'.$newFile;
        try{
            Excel::import(new ProjectImport(), $xlsFile);
            return Response::json(['code'=>0,'msg'=>'导入成功']);
        }catch (\Exception $exception){
            Log::info('导入失败：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'导入失败']);
        }
    }


}
