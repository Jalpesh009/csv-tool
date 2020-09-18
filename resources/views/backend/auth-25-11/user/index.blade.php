@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('labels.backend.access.users.management'))

@section('breadcrumb-links')
    @include('backend.auth.user.includes.breadcrumb-links')
@endsection

@section('content')
<?php
$firstname = '';
$lastname = '';
$emailaddress = '';
$role = '';
if($requestdata){
    if(isset($requestdata['first_name'])){
        $firstname = $requestdata['first_name'];
    }
    if(isset($requestdata['last_name'])){
        $lastname = $requestdata['last_name'];
    }
    if(isset($requestdata['email'])){
        $emailaddress = $requestdata['email'];
    }
    // if($requestdata['role']){
    //     $role = $requestdata['role'];
    // }
}
?>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    {{ __('labels.backend.access.users.management') }} <small class="text-muted">{{ __('labels.backend.access.users.active') }}</small>
                </h4>
            </div><!--col-->

            <div class="col-sm-7">
                @include('backend.auth.user.includes.header-buttons')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <form name="searchForm" id="searchForm" method="GET">
                    <table class="table">
                        <thead>
                        <tr>

                            <th>
                                <div class="form-group">
                                <input class="form-control" placeholder="Search First Name" type="text" name="first_name" value="{{$firstname}}"/></div><br/>
                                @lang('labels.backend.access.users.table.first_name')</th>
                            <th>
                                <div class="form-group"><input placeholder="Search Last Name" class="form-control" type="text" name="last_name" value="{{$lastname}}"/></div><br/>
                                @lang('labels.backend.access.users.table.last_name')</th>
                            <th>
                            <div class="form-group"><input placeholder="Search Email Address" class="form-control" type="text" name="email" value="{{$emailaddress}}"/></div><br/>
                                @lang('labels.backend.access.users.table.email')</th>
                            {{-- <th>@lang('labels.backend.access.users.table.confirmed')</th> --}}
                            <th>
                                    <div class="form-group">
                                            <button style="width:50px;height:50px;" type="submit" class="cbtn cprimary btn-icon text-white" value="yes" name="search"><i class="mdi mdi-magnify"></i></button>
                                    </div><br/>
                            {{-- <div class="form-group"><input placeholder="Search Role" class="form-control" type="text" name="role" value="{{$role}}"/></div><br/> --}}
                                @lang('labels.backend.access.users.table.roles')</th>
                            {{-- <th>@lang('labels.backend.access.users.table.other_permissions')</th> --}}
                            {{-- <th>@lang('labels.backend.access.users.table.social')</th> --}}
                            {{-- <th>@lang('labels.backend.access.users.table.last_updated')</th> --}}
                            <th>

                                @lang('labels.general.actions')</th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php if($users->count() !=0){?>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->first_name }}</td>
                                <td>{{ $user->last_name }}</td>
                                <td>{{ $user->email }}</td>
                                {{-- <td>{!! $user->confirmed_label !!}</td> --}}
                                <td>{!! $user->roles_label !!}</td>
                                {{-- <td>{!! $user->permissions_label !!}</td> --}}
                                {{-- <td>{!! $user->social_buttons !!}</td> --}}
                                {{-- <td>{{ $user->updated_at->diffForHumans() }}</td> --}}
                                <td>{!! $user->action_buttons !!}</td>
                            </tr>
                        @endforeach
                        <?php }else{?>
                            <tr><td class="text-center" colspan="4">No any data found for your search</td></tr>
                        <?php }?>
                        </tbody>
                    </table>
                    </form>
                </div>
            </div><!--col-->
        </div><!--row-->
        <div class="row">
            {{-- <div class="col-7">
                <div class="float-left">
                    {!! $users->total() !!} {{ trans_choice('labels.backend.access.users.table.total', $users->total()) }}
                </div>
            </div><!--col--> --}}

            <div class="col-12">
                <div class="float-right">
                    {!! $users->render() !!}
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
<style>

</style>
@endsection
