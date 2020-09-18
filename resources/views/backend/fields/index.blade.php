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
    <div class="card masterfields">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 success_error_message mb-4" style="display:none;">
                    <div class="alert alert-danger m-0">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    </div>
                </div>
            </div> 
                <form class="  p-0" id="fieldsform" method="POST" action="{{ url()->current() }}">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-3 col-lg-3 form-group ">
                            <label for="field_name">Field Name</label>
                            <input class="form-control p-3 w-100" type="text" name="field_name" id="field_name" placeholder="Field Name" maxlength="191" required="">
                        </div>
                        <div class="col-md-3 col-lg-3 form-group ">
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
                        <div class="col-md-3 col-lg-3 form-group ">
                            <label for="field_required">Is Required</label>
                            <select name="select_field_required" id="field_required" required class="form-control w-100 select_field_required">
                                <option value="no" selected>No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-3 form-group ">
                            <label for="field_show">Field Show</label>
                            <select name="select_field_show" id="field_show" required class="form-control w-100 select_field_show">
                                <option value="no" selected>No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-lg-3 form-group">
                            <label for="select_field_in_form">InForm</label>
                            <select name="select_field_in_form" id="select_field_in_form" class="form-control w-100 select_field_in_form">
                                <option value="no">No</option>
                                <option value="yes" selected>Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-3 form-group">
                            <label for="default_field">Is Default</label>
                            <select name="select_default_field" id="default_field" required class="form-control w-100 select_default_field">
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
                        <div class="col-md-3 col-lg-3 form-group unic_is">
                            <label for="select_is_fieldunic">Is UNIC</label>
                            <select name="select_is_fieldunic" id="select_is_fieldunic" required class="form-control w-100 select_is_fieldunic">
                                <option value="no">No</option> 
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-3 form-group unicName_text d-none">
                            <label for="fieldunic_name">UNIC Name</label>
                            <input class="form-control p-3 w-100 unicText" type="text" name="fieldunic_name" id="fieldunic_name" placeholder="UNIC Name" required="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-1 col-lg-2 form-group text-left">
                            <button type="button" class="cbtn csuccess btn-icon add_field " id="masterfields_sbt"><i class="mdi mdi-content-save"></i></button>
                        </div>
                    </div>
                </form>
           
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
                        <table class="table" id="fields_listTable">
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
                                <th>ID</th>
                                <th>Field Type</th> 
                                <th>Field Required</th>
                                <th>Field Show</th>
                                <th>InForm</th>
                                <th>Is UNIC</th>
                                <th>UNIC Text</th>
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
                                            <td width="12%">{{ $field->master_field_name }}</td> 
                                            <td width="12%">{{ ucfirst($field->master_field_sku) }}</td>
                                            <td width="12%">{{ ucfirst($field->master_field_type) }}</td>
                                            <td width="12%">{{ ucfirst($field->master_field_required )}} <?php // echo ucfirst("hello world!"); ?></td>
                                            <td width="12%">{{ ucfirst($field->master_field_show) }}</td>
                                            <td width="12%">{{ ucfirst($field->master_field_in_form) }}</td>
                                            <td width="12%">{{ ucfirst($field->master_field_isunic) }}</td>
                                            <td width="12%">{{ ucfirst($field->master_field_unicname) }}</td>
                                            <td width="20%" class="p-1 text-right">
                                                <div class="d-flex">
                                                    <button type="button" class="cbtn csuccess btn-icon p-1 edit_modal" data-id="{{$field->id}}" data-name="{{$field->master_field_name}}" data-type="{{$field->master_field_type}}" data-sku="{{$field->master_field_sku}}" data-inform="{{$field->master_field_in_form}}" data-required="{{$field->master_field_required}}" data-show="{{$field->master_field_show}}" data-default="{{$field->master_field_slug}}" data-isunic="{{$field->master_field_isunic}}" data-unicname="{{$field->master_field_unicname}}"><i class="mdi mdi-lead-pencil"></i></button>
                                                    <button type="button"  class="cbtn cdanger btn-icon p-1 delete_modal" data-id="{{$field->id}}" data-name="{{$field->master_field_name}}" value="Delete"><i class="mdi mdi-delete"></i></button>
                                                </div>
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

        <div class="modal-body py-2" id="modalbodypy2">
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
                            <label for="edit_f_inform">In Form</label>
                            <select name="edit_f_inform" id="edit_f_inform"  class="form-control p-3 mb-2" required>
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

                        <div class="form-group w-100 mb-3">
                            <label for="edit_f_isunic">Is UNIC</label>
                            <select name="edit_f_isunic" id="edit_f_isunic" required class="form-control p-3 w-100 edit_is_fieldunic"> 
                                <option value="no">No</option> 
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                         
                        <div class="form-group w-100 mb-3 edit_unicName_text d-none">
                            <label for="edit_funic_name">UNIC Name</label>
                            <input class="form-control p-3 w-100 editUnicText" type="text" name="edit_funic_name" id="edit_funic_name" placeholder="UNIC Name" required="">
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
    <script>
        $( document ).ready(function() {
            if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) {

           // if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                $( ".csuccess" ).click(function() {
                    setTimeout(function(){
                        $("#modalbodypy2").css("max-height", "400px");
                        $("#modalbodypy2").css("overflow", "scroll");
                    }, 1000);

                });
            }
            $("#field_required").change(function() {
                var field_required_value = this.value;
                if(field_required_value == 'yes'){
                    $("#select_field_in_form").val("yes");
                    $("#select_field_in_form").attr("disabled", true);
                }else{
                    $("#select_field_in_form").attr("disabled", false);
                }
                //alert(field_required_value);
            });
            $("#edit_f_required").change(function() {
                var field_required_value = this.value;
                if(field_required_value == 'yes'){
                    $("#edit_f_inform").val("yes");
                    $("#edit_f_inform").attr("disabled", true);
                }else{
                    $("#edit_f_inform").attr("disabled", false);
                }
                //alert(field_required_value);
            });
        });


    </script>
@endpush
@endsection


