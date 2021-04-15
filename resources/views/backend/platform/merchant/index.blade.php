@extends('backend.base')

@section('content')
    <div class="layui-card">
        <div class="layui-card-header layuiadmin-card-header-auto">
            <form action="{{route('backend.platform.merchant')}}" class="layui-form">
                <div class="layui-btn-group">
                    @can('backend.platform.merchant.destroy')
                        <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" id="listDelete">删 除</button>
                    @endcan
                    @can('backend.platform.merchant.create')
                        <a class="layui-btn layui-btn-sm" href="{{ route('backend.platform.merchant.create') }}">添 加</a>
                    @endcan
                        <button type="button" class="layui-btn layui-btn-sm" lay-submit lay-filter="search" >搜 索</button>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">服务器</label>
                        <div class="layui-input-block">
                            <select name="freeswitch_id" >
                                <option value="0">无</option>
                                @foreach($fs as $d)
                                    <option value="{{$d->id}}">{{$d->name}}({{$d->external_ip}})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">公司名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="company_name" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">联系人</label>
                        <div class="layui-input-block">
                            <input type="text" name="contact_name" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label for="" class="layui-form-label">联系电话</label>
                        <div class="layui-input-block">
                            <input type="text" name="contact_phone" class="layui-input">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
            <script type="text/html" id="options">
                <div class="layui-btn-group">
                    @can('backend.platform.merchant.bill')
                        <a class="layui-btn layui-btn-sm" lay-event="bill">帐单</a>
                    @endcan
                    @can('backend.platform.merchant.edit')
                        <a class="layui-btn layui-btn-sm" lay-event="edit">编辑</a>
                    @endcan
                    @can('backend.platform.merchant.destroy')
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
                    , height: 500
                    , url: "{{ route('backend.platform.merchant') }}" //数据接口
                    , page: true //开启分页
                    , cols: [[ //表头
                        {checkbox: true, fixed: true}
                        , {field: 'id', title: 'ID', sort: true, width: 80}
                        , {field: 'company_name', title: '公司名称'}
                        , {field: 'contact_name', title: '联系人'}
                        , {field: 'contact_phone', title: '联系电话'}
                        , {field: 'staff_num', title: '员工数量（总）'}
                        , {field: 'sip_num', title: '分机数量'}
                        , {field: 'gateway_num', title: '网关数量'}
                        , {field: 'agent_num', title: '坐席数量'}
                        , {field: 'queue_num', title: '队列数量'}
                        , {field: 'task_num', title: '任务数量'}
                        , {field: 'expire_at', title: '到期时间',width:150}
                        , {field: 'created_at', title: '创建时间'}
                        , {width: 180, align: 'center', toolbar: '#options',title: '操作'}
                    ]]
                });

                //监听工具条
                table.on('tool(dataTable)', function (obj) { //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                    var data = obj.data //获得当前行数据
                        , layEvent = obj.event; //获得 lay-event 对应的值
                    if (layEvent === 'del') {
                        layer.confirm('确认删除吗？', function (index) {
                            layer.close(index)
                            var load = layer.load();
                            $.post("{{ route('backend.platform.merchant.destroy') }}", {
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
                        location.href = '/backend/platform/merchant/' + data.id + '/edit';
                    } else if (layEvent === 'bill'){
                        layer.open({
                            type : 2,
                            title : '帐单',
                            shadeClose : true,
                            area : ['80%','80%'],
                            content : '/backend/platform/merchant/' + data.id + '/bill'
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
                            $.post("{{ route('backend.platform.merchant.destroy') }}", {
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
                        layer.msg('请选择删除项', {icon: 2});
                    }
                })

                //搜索
                form.on('submit(search)',function (data) {
                    dataTable.reload({
                        where:data.field,
                        page:{curr:1}
                    })
                    return false;
                })
            })
        </script>

@endsection
