@extends('backend.layouts.app')
@section('title', 'Edit Store'. ' | '. app_name())
@section('description', 'Edit Store')
@section('keywords', 'Edit Store')
@section('content')
@push('before-styles')  
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"> 
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.2.5/css/rowReorder.dataTables.min.css">
    <style>
   
    </style>
@endpush 

<div class="row">
    <div class="col-md-12 store_success_message" >  
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <span>{{ $message }}</span>
            </div>
        @endif
    </div><!--col-md-12-->
</div><!--row--> 
<div class="col-12 grid-margin stretch-card p-0">
    <div class="card">
       
           
            <div class="row"> 
                <div class="col-sm-12"> 
                    <form class="forms-sample" id="eventform" method="POST" action="{{ route('admin.store.update', $store->id  ) }}"  enctype="multipart/form-data">
                        {{ csrf_field() }} 
                        <input type="hidden" name="_method" value="PUT">
                        <div class="card-body  border-bottom"> 
                            <div class="row"> 
                                <div class="col-sm-5">
                                    <h4 class="card-title mb-0">Edit Retailer information</h4>
                                </div><!--col--> 
                                <div class="col-md-7">
                                    <div class="form-group text-right mb-0">  
                                        
                                        <button class="cbtn csuccess btn-icon" type="submit" name="store_edit" id="store_edit" value="SAVE"><i class="mdi mdi-content-save"></i></button>
                                        <button type="button" class="cbtn cdanger btn-icon mr-0" onclick="window.location.href = '{{ url('admin/store') }}'"><i class="mdi mdi-close"></i></button> 
                                    </div> 
                                </div> 
                            </div>  
                        </div>
                        <div class="card-body "> 
                            <div class="row pt-3">
                                <div class="col-md-3 col-lg-2">
                                    <div class="form-group row"> 
                                        <div class="col-md-12">
                                              
                                            <div class="container-image"> 
                                                <img class="img-responsive change-image" src="{{ $store->logo }}" alt="{{$store->logo}}" id="imagePreview">   
                                                <input type="file" name="store_logo" id="store_logo" class="uploader" > 
                                                <input type="hidden" name="logo" id="logo" class="form-control" value="{{ $store->logo }}"> 
                                                <div class="overlay">
                                                    <p class="change_imgtxt text-center">Upload Store</br> Logo</p>
                                                    <i class="mdi mdi-cloud-upload"></i>
                                                </div>
                                            </div>    
                                              
                                        </div> 
                                    </div> 
                                </div> 
                                <div class="col-md-9 col-lg-10">
                                    <div class="form-group row"> 
                                        <div class="col-md-12">
                                            <textarea class="form-control" name="store_description" id="store_description" placeholder="Store Description *" rows="8">{{ $store->store_description }}</textarea>
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row"> 
                                        <div class="col-md-12">
                                            <label for="store_name">Retailer Name:<span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="store_name" id="store_name" placeholder="Store Name" value="{{ $store->store_name }}">
                                        </div> 
                                    </div> 
                                </div> 
                                <div class="col-md-6">
                                    <div class="form-group row"> 
                                        <div class="col-md-12">
                                            <label for="city">City:</label>
                                            <input class="form-control" type="text" name="city" id="city" placeholder="City" value="{{ $store->city }}">
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="postal_code">Postal:</label>
                                            <input class="form-control" type="text" name="postal_code" id="store_postalCode" placeholder="Postal Code" value="{{ $store->postal_code }}">
                                        </div> 
                                    </div> 
                                </div> 
                                <div class="col-md-6">
                                    <div class="form-group row"> 
                                        <div class="col-md-12">
                                            <label for="contact_email">Contact Email:<span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="contact_email" id="contact_email" placeholder="Contact Email" value="{{ $store->contact_email }}">
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="contact_person">Contact Person:<span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="contact_person" id="contact_person" placeholder="Contact Person" value="{{ $store->contact_person }}">
                                        </div> 
                                    </div> 
                                </div> 
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="secondary_email">Secondary Email:</label>
                                            <input class="form-control" type="text" name="secondary_email" id="secondary_email" placeholder="Secondary Email" value="{{ $store->secondary_email }}">
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="secondary_person">Secondary Person:</label>
                                            <input class="form-control" type="text" name="secondary_person" id="secondary_person" placeholder="Secondary Person" value="{{ $store->secondary_person }}">
                                        </div> 
                                    </div> 
                                </div> 
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="contact_number">Contact Number: <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="contact_number" id="contact_number" placeholder="Contact Number" value="{{ $store->contact_number }}">
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 

                            <div class="row">
             
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-9"> 
                                        <label for="store_csv">Static CSV Upload <span class="text-danger">*</span></label>  
                                            <input type="file" name="edit_store_csv" id="edit_store_csv" accept=".csv,.xls,.xlsx,.xlsm" class="file-upload-default">
                                            <input type="hidden" name="old_store_csv" id="old_store_csv" class="form-control" value="{{ $store->static_csv }}">  
                                            <div class="input-group col-xs-12">
                                                <input type="text" class="form-control file-upload-info" disabled placeholder="Upload CSV">
                                                <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-success" type="button">Choose CSV</button>
                                                </span>
                                            </div> 
                                            <?php
                                            $stocsv = $store->static_csv;
                                            $name = basename($stocsv);
                                            ?>
                                            <span class="csv_name">{{ $name  }}</span>   
                                        </div>
                                        <div class="col-md-3 store-logo-upload1"> 
                                            <label class="invisible" >Static CSV Upload </label>  
                                            <?php if( $store->static_csv ){ ?>
                                                <a class="nounderline" href="{{ $store->static_csv }}" download alt="" id="edit_csv_path"> 
                                                    <img class="img-responsive csv_img" src="{{ url('/public/img/backend/excel/csv.png') }}" alt="{{$store->logo}}" width="60" height="60">
                                                    
                                                </a>
                                            <?php } ?>
                                            
                                        </div><!--col-md-6-->
                                    </div><!--form-group row-->
                                </div><!--col-md-6-->

                                <div class="col-md-6">
                                    <div class="form-group"> 
                                        <label for="store_indentifier">Indentifier<span class="text-danger">*</span></label>  
                                        <select name="indentifier" id="store_indentifier" class="form-control"> 
                                            <option value="">Select Indentifier</option>
                                            <option value="1" {{ $store->indentifier == 1 ? 'selected' : '' }}>Amazon</option>
                                            <option value="2" {{ $store->indentifier == 2 ? 'selected' : '' }}>Fnac</option>
                                            <option value="3" {{ $store->indentifier == 3 ? 'selected' : '' }}>Micromania</option>
                                            <option value="4" {{ $store->indentifier == 4 ? 'selected' : '' }}>Auchan</option>
                                            <option value="5" {{ $store->indentifier == 5 ? 'selected' : '' }}>Leclerc</option>
                                            <option value="6" {{ $store->indentifier == 6 ? 'selected' : '' }}>Cdiscount</option>
                                            <option value="7" {{ $store->indentifier == 7 ? 'selected' : '' }}>Carrefour</option>
                                            <option value="8" {{ $store->indentifier == 8 ? 'selected' : '' }}>Cultura</option>
                                        </select>
                                    </div>  
                                </div><!--col-md-6-->
                                
                            </div><!--row-->

                            <div class="row"> 
                                <div class="col-md-12">
                                    <div class="form-group text-right">  
                                        <button class="cbtn csuccess btn-icon" type="submit" name="store_edit" id="store_edit" value="SAVE"><i class="mdi mdi-content-save"></i></button>
                                        <button type="button" class="cbtn cdanger btn-icon mr-0" onclick="window.location.href = '{{ url('admin/store') }}'"><i class="mdi mdi-close"></i></button> 
                                    </div> 
                                </div> 
                            </div>  
                        </div><!--card-body-->
                    </form>   
                </div><!--col-12--> 
            </div><!--row--> 
       
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0-->
 


