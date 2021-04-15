@extends('admin.base')

@section('content')
    <div class="layui-card">
        <div class="layui-card-header layuiadmin-card-header-auto">
            <div class="layui-btn-group ">
                @can('system.permission.create')
                    <a class="layui-btn layui-btn-sm layui-bg-cyan" href="{{ route('admin.permission.create',['guard_name'=>'web']) }}">添加后台权限</a>
                    <a class="layui-btn layui-btn-sm layui-bg-green" href="{{ route('admin.permission.create',['guard_name'=>'merchant']) }}">添加前台权限</a>
                @endcan
            </div>
        </div>
        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
            <script type="text/html" id="options">
                <div class="layui-btn-group">
                    @can('system.permission.edit')
                        <a class="layui-btn layui-btn-sm" lay-event="edit">编辑</a>
                    @endcan
                    @can('system.permission.destroy')
                        <a class="layui-btn layui-btn-danger layui-btn-sm" lay-event="del">删除</a>
                    @endcan
                </div>
            </script>
        </div>
    </div>
@endsection

@section('script')
    @can('system.permission')
        <script>
            layui.config({
                base: '/static/admin/layuiadmin/modules/'
            }).extend({
                treetable: 'treetable-lay/treetable'
            }).use(['layer', 'table', 'form', 'treetable'], function () {
                var $ = layui.jquery;
                var layer = layui.layer;
                var form = layui.form;
                var table = layui.table;
                var treetable = layui.treetable;
                //用户表格初始化

                // 渲染表格
                var dataTable = function () {
                    treetable.render({
                        treeColIndex: 1,          // treetable新增参数
                        treeSpid: 0,             // treetable新增参数
                        treeIdName: 'id',       // treetable新增参数
                        treePidName: 'parent_id',     // treetable新增参数
                        treeDefaultClose: true,   // treetable新增参数
                        treeLinkage: false,        // treetable新增参数
                        elem: '#dataTable',
                        url: "{{ route('admin.permission.data') }}",
                        cols: [[ //表头
                            {field: 'id', title: 'ID', sort: true, width: 80}
                            , {field: 'display_name', title: '显示名称',width:200}
                            , {field: 'name', title: '权限名称'}
                            , {field: 'route', title: '路由'}
                            , {field: 'url', title: '链接（路由优先）'}
                            , {
                                field: 'icon_id', title: '图标', templet: function (d) {
                                    return '<i class="layui-icon ' + d.icon + '"></i>';
                                }
                            }
                            , {field: 'type_name', title: '类型'}
                            , {field: 'visiable_name', title: '可见性'}
                            , {field: 'guard_name', title: '权限所属',templet:function (d) {
                                    if (d.guard_name=='web'){
                                        return '<span class="layui-badge layui-bg-cyan">后台</span>'
                                    } else if (d.guard_name=='merchant'){
                                        return '<span class="layui-badge layui-bg-green">前台</span>'
                                    } else {
                                        return '未知';
                                    }
                                }}
                            , {field: 'created_at', title: '创建时间'}
                            , {field: 'updated_at', title: '更新时间'}
                            , {fixed: 'right', width: 260, align: 'center', toolbar: '#options'}
                        ]]
                    });
                }
                dataTable(); //调用此函数可重新渲染表格

                //监听工具条
                table.on('tool(dataTable)', function (obj) { //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                    var data = obj.data //获得当前行数据
                        , layEvent = obj.event; //获得 lay-event 对应的值
                    if (layEvent === 'del') {
                        layer.confirm('确认删除吗？', function (index) {
                            layer.close(index)
                            var load = layer.load();
                            $.post("{{ route('admin.permission.destroy') }}", {
                                _method: 'delete',
                                ids: [data.id]
                            }, function (res) {
                                layer.close(load);
                                if (res.code == 0) {
                                    layer.msg(res.msg, {icon: 1}, function () {
                                        obj.del();
                                    })
                                } else {
                                    layer.msg(res.msg, {icon: 2})
                                }
                            });
                        });
                    } else if (layEvent === 'edit') {
                        location.href = '/admin/permission/' + data.id + '/edit';
                    }
                });
            })
        </script>
    @endcan
@endsection