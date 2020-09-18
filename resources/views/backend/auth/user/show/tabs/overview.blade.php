<div class="col">
    <div class="table-responsive">
            <button class="cbtn cdanger btn-icon pull-right" onclick="history.go(-1);" name="backbtn" id=""><i class="mdi mdi-close"></i></button>
        <table class="table table-hover"> 
            <tr>
                <th>@lang('labels.backend.access.users.tabs.content.overview.avatar')</th>
                <td> 
                    <img src="<?php echo asset("storage/app/public/$user->avatar_location"); ?>"  class="user-profile-image" alt="{{ $user->first_name }}"/></img>
                </td>
            </tr>

            <tr>
                <th>@lang('labels.backend.access.users.tabs.content.overview.name')</th>
                <td>{{ $user->name }}</td>
            </tr>

            <tr>
                <th>@lang('labels.backend.access.users.tabs.content.overview.email')</th>
                <td>{{ $user->email }}</td>
            </tr>

            <tr>
                <th>@lang('labels.backend.access.users.tabs.content.overview.status')</th>
                <td>{!! $user->status_label !!}</td>
            </tr>

            <tr>
                <th>@lang('labels.backend.access.users.tabs.content.overview.confirmed')</th>
                <td>{!! $user->confirmed_label !!}</td>
            </tr>

            {{-- <tr>
                <th>@lang('labels.backend.access.users.tabs.content.overview.timezone')</th>
                <td>{{ $user->timezone }}</td>
            </tr> --}}

            <tr>
                <th>@lang('labels.backend.access.users.tabs.content.overview.last_login_at')</th>
                <td>
                    @if($user->last_login_at)
                        {{ timezone()->convertToLocal($user->last_login_at) }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>

            <tr>
                <th>@lang('labels.backend.access.users.tabs.content.overview.last_login_ip')</th>
                <td>{{ $user->last_login_ip ?? 'N/A' }}</td>
            </tr>

        </table>
    </div>
</div><!--table-responsive-->
