<div class="mt-3">

    @include('includes.partials.messages')
     
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <span>{{ $message }}</span>
        </div>
    @endif
    <form class="forms-sample" id="eventform" method="POST" action="{{ route('admin.store.store') }}"  enctype="multipart/form-data">
        {{ csrf_field() }} 
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group row"> 
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="store_name" id="store_name" placeholder="Store Name *">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
            <div class="col-md-6">
                <div class="form-group row"> 
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="city" id="city" placeholder="City">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
        </div><!--row-->

        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="postal_code" id="postal_code" placeholder="Postal Code">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-9">  
                        <input type="file" name="store_logo" id="store_logo" class="file-upload-default">
                        <input type="hidden" name="logo" id="logo" class="form-control" value=" "> 
                        <div class="input-group col-xs-12">
                            <input type="text" class="form-control file-upload-info" disabled placeholder="Upload Image">
                            <span class="input-group-append">
                            <button class="file-upload-browse btn btn-success" type="button">Choose Image</button>
                            </span>
                        </div> 
                    </div>
                    <div class="col-md-3 store-logo-upload"> 
                        <img class="img-responsive logo_img1" src="" alt="" id="imagePreview">
                        <a href="javascript:void(0);" class="delete_image" style="display:none;"><div class="badge badge-pill badge-warning "><i class="mdi mdi-close"></i></div> </a>
                    </div><!--col-md-6-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
        </div><!--row-->

        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="contact_email" id="contact_email" placeholder="Contact Email *">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="contact_person" id="contact_person" placeholder="Contact Person *">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
        </div><!--row-->

        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="secondary_email" id="secondary_email" placeholder="Secondary Email">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="secondary_person" id="secondary_person" placeholder="Secondary Person">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
        </div><!--row--> 

        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                       <input class="form-control" type="text" name="contact_number" id="contact_number" placeholder="Contact Number *">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6--> 
            <div class="col-md-6">
                <div class="form-group row"> 
                    <div class="col-md-12">
                        <textarea class="form-control" name="store_description" id="store_description" placeholder="Store Description *" ></textarea>
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
        </div><!--row-->

        <div class="row"> 
            <div class="col-md-12">
                <div class="form-group text-right">  
                    <button class="cbtn csuccess btn-icon" type="submit" name="store_submit" id="store_submit" value="SAVE"><i class="mdi mdi-content-save"></i></button>
                    <button type="button" class="cbtn cdanger btn-icon mr-0" onclick="window.location.href = '{{ url('admin/store') }}'"><i class="mdi mdi-close"></i></button> 
                </div><!--form-group-->
            </div><!--col-md-12--> 
        </div><!--row--> 
    
    </form>
</div><!--col--> 
<?php /* ?>
@push('before-scripts') 
    <script src="{{ asset('/public/assets/js/file-upload.js') }}"></script> 
@endpush 
<?php /*/ ?>