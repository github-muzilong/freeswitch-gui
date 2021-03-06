@extends('base')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form" action="{{route('system.user.store')}}" method="post">
                {{csrf_field()}}
                <div class="layui-form-item">
                    <label for="" class="layui-form-label">帐号</label>
                    <div class="layui-input-block">
                        <input type="text" maxlength="16" name="name" value="{{ $user->name ?? old('name') }}" lay-verify="required" placeholder="请输入帐号" class="layui-input" >
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="" class="layui-form-label">昵称</label>
                    <div class="layui-input-block">
                        <input type="text" maxlength="16" name="nickname" value="{{ $user->nickname ?? old('nickname') }}" lay-verify="required" placeholder="请输入昵称" class="layui-input" >
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="" class="layui-form-label">手机号码</label>
                    <div class="layui-input-block">
                        <input type="text" name="phone" value="{{$user->phone??old('phone')}}" lay-verify="required|phone"  placeholder="请输入手机号" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="" class="layui-form-label">密码</label>
                    <div class="layui-input-block">
                        <input type="password" maxlength="16" name="password" placeholder="请输入密码" lay-verify="required" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="" class="layui-form-label">角色</label>
                    <div class="layui-input-block">
                        @include('common.get_role_by_user_id')
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="" class="layui-form-label">部门</label>
                    <div class="layui-input-block">
                        @include('common.get_department_by_user_id')
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="" class="layui-form-label">分机</label>
                    <div class="layui-input-block">
                        <select name="sip_id" >
                            <option value="0">无</option>
                            @foreach($sips as $sip)
                                <option value="{{$sip->id}}" @if(in_array($sip->id,$exsits)) disabled @endif @if(isset($user)&&$user->sip_id==$sip->id) selected @endif >{{$sip->username}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="button" class="layui-btn layui-btn-sm" lay-submit="" lay-filter="go-close-refresh">确 认</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection



