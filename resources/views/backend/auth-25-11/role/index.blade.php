@extends('backend.layouts.app')

@section('title', app_name() . ' | '. __('labels.backend.access.roles.management'))

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    @lang('labels.backend.access.roles.management')
                </h4>
            </div><!--col-->

            <div class="col-sm-7 pull-right">
                @include('backend.auth.role.includes.header-buttons')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>@lang('labels.backend.access.roles.table.role')</th>
                            <th>@lang('labels.backend.access.roles.table.permissions')</th>
                            <th>@lang('labels.backend.access.roles.table.number_of_users')</th>
                            <th>@lang('labels.general.actions')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td>{{ ucwords($role->name) }}</td>
                                <td>
                                    @if($role->id == 1)
                                        @lang('labels.general.all')
                                    @else
                                        @if($role->permissions->count())
                                            <ul class="role-td-ul">
                                            @foreach($role->permissions as $permission)
                                                <li> {{ ucwords($permission->name) }}</li>
                                            @endforeach
                                            </ul>
                                        @else
                                            @lang('labels.general.none')
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $role->users->count() }}</td>
                                <td>{!! $role->action_buttons !!}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div><!--col-->
        </div><!--row-->
        <div class="row">
            <div class="col-7">
                <div class="float-left">
                    {!! $roles->total() !!} {{ trans_choice('labels.backend.access.roles.table.total', $roles->total()) }}
                </div>
            </div><!--col-->

            <div class="col-5">
                <div class="float-right">
                    {!! $roles->render() !!}
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
<style>
.role-td-ul{
    columns: 4;
  -webkit-columns: 4;
  -moz-columns: 4;
}
</style>
<div class="modal fade" id="delete_role_modal" role="dialog">
        <div class="modal-dialog modal-md w-50">
          <div class="modal-content">
            <div class="modal-header py-3">
                <h4 class="modal-title">Confirm Delete Role</h4>
                <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <span>Are you sure want to delete?</span>
            </div>
            <div class="modal-footer py-3">
                    <a href=""
                    data-method="delete" data-trans-button-cancel="Cancel" data-trans-button-confirm="Delete" data-trans-title="Are you sure you want to do this?"
                    class="cbtn cdanger btn-icon p-1 deleterole"><i class="text-white mdi mdi-delete" data-toggle="tooltip" data-placement="top" title="Delete"></i></a>
                {{-- <button class="" data-dismiss="modal" type="submit"><i class="mdi mdi-delete"></i></button> --}}
                <button type="button" class="cbtn cprimary btn-icon" data-dismiss="modal"><i class="mdi mdi-close"></i></button>
            </div>
          </div>
        </div>
    </div>
@endsection
