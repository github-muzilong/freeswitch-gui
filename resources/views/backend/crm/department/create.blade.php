@extends('backend.base')

@section('content')
    <div class="layui-card">
        <div class="layui-card-header layuiadmin-card-header-auto">
            <h2>添加部门</h2>
        </div>
        <div class="layui-card-body">
            <form action="{{route('backend.crm.department.store')}}" method="post" class="layui-form">
                @include('backend.crm.department._form')
            </form>
        </div>
    </div>
@endsection

@section('script')
    @include('backend.crm.department._js')
@endsection