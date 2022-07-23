@extends('base')

@section('content')
    <div class="layui-card">
        <div class="layui-card-header layuiadmin-card-header-auto">
            <form class="layui-form" action="{{route("crm.assignment")}}">
                <div class="layui-btn-group">
                    @can('crm.assignment.destroy')
                        <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" data-url="{{route('crm.assignment.destroy')}}" id="listDelete">删除</button>
                    @endcan
                    @can('crm.assignment.create')
                        <a class="layui-btn layui-btn-sm" id="addBtn">录入</a>
                    @endcan
                    @can('crm.assignment.import')
                        <button type="button" id="import_project" class="layui-btn layui-btn-sm">导入</button>
                        <a href="/template/import.xlsx" class="layui-btn layui-btn-sm layui-btn-warm">模板下载</a>
                    @endcan
                        <button type="button" lay-submit lay-filter="search" class="layui-btn layui-btn-sm" >搜索</button>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">客户名称：</label>
                        <div class="layui-input-block" style="width: 275px">
                            <input type="text" name="name" placeholder="请输入名称" class="layui-input" >
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">联系人：</label>
                        <div class="layui-input-block" style="width: 275px">
                            <input type="text" name="contact_name" placeholder="请输入联系人" class="layui-input" >
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">联系电话：</label>
                        <div class="layui-input-block" style="width: 275px">
                            <input type="text" name="contact_phone" placeholder="请输入联系电话" class="layui-input" >
                        </div>
                    </div>
                </div>
            </form>
            @can('crm.assignment.to')
            <form class="layui-form" action="{{route("crm.assignment.to")}}">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">员工：</label>
                        <div class="layui-input-block" style="width: 275px">
                            @include('common.get_user')
                        </div>
                    </div>
                    <input type="hidden" name="type" value="user">
                    <button type="button" class="layui-btn layui-btn-sm" lay-submit lay-filter="assignment_to" >分配</button>
                </div>
            </form>
            <form class="layui-form" action="{{route("crm.assignment.to")}}">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">经理：</label>
                        <div class="layui-input-block" style="width: 275px">
                            <select name="user_id" >
                                <option value=""></option>
                                @foreach($business as $d)
                                    <option value="{{$d->id}}">{{$d->nickname}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="type" value="business">
                    <button type="button" class="layui-btn layui-btn-sm" lay-submit lay-filter="assignment_to" >分配</button>
                </div>
            </form>
            <form class="layui-form" action="{{route("crm.assignment.to")}}">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">部门：</label>
                        <div class="layui-input-block" style="width: 275px">
                            @include('common.get_department_by_user_id')
                        </div>
                    </div>
                    <input type="hidden" name="type" value="department">
                    <button type="button" class="layui-btn layui-btn-sm" lay-submit lay-filter="assignment_to" >分配</button>
                </div>
            </form>
            @endcan
        </div>
        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
            <script type="text/html" id="options">
                <div class="layui-btn-group">
                    @can('crm.assignment.edit')
                        <a class="layui-btn layui-btn-sm" lay-event="edit">编辑</a>
                    @endcan
                </div>
            </script>
        </div>
    </div>
@endsection

@section('script')
    <script>
        layui.use(['layer','table','form','laydate','upload'],function () {
            var $ = layui.jquery;
            var layer = layui.layer;
            var form = layui.form;
            var table = layui.table;
            var laydate = layui.laydate;
            var upload = layui.upload;
            //用户表格初始化
            var dataTable = table.render({
                elem: '#dataTable'
                ,height: 'full-200'
                ,url: "{{ route('crm.assignment') }}" //数据接口
                ,page: true //开启分页
                ,cols: [[ //表头
                    {checkbox: true}
                    ,{field: 'uuid', title: '客户编号'}
                    ,{field: 'name', title: '客户名称'}
                    ,{field: 'contact_name', title: '联系人'}
                    ,{field: 'contact_phone', title: '联系电话'}
                    ,{field: 'created_at', title: '录入时间'}
                    ,{fixed: 'right', width: 250, align:'center', toolbar: '#options', title:'操作'}
                ]]
            });

            //监听工具条
            table.on('tool(dataTable)', function(obj){ //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data //获得当前行数据
                    ,layEvent = obj.event; //获得 lay-event 对应的值
                if(layEvent === 'edit'){
                    layer.open({
                        type: 2,
                        title: "编辑",
                        shadeClose: true,
                        area: ["90%","90%"],
                        content: '/crm/assignment/'+data.id+'/edit',
                    })
                }
            });
            $("#addBtn").click(function () {
                layer.open({
                    type: 2,
                    title: "添加",
                    shadeClose: true,
                    area: ["90%","90%"],
                    content: "{{route("crm.assignment.create")}}",
                })
            })

            //导入
            $("#import_project").click(function() {
                layer.open({
                    type : 2,
                    title : '导入客户，仅允许xls、xlsx格式',
                    shadeClose : true,
                    area : ['600px','400px'],
                    content : "{{route('crm.assignment.import')}}"
                })
            })

            //分配
            form.on('submit(assignment_to)', function (data) {
                var ids = [];
                var hasCheck = table.checkStatus('dataTable');
                var hasCheckData = hasCheck.data;
                if (hasCheckData.length > 0) {
                    $.each(hasCheckData, function (index, element) {
                        ids.push(element.id)
                    })
                }
                if (ids.length === 0){
                    layer.msg('请选择分配项', {icon: 2});
                    return false
                }
                layer.confirm('确认分配吗？', function (index) {
                    layer.close(index);
                    let load = layer.load();
                    $.post(data.form.action, {ids:ids,user_id:data.field.user_id,type:data.field.type,department_id:data.field.department_id}, function (res) {
                        layer.close(load);
                        let code = res.code
                        layer.msg(res.msg, {time: 2000, icon: code == 0 ? 1 : 2}, function () {
                            if (code === 0) {
                                dataTable.reload()
                            }
                        });
                    });
                })

                return false;
            })


        })
    </script>
@endsection
