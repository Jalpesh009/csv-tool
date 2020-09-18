@extends('backend.layouts.app')
@push('before-styles') 
    <link rel="stylesheet" href="{{ asset('public/assets/vendors/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css') }}"> 
    <link rel="stylesheet" href="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
@endpush 
@section('title', 'Manage Fields'. ' | '. app_name())
@section('description', 'Manage Fields')
@section('keywords', 'Manage Fields')
@section('content') 
<div class="col-12 grid-margin stretch-card p-0">
    <div class="card">
        <div class="card-body"> 
            <div class="row">
                <div class="col-lg-12 success_error_message mb-4" style="display:none;"> 
                    <div class="alert alert-danger m-0">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> 
                    </div>
                </div> 
            </div> 
            <div class="row">  
                <form class="form-inline col-md-12 p-0" id="fieldsform" method="POST" action="{{ url()->current() }}">
                    {{ csrf_field() }}
                    <div class="col-md-3 form-group">  
                        <label for="field_name">Field Name</label> 
                        <input class="form-control p-3 w-100" type="text" name="field_name" id="field_name" placeholder="Field Name" maxlength="191" required=""> 
                    </div>  
                    <div class="col-md-2 form-group">  
                        <label for="field_type">Field Type</label> 
                        <select name="select_field_type" id="field_type" placeholder="Field Name"  required class="form-control w-100">
                            <option value="">Field Type</option>
                            <option value="float">Float</option>
                            <option value="varchar">Varchar</option>
                            <option value="text">Text</option>
                            <option value="date">Date</option>
                            <option value="image">Image</option>
                            <option value="gallery">Gallery</option>
                            <option value="readonly">Read Only</option>
                        </select> 
                    </div>  
                    <div class="col-md-2 form-group">  
                        <label for="field_required">Field Required</label> 
                        <select name="select_field_required" id="field_required" required class="form-control w-100"> 
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option> 
                        </select> 
                    </div>  
                    <div class="col-md-2 form-group">  
                        <label for="field_show">Field Show</label> 
                        <select name="select_field_show" id="field_show" required class="form-control w-100"> 
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option> 
                        </select> 
                    </div>  
                    <div class="col-md-2 form-group">  
                        <label for="default_field">Default Field</label> 
                        <select name="select_default_field" id="default_field" required class="form-control w-100"> 
                            <?php 
                            $selected = '';
                            foreach ($defaultFields as $key => $value) {
                                if( $key == 4){
                                    $selected = 'selected';
                                }
                                echo '<option value="'. $value['field_slug'] .'" '.$selected.'>'. $value['field_name'] .'</option>';
                            } ?> 
                             
                        </select> 
                    </div>  
                    <div class="col-md-1 form-group text-right">  
                    <button type="button" class="cbtn csuccess btn-icon mt-4 add_field " id="masterfields_sbt"><i class="mdi mdi-content-save"></i></button> 
                    </div>  
                </form> 
            </div>
        </div><!--card-body-->
    </div><!--card-->    
