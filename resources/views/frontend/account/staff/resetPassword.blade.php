@extends('frontend.base')

@section('content')
    <div class="layui-card">
        <div class="layui-card-header  layuiadmin-card-header-auto">
            <h2>重置密码</h2>
        </div>
        <div class="layui-card-body">
            <form class="layui-form" action="{{route('frontend.account.staff.resetPassword',['id'=>$user->id])}}" >
                {{csrf_field()}}
                {{method_field('put')}}
                <div class="layui-form-item">
                    <label for="" class="layui-form-label">新密码</label>
                    <div class="layui-input-inline">
                        <input type="password" name="new_password" maxlength="14" lay-verify="required" placeholder="请输入新密码" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="" class="layui-form-label">确认密码</label>
                    <div class="layui-input-inline">
                        <input type="password" name="new_password_confirmation" maxlength="14" lay-verify="required" placeholder="请确认新密码" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="" class="layui-form-label"></label>
                    <div class="layui-input-block">
                        <button type="button" class="layui-btn layui-btn-sm" lay-submit lay-filter="go">确 认</button>
                        <a  class="layui-btn layui-btn-sm" href="{{route('frontend.account.staff')}}" >返 回</a>
                    </div>
                </div>
        </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        layui.use(['layer','table','form','element','jquery'],function () {
            var $ = layui.jquery;
            var layer = layui.layer;
            var form = layui.form;

        })
    </script>
@endsection
