<div class="layui-btn-group">
    <a href="{{route('admin.order')}}" class="layui-btn layui-btn-sm layui-btn-primary">返回列表</a>
    @can('crm.order.show')
    <a href="{{route('admin.order.show',['id'=>$model->id])}}" class="layui-btn layui-btn-sm">项目详情</a>
    @endcan
    @can('crm.order.destroy')
    <a id="destroyBtn" class="layui-btn layui-btn-sm layui-btn-danger">删除</a>
    @endcan
</div>