<!-- {{ html()->form()->close() }} --> 
@push('before-scripts') 
    <script src="{{ asset('/public/assets/js/file-upload.js') }}"></script> 
    <script src="{{ asset('/public/assets/js/tabs.js') }}"></script> 

    <script src="{{ asset('public/assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script> 
     <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.2.5/js/dataTables.rowReorder.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.3.0/js/dataTables.select.min.js"></script>
    <script src="{{ url('public/js/dataTables.editor.js') }}"></script> 
     
    <script type="text/javascript">  
        
        $(document).ready(function() { 
            
            

            $(".header").click(function () {

                $header = $(this);
                //getting the next element
                $content = $header.next();
                //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
                $content.slideToggle(500, function () { 
                    $header.html(function () {
                        //change text based on condition
                        return $content.is(":visible") ? '<div class="btn-toolbar float-right" role="toolbar" aria-label="@lang("labels.general.toolbar_btn_groups")"><button type="button" class="cbtn csuccess btn-icon"><i class="mdi mdi-content-save"></i></span></button><button type="button" class="cbtn cdanger btn-icon cancel_btn mr-0"><i class="mdi mdi-close"></i></span></button></div>' : '<div class="btn-toolbar float-right" role="toolbar" aria-label="@lang("labels.general.toolbar_btn_groups")"><button type="button"  class="cbtn cprimary btn-icon mr-0"><i class="mdi mdi-plus"></i></span></button> </div>';
                    });
                });

            });
            $(".new-store-form .alert").is(":visible") ?  $(".new-store-form").show() : $(".new-store-form").hide();
            $(".new-store-form").is(":visible") ? $(".header").html('<div class="btn-toolbar float-right" role="toolbar" aria-label="@lang("labels.general.toolbar_btn_groups")"><button type="button" class="cbtn csuccess btn-icon"><i class="mdi mdi-content-save"></i></span></button><button type="button" class="cbtn cdanger btn-icon cancel_btn mr-0"><i class="mdi mdi-close"></i></span></button></div>') : $(".header").html('<div class="btn-toolbar float-right" role="toolbar" aria-label="@lang("labels.general.toolbar_btn_groups")"><button type="button"  class="cbtn cprimary btn-icon mr-0"><i class="mdi mdi-plus"></i></span></button> </div>') ; 
    } );
    

    </script>

@endpush 


@endsection 