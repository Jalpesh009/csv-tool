<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
      <li class="nav-item nav-profile">
        <div class="nav-link d-flex">
          <div class="profile-image">
            <img src="{{ $logged_in_user->picture }}" class="img-avatar" alt="{{ $logged_in_user->email }}">
          </div>
          <div class="profile-name">
            <p class="name">
              <?php $user = Auth::user(); ?> 
              {{ $logged_in_user->full_name }}
            </p>
            <p class="designation">
                {{ ucfirst(trans( $user->roles->toArray()[0]['name'] )) }}
            </p>
          </div>
        </div>
    </li>
    <!-- <li class="nav-item"> 
       @lang('menus.backend.sidebar.general') 
    </li> -->
    <li class="nav-item {{ request()->is('admin.dashboard') ? 'active' : '' }}">
      <a class="nav-link " href="{{ route('admin.dashboard') }}">
      <i class="mdi mdi-puzzle menu-icon"></i>
      <span class="menu-title"> @lang('menus.backend.sidebar.dashboard') </span>
      </a>
    </li>
    <li class="nav-item {{ request()->is('admin/masterfields*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ url('admin/masterfields') }}"  >
      <i class="mdi mdi-format-align-justify menu-icon"></i>
      <span class="menu-title">Master Matrix</span> 
      </a> 
    </li>
    <li class="nav-item {{ request()->is('admin/store*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ url('admin/store') }}"  >
      <i class="mdi mdi-store menu-icon"></i>
      <span class="menu-title">Manage Retailer Matrix</span> 
      </a> 
    </li>
    <li class="nav-item {{ request()->is('admin/game*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ url('admin/game') }}"  >
      <i class="mdi mdi-gamepad-variant menu-icon"></i>
      <span class="menu-title">Manage Game</span> 
      </a> 
    </li>
    <!-- <li class="nav-item {{ request()->is('admin/storemonitor*') ? 'active' : '' }} ">
      <a class="nav-link" href="{{ url('admin/storemonitor') }}"  >
      <i class="mdi mdi-gamepad-variant menu-icon"></i>
      <span class="menu-title">Store Monitoring</span> 
      </a> 
    </li> -->
    <!-- <li class="nav-title">
        @lang('menus.backend.sidebar.system')
    </li> -->
    @if ($logged_in_user->isAdmin())
    <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#ui-advanced" aria-expanded="false" aria-controls="ui-advanced">
        <i class="mdi mdi-palette menu-icon"></i>
        <span class="menu-title">@lang('menus.backend.access.title')</span>
        <i class="menu-arrow"></i>
        @if ($pending_approval > 0)
            <span class="badge badge-danger">{{ $pending_approval }}</span>
        @endif
      </a>
      <div class="collapse" id="ui-advanced">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item"> 
                <a class="nav-link" href="{{ route('admin.auth.user.index') }}"> 
                    @lang('labels.backend.access.users.management')
                    @if ($pending_approval > 0)
                        <span class="badge badge-danger">{{ $pending_approval }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item"> 
                <a class="nav-link" href="{{ route('admin.auth.role.index') }}"> @lang('labels.backend.access.roles.management')</a>
            </li> 
        </ul>
      </div>
    </li>
    <li class="nav-item d-none">
      <a class="nav-link" data-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">
      <i class="mdi mdi-view-headline menu-icon"></i>
      <span class="menu-title">@lang('menus.backend.log-viewer.main') </span>
      <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="form-elements">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"><a class="nav-link" href="{{ route('log-viewer::dashboard') }}">@lang('menus.backend.log-viewer.dashboard')</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('log-viewer::logs.list') }}">@lang('menus.backend.log-viewer.logs')</a></li>
           
        </ul>
      </div>
    </li> 
    @endif 
  </ul>
</nav> 