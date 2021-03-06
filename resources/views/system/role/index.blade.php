@extends('base')

@section('content')
    <div class="layui-card">
        <div class="layui-card-header layuiadmin-card-header-auto">
            <div class="layui-btn-group">
                @can('system.role.destroy')
                    <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" id="listDelete" data-url="{{ route('system.role.destroy') }}">删除</button>
                @endcan
                @can('system.role.create')
                    <a class="layui-btn layui-btn-sm" id="addBtn">添加</a>
                @endcan
            </div>
        </div>
        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
            <script type="text/html" id="options">
                <div class="layui-btn-group">
                    @can('system.role.edit')
                        <a class="layui-btn layui-btn-sm" lay-event="edit">编辑</a>
                    @endcan
                    @can('system.role.destroy')
                        <a class="layui-btn layui-btn-danger layui-btn-sm" lay-event="del">删除</a>
                    @endcan
                </div>
            </script>
        </div>
    </div>
@endsection

@section('script')
    <script>
        layui.use(['layer', 'table', 'form'], function () {
            var $ = layui.jquery;
            var layer = layui.layer;
            var form = layui.form;
            var table = layui.table;
            //用户表格初始化
            var dataTable = table.render({
                elem: '#dataTable'
                , height: 'full-200'
                , url: "{{ route('system.role') }}" //数据接口
                , page: true //开启分页
                , cols: [[ //表头
                    {checkbox: true, fixed: true}
                    , {field: 'id', title: 'ID', sort: true, width: 80}
                    , {field: 'display_name', title: '显示名称'}
                    , {field: 'name', title: '名称'}
                    , {field: 'created_at', title: '创建时间'}
                    , {field: 'updated_at', title: '更新时间'}
                    , {fixed: 'right', width: 260, align: 'center', toolbar: '#options'}
                ]]
            });

            //监听工具条
            table.on('tool(dataTable)', function (obj) { //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data //获得当前行数据
                    , layEvent = obj.event; //获得 lay-event 对应的值
                if (layEvent === 'del') {
                    deleteData(obj,"{{ route('system.role.destroy') }}");
                } else if (layEvent === 'edit') {
                    layer.open({
                        type: 2,
                        title: "编辑",
                        shadeClose: true,
                        area: ["600px","400px"],
                        content: '/system/role/' + data.id + '/edit',
                    })
                }
            });


            $("#addBtn").click(function () {
                layer.open({
                    type: 2,
                    title: "添加",
                    shadeClose: true,
                    area: ["600px","400px"],
                    content: "{{route("system.role.create")}}",
                })
            })
        })
    </script>
@endsection
