{{csrf_field()}}
<div class="layui-row layui-col-space30">
    <div class="layui-col-lg4">
        <div class="layui-form-item">
            <label for="" class="layui-form-label">帐号</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="text" name="username" lay-verify="required" value="{{$model->username??old('username')}}" placeholder="商家帐号">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">密码</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="password" name="password" @if(!isset($model)) lay-verify="required" @endif value="" placeholder="商家密码">
            </div>
            <div class="layui-word-aux layui-form-mid">不修改则留空</div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">联系人</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="text" name="contact_name" lay-verify="required" value="{{$model->contact_name??old('contact_name')}}" placeholder="联系人">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">联系电话</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="text" name="contact_phone" lay-verify="required" value="{{$model->contact_phone??old('contact_phone')}}" placeholder="联系电话">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">状态</label>
            <div class="layui-input-inline">
                <select name="status" lay-verify="required">
                    <option value="">请选择</option>
                    @foreach(config('freeswitch.merchant_status') as $k=>$v)
                        <option value="{{$k}}" @if(isset($model)&&$model->status==$k) selected @endif >{{$v}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="layui-col-lg4">
        <div class="layui-form-item">
            <label for="" class="layui-form-label">公司名称</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="text" name="company_name" lay-verify="required" value="{{$model->info->company_name??old('company_name')}}" placeholder="公司名称">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">分机数量</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="number" name="sip_num" lay-verify="required|number" value="{{$model->info->sip_num??old('sip_num')}}" placeholder="分机数量">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">员工数量</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="number" name="member_num" lay-verify="required|number" value="{{$model->info->member_num??old('member_num')}}" placeholder="子帐号数量">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">队列数量</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="number" name="queue_num" lay-verify="required|number" value="{{$model->info->queue_num??old('queue_num')}}" placeholder="队列数量">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">到期时间</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="text" name="expires_at" id="expires_at" lay-verify="required" value="{{$model->info->expires_at??old('expires_at')}}" placeholder="点击选择" readonly>
            </div>
        </div>
    </div>
</div>


<div class="layui-form-item">
    <label for="" class="layui-form-label"></label>
    <div class="layui-input-inline">
        <button type="submit" class="layui-btn" lay-submit lay-filter="*" >确 认</button>
        <a href="{{route('admin.merchant')}}" class="layui-btn" >返 回</a>
    </div>
</div>