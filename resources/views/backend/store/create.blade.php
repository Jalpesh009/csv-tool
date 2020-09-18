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
                        <label for="store_name">Store Name <span class="text-danger">*</span></label> 
                        <input class="form-control" type="text" name="store_name" id="store_name" value="{{ old('store_name') }}">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
            <div class="col-md-6">
                <div class="form-group row"> 
                    <div class="col-md-12">
                        <label for="city">City</label> 
                        <input class="form-control" type="text" name="city" id="city" value="{{ old('city') }}">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
        </div><!--row-->

        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <label for="postal_code">Postal Code</label> 
                        <input class="form-control" type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-9"> 
                        <label for="store_logo">Logo <span class="text-danger">*</span></label>  
                        <input type="file" name="store_logo" id="store_logo" class="file-upload-default">
                        <input type="hidden" name="logo" id="logo" class="form-control" value=" "> 
                        <div class="input-group col-xs-12">
                            <input type="text" class="form-control file-upload-info" disabled placeholder="Upload Store Logo">
                            <span class="input-group-append">
                            <button class="file-upload-browse btn btn-success" type="button">Choose Logo</button>
                            </span>
                        </div> 
                    </div>
                    <div class="col-md-3 store-logo-upload"> 
                        <label for="store_logo" class="invisible"> logo</label>  
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
                        <label for="contact_email">Contact Email <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="contact_email" id="contact_email" value="{{ old('contact_email') }}">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <label for="contact_person">Contact Person <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="contact_person" id="contact_person" value="{{ old('contact_person') }}">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
        </div><!--row-->

        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <label for="secondary_email">Secondary Email</label>
                        <input class="form-control" type="text" name="secondary_email" id="secondary_email" value="{{ old('secondary_email') }}">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <label for="secondary_person">Secondary Person</label>
                        <input class="form-control" type="text" name="secondary_person" id="secondary_person" value="{{ old('secondary_person') }}">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
        </div><!--row--> 

        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12">
                        <label for="contact_number">Contact Number <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}">
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6--> 
            <div class="col-md-6">
                <div class="form-group row"> 
                    <div class="col-md-12">
                        <label for="contact_number">Store Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="store_description" id="store_description">{{ old('store_description') }}</textarea>
                    </div><!--col-md-12-->
                </div><!--form-group row-->
            </div><!--col-md-6-->
        </div><!--row-->

        <div class="row">
             
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-md-12"> 
                    <label for="store_csv">Static CSV Upload <span class="text-danger">*</span></label>  
                        <input type="file" name="store_csv" id="store_csv" accept=".csv,.xls,.xlsx,.xlsm" class="file-upload-default">
                         
                        <div class="input-group col-xs-12">
                            <input type="text" class="form-control file-upload-info" disabled placeholder="Upload CSV">
                            <span class="input-group-append">
                            <button class="file-upload-browse btn btn-success" type="button">Choose CSV</button>
                            </span>
                        </div> 
                    </div>
                    <!-- <div class="col-md-3 store-logo-upload1"> 
                        <img class="img-responsive logo_img1" src="" alt="" id="csv_path">
                        <a href="javascript:void(0);" class="delete_image1" style="display:none;"><div class="badge badge-pill badge-warning "><i class="mdi mdi-close"></i></div> </a>
                    </div> col-md-6  -->
                </div><!--form-group row-->
            </div><!--col-md-6-->
            
            <div class="col-md-6">
                <div class="form-group"> 
                    <label for="store_indentifier">Indentifier<span class="text-danger">*</span></label>  
                    <select name="indentifier" id="store_indentifier" class="form-control">
                        <option value="">Select Indentifier</option>
                        <option value="1" <?php if(old('indentifier') == '1'){ echo 'selected'; } ?> >Amazon</option>
                        <option value="2" <?php if(old('indentifier') == '2'){ echo 'selected'; } ?> >Fnac</option>
                        <option value="3" <?php if(old('indentifier') == '3'){ echo 'selected'; } ?> >Micromania</option>
                        <option value="4" <?php if(old('indentifier') == '4'){ echo 'selected'; } ?> >Auchan</option>
                        <option value="5" <?php if(old('indentifier') == '5'){ echo 'selected'; } ?> >Leclerc</option>
                        <option value="6" <?php if(old('indentifier') == '6'){ echo 'selected'; } ?> >Cdiscount</option>
                        <option value="7" <?php if(old('indentifier') == '7'){ echo 'selected'; } ?> >Carrefour</option>
                        <option value="8" <?php if(old('indentifier') == '8'){ echo 'selected'; } ?> >Cultura</option>
                    </select>
                </div>  
            </div><!--col-md-6-->

        </div><!--row-->
        <div class="row">
             
            
            
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