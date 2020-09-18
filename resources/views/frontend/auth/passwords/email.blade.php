@extends('frontend.layouts.app')

@section('title', app_name() . ' | ' . __('labels.frontend.passwords.reset_password_box_title'))

@section('content')  
    <div class="container-scroller"> 
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
                <div class="row flex-grow">
                    <div class="col-lg-6 d-flex align-items-center justify-content-center">
                        <div class="auth-form-transparent text-left p-3">
                            <div class="brand-logo">
                                <img src="{{ asset('public/img/backend/brand/logo.png') }}" alt="logo">
                            </div>
                            <h4>@lang('labels.frontend.passwords.reset_password_box_title')</h4>
                            <h6 class="font-weight-light">Happy to see you again!</h6>
                            @if(session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif
                            {{ html()->form('POST', route('frontend.auth.password.email.post'))->open() }}
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
                                        ->required()
                                        ->autofocus() }}
                                    </div>  
                                </div>  
                                <div class="my-3">
                                  <!--   <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn" >LOGIN</button>  -->
                                    {{ form_submit(__('labels.frontend.passwords.send_password_reset_link_button'), 'btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn') }}
                                   

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
