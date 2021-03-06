{{csrf_field()}}
<div class="layui-form-item">
    <label for="" class="layui-form-label">网关名称</label>
    <div class="layui-input-inline">
        <input class="layui-input" type="text" name="name" lay-verify="required" value="{{$model->name??old('name')}}" placeholder="如：联通">
    </div>
</div>
<div class="layui-form-item">
    <label for="" class="layui-form-label">网关地址</label>
    <div class="layui-input-inline">
        <input class="layui-input" type="text" name="realm" lay-verify="required" value="{{$model->realm??old('realm')}}" placeholder="格式：192.168.254.100:5066">
    </div>
    <div class="layui-form-mid layui-word-aux">默认5060端口</div>
</div>
<div class="layui-form-item">
    <label for="" class="layui-form-label">帐号</label>
    <div class="layui-input-inline">
        <input class="layui-input" type="text" name="username" lay-verify="required" value="{{$model->username??old('username')}}" placeholder="如：Job">
    </div>
</div>
<div class="layui-form-item">
    <label for="" class="layui-form-label">密码</label>
    <div class="layui-input-inline">
        <input class="layui-input" type="text" name="password" lay-verify="required" value="{{$model->password??old('password')}}" placeholder="如：123456">
    </div>
</div>
<div class="layui-form-item">
    <label for="" class="layui-form-label">前缀</label>
    <div class="layui-input-inline">
        <input class="layui-input" type="text" name="prefix" value="{{$model->prefix??old('prefix')}}" placeholder="非必填">
    </div>
</div>
<div class="layui-form-item">
    <label for="" class="layui-form-label">出局号码</label>
    <div class="layui-input-inline">
        <input class="layui-input" type="text" name="outbound_caller_id" value="{{$model->outbound_caller_id??old('outbound_caller_id')}}" placeholder="非必填">
    </div>
</div>
<div class="layui-form-item">
    <label for="" class="layui-form-label">对接方式</label>
    <div class="layui-input-inline">
        <input type="radio" name="type" value="1" title="SIP" @if(!isset($model->type) || (isset($model->type)&&$model->type==1)) checked @endif>
        <input type="radio" name="type" value="2" title="IP" @if(isset($model->type)&&$model->type==2) checked @endif>
    </div>
    <div class="layui-word-aux layui-form-mid">
        IP对接时，帐号密码可随意填写
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-input-block">
        <button type="button" class="layui-btn layui-btn-sm" lay-submit lay-filter="go-close-refresh" >确 认</button>
    </div>
</div>
