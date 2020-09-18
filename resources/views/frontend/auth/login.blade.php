@extends('frontend.layouts.app')

@section('title', app_name() . ' | ' . __('labels.frontend.auth.login_box_title'))

@section('content') 
  <div class="container-scroller"> 
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
        <div class="row flex-grow">
          <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <div class="auth-form-transparent text-left p-3">
              <div class="brand-logo">
                <a href="{{ url('/') }}"><img src="{{ asset('public/img/backend/brand/logo.png') }}" alt="logo"></a>
              </div>
              <h4>Welcome back!</h4>
              <h6 class="font-weight-light">Happy to see you again!</h6>
              @include('includes.partials.messages')
              {{ html()->form('POST', route('frontend.auth.login.post'))->open() }}
                <div class="form-group">
                  {{ html()->label(__('validation.attributes.frontend.email'))->for('email') }}
                  <div class="input-group"> 
                    <div class="input-group-prepend bg-transparent">
                      <span class="input-group-text bg-transparent border-right-0">
                        <i class="mdi mdi-email-outline text-primary"></i>
                      </span>
                    </div>
                    {{ html()->email('email')
                        ->class('form-control form-control-lg border-left-0')
                        ->placeholder(__('validation.attributes.frontend.email'))
                        ->attribute('maxlength', 191)
                        ->required() }}

                  </div>
                </div>
                <div class="form-group">
                  {{ html()->label(__('validation.attributes.frontend.password'))->for('password') }}
                  <div class="input-group">
                    <div class="input-group-prepend bg-transparent">
                      <span class="input-group-text bg-transparent border-right-0">
                        <i class="mdi mdi-lock-outline text-primary"></i>
                      </span>
                    </div>  
                    {{ html()->password('password')
                        ->class('form-control form-control-lg border-left-0')
                        ->placeholder(__('validation.attributes.frontend.password'))
                        ->required() }}                     
                  </div>
                </div>
                <div class="my-2 d-flex justify-content-between align-items-center">
                  <div class="form-check">
                    <label class="form-check-label text-muted">
                     
                      {{ html()->label(html()->checkbox('remember', true, 1) . ' ' . __('labels.frontend.auth.remember_me'))->for('remember') }}
                    </label>
                  </div>
                   
                    <a href="{{ route('frontend.auth.password.reset') }}" class="auth-link text-black">@lang('labels.frontend.passwords.forgot_password')</a>
                </div>
                <div class="my-3">
                  <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn" >LOGIN</button> 
                </div>
                {{ html()->form()->close() }}
            </div>
          </div>
          <div class="col-lg-6 login-half-bg d-flex flex-row">
            <p class="text-white font-weight-medium text-center flex-grow align-self-end">Copyright &copy; 2018  All rights reserved.</p>
          </div>
        </div>
      </div>
      <!-- content-wrapper ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
@endsection