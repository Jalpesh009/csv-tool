<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-left navbar-brand-wrapper d-flex align-items-center justify-content-between">
        <a class="navbar-brand brand-logo" href="{{ route('admin.dashboard') }}"><img src="{{ asset('public/img/backend/brand/logo.png') }}" alt="logo"/></a>
        <a class="navbar-brand brand-logo-mini" href="{{ route('admin.dashboard') }}"><img src="{{ asset('public/img/backend/brand/logo.png') }}" alt="logo"/></a>
        <button class="navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
     <div class="row pt-3">
      <?php /* ?>
      <h5 class="m-0">
        <?php echo isset($pagetitle) ? $pagetitle : 'Page Title'; ?> </h5>
       <?php /*/ ?>

       {{ Breadcrumbs::render() }}
    </div>
    <ul class="navbar-nav navbar-nav-right">

      <li class="nav-item dropdown nav-user-icon">
          <a class="nav-link dropdown-toggle " id="user_account" href="#" data-toggle="dropdown">
              <img src="<?php echo asset("storage/app/public/$logged_in_user->avatar_location"); ?>" class="img-avatar" alt="{{ $logged_in_user->first_name }}">
          </a>
          <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="user_account">
              <div class="dropdown-header text-center"> <strong>Account</strong> </div>
              <a class="dropdown-item" href="{{ route('admin.auth.user.edit',  $logged_in_user->id) }}">
                  <i class="mdi mdi-pencil-box-outline"></i>Update Profile
              </a>
              <a class="dropdown-item" href="{{ route('frontend.auth.logout') }}">
                  <i class="mdi mdi-logout"></i> @lang('navs.general.logout')
              </a>
          </div>
      </li>
     <!--  <li class="nav-item nav-settings d-none d-lg-flex">
          <a class="nav-link" href="#"> <i class="mdi mdi-dots-horizontal"></i> </a>
      </li> -->
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas"> <span class="mdi mdi-menu"></span></button>
  </div>
</nav>
