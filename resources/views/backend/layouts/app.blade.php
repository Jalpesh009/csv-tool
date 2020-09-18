<!DOCTYPE html>
@langrtl
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
@else
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endlangrtl
<head>
    <meta  http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', app_name())</title>
    <link rel="shortcut icon" href="{{ asset('public/img/backend/brand/favicon.ico') }}"/>
    
    <meta name="description" content="@yield('description')">
    <meta name="keywords" content="@yield('keywords')">

    <meta name="author" content="@yield('meta_author', 'Anthony Rappa')">   
    @yield('meta') 
    <link rel="stylesheet" href="{{ asset('public/assets/vendors/mdi/css/materialdesignicons.min.css') }}"> 
    <link rel="stylesheet" href="{{ asset('public/assets/vendors/font-awesome/css/font-awesome.min.css') }}"> 
    <link rel="stylesheet" href="{{ asset('public/assets/vendors/base/vendor.bundle.base.css') }}"> 

    @stack('before-styles') 
        <!-- {{ style(mix('css/backend.css')) }}   -->
    @stack('after-styles')
    <link rel="stylesheet" href="{{ asset('public/assets/css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/backend-custom.css') }}"> 
</head>

<body class="{{ config('backend.body_classes') }}">
    @include('backend.includes.header')

    <div class="container-fluid page-body-wrapper">
      <!-- partial:../../partials/_settings-panel.html -->
         
        <div id="right-sidebar" class="settings-panel">
          <i class="settings-close mdi mdi-close"></i>
          <ul class="nav nav-tabs" id="setting-panel" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="todo-tab" data-toggle="tab" href="#todo-section" role="tab" aria-controls="todo-section" aria-expanded="true">TO DO LIST</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="chats-tab" data-toggle="tab" href="#chats-section" role="tab" aria-controls="chats-section">CHATS</a>
            </li>
          </ul>
          <div class="tab-content" id="setting-content">
            <div class="tab-pane fade show active scroll-wrapper" id="todo-section" role="tabpanel" aria-labelledby="todo-section">
              <div class="add-items d-flex px-3 mb-0">
                <form class="form w-100">
                  <div class="form-group d-flex">
                    <input type="text" class="form-control todo-list-input" placeholder="Add To-do">
                    <button type="submit" class="add btn btn-primary todo-list-add-btn" id="add-task">Add</button>
                  </div>
                </form>
              </div>
              <div class="list-wrapper px-3">
                <ul class="d-flex flex-column-reverse todo-list">
                  <li>
                    <div class="form-check">
                      <label class="form-check-label">
                      <input class="checkbox" type="checkbox">
                      Team review meeting at 3.00 PM
                      </label>
                    </div>
                    <i class="remove mdi mdi-delete"></i>
                  </li>
                  <li>
                    <div class="form-check">
                      <label class="form-check-label">
                      <input class="checkbox" type="checkbox">
                      Prepare for presentation
                      </label>
                    </div>
                    <i class="remove mdi mdi-delete"></i>
                  </li>
                  <li>
                    <div class="form-check">
                      <label class="form-check-label">
                      <input class="checkbox" type="checkbox">
                      Resolve all the low priority tickets due today
                      </label>
                    </div>
                    <i class="remove mdi mdi-delete"></i>
                  </li>
                  <li class="completed">
                    <div class="form-check">
                      <label class="form-check-label">
                      <input class="checkbox" type="checkbox" checked>
                      Schedule meeting for next week
                      </label>
                    </div>
                    <i class="remove mdi mdi-delete"></i>
                  </li>
                  <li class="completed">
                    <div class="form-check">
                      <label class="form-check-label">
                      <input class="checkbox" type="checkbox" checked>
                      Project review
                      </label>
                    </div>
                    <i class="remove mdi mdi-delete"></i>
                  </li>
                </ul>
              </div>
              <div class="events py-4 border-bottom px-3">
                <div class="wrapper d-flex mb-2">
                  <i class="mdi mdi-circle-outline text-primary mr-2"></i>
                  <span>Feb 11 2018</span>
                </div>
                <p class="mb-0 font-weight-thin text-gray">Creating component page</p>
                <p class="text-gray mb-0">build a js based app</p>
              </div>

              <div class="events pt-4 px-3">
                <div class="wrapper d-flex mb-2">
                  <i class="mdi mdi-circle-outline text-primary mr-2"></i>
                  <span>Feb 7 2018</span>
                </div>
                <p class="mb-0 font-weight-thin text-gray">Meeting with Alisa</p>
                <p class="text-gray mb-0 ">Call Sarah Graves</p>
              </div>
            </div>
            <!-- To do section tab ends -->
            <div class="tab-pane fade" id="chats-section" role="tabpanel" aria-labelledby="chats-section">
              <div class="d-flex align-items-center justify-content-between border-bottom">
                <p class="settings-heading border-top-0 mb-3 pl-3 pt-0 border-bottom-0 pb-0">Friends</p>
                <small class="settings-heading border-top-0 mb-3 pt-0 border-bottom-0 pb-0 pr-3 font-weight-normal">See All</small>
              </div>
              <ul class="chat-list">
                <li class="list active">
                  <div class="profile"><img src="https://via.placeholder.com/40x40" alt="image"><span class="online"></span></div>
                  <div class="info">
                    <p>Thomas Douglas</p>
                    <p>Available</p>
                  </div>
                  <small class="text-muted my-auto">19 min</small>
                </li>
                <li class="list">
                  <div class="profile"><img src="https://via.placeholder.com/40x40" alt="image"><span class="offline"></span></div>
                  <div class="info">
                    <div class="wrapper d-flex">
                      <p>Catherine</p>
                    </div>
                    <p>Away</p>
                  </div>
                  <div class="badge badge-success badge-pill my-auto mx-2">4</div>
                  <small class="text-muted my-auto">23 min</small>
                </li>
                <li class="list">
                  <div class="profile"><img src="https://via.placeholder.com/40x40" alt="image"><span class="online"></span></div>
                  <div class="info">
                    <p>Daniel Russell</p>
                    <p>Available</p>
                  </div>
                  <small class="text-muted my-auto">14 min</small>
                </li>
                <li class="list">
                  <div class="profile"><img src="https://via.placeholder.com/40x40" alt="image"><span class="offline"></span></div>
                  <div class="info">
                    <p>James Richardson</p>
                    <p>Away</p>
                  </div>
                  <small class="text-muted my-auto">2 min</small>
                </li>
                <li class="list">
                  <div class="profile"><img src="https://via.placeholder.com/40x40" alt="image"><span class="online"></span></div>
                  <div class="info">
                    <p>Madeline Kennedy</p>
                    <p>Available</p>
                  </div>
                  <small class="text-muted my-auto">5 min</small>
                </li>
                <li class="list">
                  <div class="profile"><img src="https://via.placeholder.com/40x40" alt="image"><span class="online"></span></div>
                  <div class="info">
                    <p>Sarah Graves</p>
                    <p>Available</p>
                  </div>
                  <small class="text-muted my-auto">47 min</small>
                </li>
              </ul>
            </div>
            <!-- chat tab ends -->
          </div>
        </div>
        @include('backend.includes.sidebar') 
        <div class="main-panel">
            <div class="content-wrapper">
                <!-- @include('includes.partials.demo') -->
                <!-- @include('includes.partials.logged-in-as')  -->  
                @yield('page-header') 
                <!-- {{ \Route::current()->getName() }} -->
                <?php if (\Route::current()->getName() != 'admin.game.index' && \Route::current()->getName() != 'admin.store.index')  { ?>
                   @include('includes.partials.messages')
                <?php } ?>
               
                @yield('content') 
            </div><!--container-fluid-->
            <!-- @include('backend.includes.footer')  -->
        </div><!--main--> 
        <!-- @include('backend.includes.aside') -->
    </div><!--app-body--> 
    <!-- Scripts -->
    <script src="{{ asset('public/js/jquery.min.js') }}"></script>
    <script src="{{ asset('public/assets/vendors/base/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('public/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('public/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('public/assets/js/template.js') }}"></script>
    <script src="{{ asset('public/assets/js/settings.js') }}"></script>
    <script src="{{ asset('public/assets/js/todolist.js') }}"></script> 
    <script src="{{ asset('public/js/main.js') }}"></script>  
    @stack('before-scripts')
    <!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>  -->
    
    @stack('after-scripts')
</body>
</html>
