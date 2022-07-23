@extends('base')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="layui-row layui-col-space30">
                <div class="layui-col-xs6">
                    <div class="layui-card">
                        <div class="layui-card-header"><b>跟进记录</b></div>
                        <div class="layui-card-body">
                            <ul class="layui-timeline" id="remark_list_box"></ul>
                        </div>
                    </div>
                </div>
                <div class="layui-col-xs6">
                    <div class="layui-card">
                        <div class="layui-card-header"><b>备注跟进</b></div>
                        <div class="layui-card-body">
                            <form class="layui-form" action="{{route('order.order.remark',['id'=>$model->id])}}" method="post">
                                {{csrf_field()}}
                                <div class="layui-form-item">
                                    <label for="" class="layui-form-label">节点</label>
                                    <div class="layui-input-block">
                                        @include('common.get_node',['node_id'=>$model->node_id,'type'=>3])
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label for="" class="layui-form-label">备注内容</label>
                                    <div class="layui-input-block">
                                        <textarea name="content" class="layui-textarea" lay-verify="required"></textarea>
                                    </div>
                                    <div class="layui-word-aux layui-form-mid"></div>
                                </div>
                                <div class="layui-form-item">
                                    <label for="" class="layui-form-label">下次跟进</label>
                                    <div class="layui-input-block">
                                        <input type="text" id="next_follow_time" name="next_follow_time" placeholder="请选择时间" lay-verify="required" readonly class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-input-block">
                                        <button type="button" lay-submit lay-filter="go-close-refresh" class="layui-btn layui-btn-sm">确认</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        layui.use(['layer','table','form','element','flow','laydate'],function () {
            var $ = layui.jquery;
            var form = layui.form;
            var flow = layui.flow;
            var laydate = layui.laydate;
            laydate.render({
                elem: '#next_follow_time',
                type: 'datetime'
            });
            flow.load({
                elem: '#remark_list_box' //流加载容器
                ,done: function(page, next){ //执行下一页的回调
                    $.post('{{route('api.remarkList')}}',{id:'{{$model->id}}',page:page,'type':3},function (res) {
                        var _html = '';
                        res.data.list.forEach(function (item,index) {
                            console.log(item)
                            _html += '<li class="layui-timeline-item">';
                            _html += '  <i class="layui-icon layui-timeline-axis">&#xe63f;</i>';
                            _html += '  <div class="layui-timeline-content layui-text">';
                            _html += '      <h3 class="layui-timeline-title">'+item.created_at+'</h3>';
                            _html += '      <p><b>节点进度：</b>' + item.old_node_name + ' -> '+item.new_node_name+'</p>';
                            _html += '      <p><b>跟进人：</b>' + item.user_nickname + '</p>';
                            _html += '      <p><b>跟进内容：</b>' + item.content + '</p>';
                            _html += '  </div>';
                            _html += '</li>';
                        })
                        next(_html, page < res.data.lastPage); //假设总页数为 10
                    });

                }
            });
        });
    </script>
@endsection