</div> 
<div class="col-12 grid-margin stretch-card p-0 store_listing">
    <div class="card">
        <div class="card-body">   
            <div class="row">
                <div class="col-sm-12"><h4 class="card-title">Field List</h4></div><!--col--> 
            </div><!--row-->     
            <div class="row">
                <div class="col fields_list">

                    <div class="table-responsive">
                        <table class="table" id=" ">
                            <thead>
                            <tr>
                                <?php if($count_fields > 0 ){  ?>
                                <th width="3%"> 
                                    <div class="form-check my-0">
                                        <label class="form-check-label"> 
                                            <input type="checkbox" name="field_ids" class="form-check-input delete_all_fields" >   
                                        <i class="input-helper"></i></label>
                                    </div>
                                </th>
                                <?php } ?>
                                <th>Field Name</th>
                                <th>Field Type</th>
                                <th>ID</th>
                                <th>Field Required</th>
                                <th>Field In List</th>
                                <?php if($count_fields > 0 ){  ?>
                                 <th data-orderable="false" class="nosort text-right pr-1"> 
                                    <button type="submit" name="delete" class="cbtn cdanger btn-icon delete_f_modal" value="Delete"><i class="mdi mdi-delete m-0"></i></button>  
                                </th>
                                <?php } ?> 
                            </tr>
                            </thead>
                            <tbody>
                                <?php if (count($fields) > 0 ) { 

                                    foreach($fields as $field) { ?> 
                                        <tr id="iteam{{ $field->id }}" class=""> 
                                            <td width="5%">
                                                <div class="form-check tr_field">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" name="field_id" value="{{$field->id}}" class="form-check-input" ><i class="input-helper"></i></label>
                                                </div>
                                            </td>
                                            <td width="20%">{{ $field->master_field_name }}</td>
                                            <td width="20%">{{ $field->master_field_type }}</td>
                                            <td width="20%">{{ $field->master_field_sku }}</td>
                                            <td width="12%">{{ $field->master_field_required }}</td>
                                            <td width="12%">{{ $field->master_field_show }}</td>

                                            <td width="20%" class="p-1 text-right"> 
                                                <button type="button" class="cbtn csuccess btn-icon edit_modal" data-id="{{$field->id}}" data-name="{{$field->master_field_name}}" data-type="{{$field->master_field_type}}" data-sku="{{$field->master_field_sku}}" data-required="{{$field->master_field_required}}" data-show="{{$field->master_field_show}}" data-default="{{$field->master_field_slug}}"><i class="mdi mdi-lead-pencil"></i></button>                                          
                                                <button type="button"  class="cbtn cdanger btn-icon delete_modal" data-id="{{$field->id}}" data-name="{{$field->master_field_name}}" data-toggle="modal" data-target="#delete_field_modal" value="Delete"><i class="mdi mdi-delete"></i></button>
                                                 
                                            </td>
                                        </tr>
                                    <?php  }
                                } else { ?>
                                    <tr>
                                        <td colspan="5" align="center">No data found!</td> 
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div><!--col-->
            </div><!--row-->
        </div><!--card-body-->
    </div><!--card-->
</div> 
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-md w-50">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Edit Field</h4>
            <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>         
        </div>
        <div class="p-2 my-2">
            <div class="col-lg-12 success_error_message_edit" style="display:none;"> 
                <div class="alert alert-danger m-0">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> 
                </div>
            </div> 
        </div> 

        <div class="modal-body py-2">
            <form class="forms-sample" id="eventform_edit" method="POST">
                {{ csrf_field() }}
                <input class="form-control" type="hidden" name="edit_f_id" id="edit_field_id" value="">
                <div class="row  ">
                    <div class="col">
                        <div class="form-group w-100 mb-3">
                            <label for="edit_field_name">Field Name</label> 
                                <input class="form-control" type="text" name="edit_f_name" id="edit_field_name" value="" placeholder="Field Name" maxlength="191" required="">
                           
                        </div>
                        <div class="form-group w-100 mb-3">
                            <label for="edit_field_type">Field Type</label>  
                            <select name="edit_f_type" id="edit_field_type"  class="form-control p-3 mb-2" required>
                                <option value="">Please Select</option>
                                <option value="float">Float</option>
                                <option value="varchar">Varchar</option>
                                <option value="text">Text</option>
                                <option value="date">Date</option>
                                <option value="image">Image</option>
                                <option value="gallery">Gallery</option>
                                <option value="readonly">Read Only</option>
                            </select> 
                        </div>  
                        <div class="form-group w-100 mb-3">
                            <label for="edit_field_required">Field Required</label>  
                            <select name="edit_f_required" id="edit_field_required"  class="form-control p-3 mb-2" required> 
                                <option value="no">No</option>
                                <option value="yes">Yes</option> 
                            </select> 
                        </div>  
                        <div class="form-group w-100 mb-3">
                            <label for="edit_field_show">Field Show</label>  
                            <select name="edit_f_show" id="edit_field_show"  class="form-control p-3 mb-2" required> 
                                <option value="no">No</option>
                                <option value="yes">Yes</option> 
                            </select> 
                        </div>
                        <div class="form-group w-100 mb-3">
                            <label for="edit_field_default">Default Field</label>   
                            <select name="edit_f_default" id="edit_field_default" required class="form-control p-3 w-100"> 
                                <?php 
                                $selected = '';
                                foreach ($defaultFields as $key => $value) {
                                    if( $key == 4){
                                        $selected = 'selected';
                                    }
                                    echo '<option value="'. $value['field_slug'] .'" '.$selected.'>'. $value['field_name'] .'</option>';
                                } ?> 
                            </select> 
                        </div>   
                        
                    </div><!--col-->
                </div> 
            </form>
        </div>
        <div class="modal-footer py-3">
            <button class="edit_field cbtn csuccess btn-icon"  id="masterfields_edit" type="submit"><i class="mdi mdi-content-save"></i></button>
            <button type="button" class="cbtn cdanger btn-icon" data-dismiss="modal"><i class="mdi mdi-close"></i></button>
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="delete_field_modal" role="dialog">
    <div class="modal-dialog modal-md w-50">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Confirm Delete Field</h4>
            <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>         
        </div>
        <div class="modal-body text-center"> 
            <span>Are you sure want to delete  <span class="modal_fieldName" style="color:#e94437;"></span>?</span>
        </div>
        <div class="modal-footer py-3"> 
            <button class="cbtn cdanger btn-icon delete_modal_field" data-dismiss="modal" type="submit"><i class="mdi mdi-delete"></i></button> 
            <button type="button" class="cbtn cprimary btn-icon" data-dismiss="modal"><i class="mdi mdi-close"></i></button>
        </div>
      </div>
    </div>
</div>


<div class="modal fade" id="delete_storefield_modal" role="dialog">
    <div class="modal-dialog modal-md w-50">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Confirm Delete Matrix</h4>
            <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>         
        </div>
        <div class="modal-body text-center"> 
            <span>Are you sure want to delete selected/all matrix fields?</span>
        </div>
        <div class="modal-footer py-3"> 
            <form method="POST" action="{{ url( 'admin/masterfields/deleteall') }}">
                {{ csrf_field() }}
                <input name="fields_ids_arr" id="fields_ids_arr" type="hidden">
                <button type="submit" name="delete" class="cbtn cdanger btn-icon delete_all" value="Delete"><i class="mdi mdi-delete m-0"></i></button> 
                <button type="button" class="cbtn cprimary btn-icon" data-dismiss="modal"><i class="mdi mdi-close"></i></button>
            </form>  
            
        </div>
      </div>
    </div>
</div>
<div class="modal fade" id="delete_beforeValidation_modal" role="dialog">
    <div class="modal-dialog modal-md w-50">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Manage Matrix</h4>
            <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>         
        </div>
        <div class="modal-body text-center"> 
            <span>Please select at least one or more fields to delete.</span>
        </div>
        <div class="modal-footer py-3">  
            <button type="button" class="cbtn cprimary btn-icon" data-dismiss="modal"><i class="mdi mdi-close"></i></button>
        </div>
      </div>
    </div>
</div>
@push('before-scripts') 
    <script src="{{ asset('public/assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script> 
    <script src="{{ asset('public/assets/js/data-table.js') }}"></script> 
    
@endpush 
@endsection


