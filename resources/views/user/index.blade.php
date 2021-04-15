@extends('base')

@section('content')
    <div class="layui-card">

        <div class="layui-card-header layuiadmin-card-header-auto">
            <div class="layui-btn-group">
                @can('system.user.destroy')
                    <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" id="listDelete">删 除</button>
                @endcan
                @can('system.user.create')
                    <a class="layui-btn layui-btn-sm" id="addBtn">添 加</a>
                @endcan
            </div>
        </div>

        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
            <script type="text/html" id="options">
                <div class="layui-btn-group">
                    @can('system.user.edit')
                        <a class="layui-btn layui-btn-sm" lay-event="edit">编辑</a>
                    @endcan
                    @can('system.user.role')
                        <a class="layui-btn layui-btn-sm" lay-event="role">角色</a>
                    @endcan
                    @can('system.user.destroy')
                        <a class="layui-btn layui-btn-danger layui-btn-sm " lay-event="del">删除</a>
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
                    , url: "{{ route('system.user') }}"
                    , page: true //开启分页
                    , cols: [[ //表头
                        {checkbox: true, fixed: true}
                        , {field: 'id', title: 'ID', sort: true, width: 80}
                        , {field: 'name', title: '帐号'}
                        , {field: 'nickname', title: '昵称'}
                        , {field: 'phone', title: '手机号码'}
                        , {field: 'last_login_at', title: '最近登录时间'}
                        , {field: 'last_login_ip', title: '最近登录IP'}
                        , {field: 'status', title: '状态', templet: function (res) {
                                if (res.status == 1){
                                    return '<input type="checkbox" name="switch" lay-skin="switch" lay-text="启用|禁用" data-userid="'+res.id+'" lay-filter="status-switch" checked />';
                                }else {
                                    return '<input type="checkbox" name="switch" lay-skin="switch" lay-text="启用|禁用" data-userid="'+res.id+'" lay-filter="status-switch" />';
                                }
                            }}
                        , {field: 'created_at', title: '创建时间'}
                        , {field: 'updated_at', title: '更新时间'}
                        , {fixed: 'right', width: 320, align: 'center', toolbar: '#options'}
                    ]]
                });

                //监听工具条
                table.on('tool(dataTable)', function (obj) { //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                    var data = obj.data //获得当前行数据
                        , layEvent = obj.event; //获得 lay-event 对应的值
                    if (layEvent === 'del') {
                        layer.confirm('确认删除吗？', function (index) {
                            layer.close(index);
                            var load = layer.load();
                            $.post("{{ route('system.user.destroy') }}", {
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
                        layer.open({
                            type: 2,
                            title: "编辑",
                            shadeClose: true,
                            area: ["600px","400px"],
                            content: "/system/user/" + data.id + "/edit",
                        })
                    }
                });

                //按钮批量删除
                $("#listDelete").click(function () {
                    var ids = [];
                    var hasCheck = table.checkStatus('dataTable');
                    var hasCheckData = hasCheck.data;
                    if (hasCheckData.length > 0) {
                        $.each(hasCheckData, function (index, element) {
                            ids.push(element.id)
                        })
                    }
                    if (ids.length > 0) {
                        layer.confirm('确认删除吗？', function (index) {
                            layer.close(index);
                            var load = layer.load();
                            $.post("{{ route('system.user.destroy') }}", {
                                _method: 'delete',
                                ids: ids
                            }, function (res) {
                                layer.close(load);
                                if (res.code == 0) {
                                    layer.msg(res.msg, {icon: 1}, function () {
                                        dataTable.reload({page: {curr: 1}});
                                    })
                                } else {
                                    layer.msg(res.msg, {icon: 2})
                                }
                            });
                        })
                    } else {
                        layer.msg('请选择删除项', {icon: 2})
                    }
                })


                $("#addBtn").click(function () {
                    layer.open({
                        type: 2,
                        title: "添加",
                        shadeClose: true,
                        area: ["600px","400px"],
                        content: "{{route("system.user.create")}}",
                    })
                })

                form.on('switch(status-switch)', function(data){
                    var status = data.elem.checked ? 1 : 2;
                    var load = layer.load()
                    $.post("{{route("system.user.status")}}",{status:status,user_id:$(data.elem).data("userid")},function (res) {
                        layer.close(load);
                        layer.msg(res.msg, {icon: res.code == 0 ? 1 : 2})
                    })
                });

            })
        </script>

@endsection



