@extends('backend.layouts.app')
@push('before-styles')  
    <!-- <link rel="stylesheet" href="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}"> -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"> 
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.2.5/css/rowReorder.dataTables.min.css">
    <style type="text/css">
        button:disabled,
        button[disabled]{
              opacity: 0.6;
        }
        div#games-listing_filter {
            display: none;
        }
    </style>
@endpush 
@section('title',  'View Store'. ' | '. app_name()) 
@section('description', 'View Store')
@section('keywords', 'View Store')
@section('content')

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
        <div class="card-body">  
            <div class="row">
                <div class="col-sm-6"> 
                    <ul class="nav nav-tabs border-0" role="tablist">
                        <li class="nav-item">
                          <a class="nav-link active" href="#basic-info" id="basic-info-tab" data-toggle="tab" href="#basic-info" role="tab" aria-controls="basic-info" aria-selected="true">Retailer Information</a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" href="#manage-fields" id="manage-fields-tab" data-toggle="tab" href="#manage-fields" role="tab" aria-controls="manage-fields" aria-selected="false">Manage Fields</a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" href="#game-list" id="game-list-tab" data-toggle="tab" href="#game-list" role="tab" aria-controls="game-list" aria-selected="false">Game List</a>
                        </li>
                    </ul>    
                </div>
                <div class="col-sm-6"> 
                    <div class=" text-right">   
                        <button type="button" class="cbtn csuccess btn-icon " onclick="window.location.href = '{{ route('admin.store.edit', $store->id ) }}'"><i class="mdi mdi-lead-pencil"></i></button> 
                        <button type="button" class="cbtn cdanger btn-icon " onclick="window.location.href = '{{ url('admin/store') }}'"><i class="mdi mdi-close"></i></button> 
                    </div>  
                </div>
            </div>
            <hr class="mb-0">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="basic-info-tab"> 
                    <div class="row">
                        <div class="col-12">   
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group row"> 
                                        <div class="col-md-12">
                                            <img src="{{  $store->logo  }}" class="img-responsive change-image" >
                                        </div> 
                                    </div> 
                                </div> 
                                <div class="col-md-8">
                                    <div class="form-group row"> 
                                        <div class="col-md-12">
                                            <div>{{ $store->store_description }}</div>
                                        </div> 
                                    </div> 
                                </div> 
                               
                            </div>  

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row"> 
                                        <div class="col-md-12">
                                            <label for="store_name">Store Name <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" readonly name="store_name" id="store_name" placeholder="Store Name" value="{{ $store->store_name }}">
                                        </div> 
                                    </div> 
                                </div> 
                                <div class="col-md-6">
                                    <div class="form-group row"> 
                                        <div class="col-md-12">
                                            <label for="city">City</label>
                                            <input class="form-control" type="text" readonly name="city" id="city" placeholder="City" value="{{ $store->city }}">
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="postal_code">Postal Code</label>
                                            <input class="form-control" type="text" readonly name="postal_code" id="store_postalCode" placeholder="Postal Code" value="{{ $store->postal_code }}">
                                        </div> 
                                    </div> 
                                </div>  
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="contact_email">Contact Email <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" readonly name="contact_email" id="contact_email" placeholder="Contact Email" value="{{ $store->contact_email }}">
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 

                            <div class="row"> 
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="contact_person">Contact Person <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" readonly name="contact_person" id="contact_person" placeholder="Contact Person" value="{{ $store->contact_person }}">
                                        </div> 
                                    </div> 
                                </div> 
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="secondary_email">Secondary Email</label>
                                            <input class="form-control" type="text" readonly name="secondary_email" id="secondary_email" placeholder="Secondary Email" value="{{ $store->secondary_email }}">
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 

                            <div class="row"> 
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="secondary_person">Secondary Person</label>
                                            <input class="form-control" type="text" readonly name="secondary_person" id="secondary_person" placeholder="Secondary Person" value="{{ $store->secondary_person }}">
                                        </div> 
                                    </div> 
                                </div> 
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="contact_number">Contact Number <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" readonly name="contact_number" id="contact_number" placeholder="Contact Number" value="{{ $store->contact_number }}">
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 
                         
                        </div><!--col-12--> 
                    </div><!--row-->  
                </div>
                
                <div class="tab-pane fade" id="manage-fields" role="tabpanel" aria-labelledby="manage-fields-tab"> 
                    <div class="row manage_fields_div">
                        <div class="col-12"> 
                            <h4 class="card-title">Retailer Matrix Field</h4> 
                            <form class="forms-sample" id="edit_managed_fields" method="POST" action="{{ route('admin.update_manage_fields', $store->id ) }}" >
                            {!! csrf_field() !!}

                                <input type="hidden" name="_method_managed_fields" value="POST">
                                <input type="hidden" name="store_id" value="{{ $store->id }}">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">   
                                            <table class="table"> 
                                                <tr class="all_field_tr"> 
                                                    <td >
                                                        <div class="form-check">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" name="moveall" value="moveall" class="form-check-input moveall_cls" > All Fields
                                                            </label>
                                                        </div>
                                                    </td> 
                                                    <td class="text-right">
                                                          
                                                        <button class="cbtn csuccess btn-icon" type="submit" name="store_addFields" id="store_edit1" value="Add Fields"><i class="mdi mdi-plus"></i></button>
                                               
                                                        <button type="submit" name="store_deleteFields" value="Delete Fields" class="cbtn cdanger btn-icon" onclick="window.location.href = '{{ url('admin/store') }}'"><i class="mdi mdi-close"></i></button> 
                                                    
                                                    </td>
                                                </tr>  
                                                <?php  
                                                foreach ($fields as $key => $value) {
                                                    $checked = '';
                                                    $btnclass = 'csuccess';
                                                    $btntxt= '<i class="mdi mdi-plus"></i>';
                                                    $defaultenabled = '';
                                                    $onclk = "store_add_field('add_remove_field_".$key."')";
                                            
                                                    if($selected_fields != null && $value->id != null ){
                                                        if (in_array( $value->id, $selected_fields)) { 
                                                            $checked = 'checked';
                                                            $btnclass = 'cdanger';
                                                            $btntxt= '<i class="mdi mdi-close"></i>';
                                                            $defaultenabled = 'disabled';
                                                            $onclk = "store_remove_field('add_remove_field_".$key."')";
                                                     
                                                        } 
                                                    } ?>
                                                    <tr class="field_tr"> 
                                                        <td>
                                                            <div class="form-check">
                                                                <label class="form-check-label"> 
                                                                    <input type="checkbox" name="field_id[]" value="{{ $value->id }}" class="form-check-input" {{ $checked }}>{{ $value->master_field_name }}

                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td align="right" class="">
                                                            <button onclick="{{ $onclk }}" type="button" class="move_single_field cbtn {{ $btnclass }} btn-icon" data-fieldval="{{ $value->id }}" data-storeid="{{ $store->id }}" id="add_remove_field_{{ $key }}" ><?php echo $btntxt; ?></button> </td> 
                                                    </tr> 
                                                    <?php  
                                                } ?> 
                                            </table>
                                        </div> 
                                    </div>   
                                </div> 
                                <div class="row"> 
                                    <div class="col-md-12 ">
                                        <div class="form-group text-right">  
                                            <button class="cbtn csuccess btn-icon" type="submit" name="store_addFields" id="store_edit1" value="Add Fields"><i class="mdi mdi-plus"></i></button>
                                   
                                            <button type="submit" name="store_deleteFields" value="Delete Fields" class="cbtn cdanger btn-icon" onclick="window.location.href = '{{ url('admin/store') }}'"><i class="mdi mdi-close"></i></button> 
                                        </div> 
                                    </div> 
                                </div>  
                            </form>  
                        </div><!--col--> 
                    </div><!--row--> 
                </div><!--tab-pane fade show active--> 
                <?php   ?> 
                <div class="tab-pane fade" id="game-list" role="tabpanel" aria-labelledby="game-list-tab"> 
                    <div class="row">
                        <div class="col-12">
                            
                            <h4 class="card-title">Game List</h4>
                            <div class="row">

                                <div class="col fields_list">
                                    <div id="tableDiv" class="table-responsive table_store_fields" style="display:none;"></div>
                                    
                                </div><!--col-->
                            </div><!--row--> 

                        </div><!--col--> 
                    </div><!--row--> 
                </div><!--tab-pane fade show active--> 

                <?php  ?> 

            </div>

        </div><!--card-body-->
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0--> 
 
<div class="col-12 grid-margin stretch-card p-0">
    <div class="card">
        <div class="card-body">  

            <div class="row">
                <div class="col-sm-4">
                   <h4 class="card-title">Retailer Matrix Field</h4>
                </div>
                <div class="col-sm-8">
                    <?php if($count_all_fields > 0 ){ ?>
                        <div class="text-right mb-4">
                           
                            <button type="button" class="cbtn cdanger btn-icon delete_sf_modal"  value="Delete"  ><i class="mdi mdi-delete"></i></button>
                            
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="loader_cls border-0" style="border:0!important;">
                <div class="loader-demo-box">
                    <div class="dot-opacity-loader">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div> 
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive table_store_fields" style="display:none;"> 
                        <table class="display w-100" id="manages_store_fields" > 
                            <thead> 
                                <tr> 
                                    <th>ID</th>
                                    <th>
                                    <?php if($count_all_fields > 0 ){ ?>
                                        
                                            <div class="form-check tr_field"><label class="form-check-label">
                                                <input type="checkbox" name="delete_store_fields" value="delete_store_fields" id="delete_store_fields"><i class="input-helper"></i></label> </div>
                                        
                                    <?php } ?>
                                    </th>
                                    <th>Field Name</th>
                                    <th>Action</th>  
                                </tr> 
                            </thead>
                            <tbody> </tbody>
                        </table> 
                    </div><!--row-->  
                </div><!--card-body-->
            </div><!--card-->
        </div><!--card-body-->
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0--> 


<div class="modal fade" id="delete_storefield_modal" role="dialog">
    <div class="modal-dialog modal-md w-50">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Confirm Delete Retailer Fields</h4>
            <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>         
        </div>
        <div class="modal-body text-center"> 
            <span>Are you sure want to delete selected/all retailer fields?</span>
        </div>
        <div class="modal-footer py-3"> 
              
            <form method="POST" action="{{ url( 'admin/store/'.$store->id.'/view/deletestorefieldsall') }}">
                {{ csrf_field() }}
                <input name="delete_store_fields_arr" id="delete_store_fields_arr" type="hidden"> 
                <input name="store_id" id="store_id" type="hidden" value="{{$store->id }}"> 
                <button type="submit" name="delete" class="cbtn cdanger btn-icon delete_store_fields_all" value="Delete"><i class="mdi mdi-delete"></i></button>  
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
            <h4 class="modal-title">Manage Retailer Fields</h4>
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

<div class="modal fade" id="gamestore_modal" role="dialog">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Remove Game</h4>
            <!-- <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>       -->
            <button class="close m-0 p-0" data-dismiss="modal" ><i class="mdi mdi-close"></i></button>    
        </div> 
        <div class="modal-body text-center"> 
            <span>Are you sure want to delete game?</span>
        </div> 
        <div class="modal-footer py-3"> 

            <form method="POST" action="{{ url( 'admin/store/'.$store->id.'/view/deletegame') }}">
                {{ csrf_field() }} 
                <input name="gameId" class="gameId" id="gameId" type="hidden">
                <button type="submit" name="delete" class="cbtn cdanger btn-icon delete_all" value="Delete"><i class="mdi mdi-delete m-0"></i></button> 
                <button type="button" class="cbtn cprimary btn-icon" data-dismiss="modal"><i class="mdi mdi-close"></i></button>
            </form>  

        </div>
      </div>
    </div>
</div> 
<?php
    $headings = [];
    $headingsTable = [];

    $headings[0]['label']="Ordre";
    $headings[0]['name']='f_order';

    $headingsTable[0]['data']='f_order';
    $headingsTable[0]['className']='reorder sorting_disabled'; 

    $i=1;
    $targets =[];
   
    foreach($headers as $k=>$header){
     
        $headings[$i]['label'] = $header->master_field_name;
        $headings[$i]['name'] = 'field_'.$header->id;
 
 
        $headingsTable[$i]['data'] =  'field_'.$header->id;
        $headingsTable[$i]['className'] = '';
        $targets[]=$k;
        $i++;
    } 

    array_push($headingsTable, array( 'data' => 'game_id' ) );
    array_push($headings, array( 'label' => 'Actions', 'name' => 'game_id'));
    array_push($targets, count($targets) ); 

    $target_count = count($targets);
    $headingsTable = json_encode($headingsTable);
    $headings = json_encode($headings);
    $targets = json_encode($targets);
    //  echo '<pre>';
    //     print_r( $target_count  );
    //     echo '</pre>'; 
    // die;

?>

@push('before-scripts') 
     <script src="{{ asset('public/assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script> 

    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.2.5/js/dataTables.rowReorder.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.3.0/js/dataTables.select.min.js"></script> 
    <script src="{{ url('public/js/dataTables.editor.js') }}"></script> 
    <script type="text/javascript">
    var order_var = '';

        var editor; 
        var store_id = '{{ $store->id }}';
        function load_fieldsData(){
            $(".table_store_fields").hide(); 
             
            editor = new $.fn.dataTable.Editor( {
                ajax: {
                    url: '{{URL::to('/')}}/admin/store/'+{{ $store->id }}+'/view/fieldslist/ajax?storeId='+store_id,
                    method : 'GET' ,
                },
                table: '#manages_store_fields',
                fields: [ {
                        label: 'Order',
                        name: 'field_order', 
                    }, {
                        label: 'All',
                        name: 'id', 
                    }, {
                        label: 'Field Name',
                        name:  'field_personal_name'
                    }, {
                        label: 'Action',
                        name:  'created_at'
                    } 
                ]
            } );
         
            $('#manages_store_fields').on( 'click', 'tbody td.editable', function (e) {
                editor.inline( this );
            } );  
            var table = $('#manages_store_fields').DataTable( {
                "searching": false,
                "paging": false,
                dom: 'Bfrtip',
                ajax: {
                    url: '{{URL::to('/')}}/admin/store/'+{{ $store->id }}+'/view/fieldslist/ajax?storeId='+store_id,
                    method : 'GET',
                },
                <?php if($count_all_fields > 0 ){ ?> 
                "ordering": true,
                <?php }else{ ?>
                "ordering": false, 
                <?php } ?>
                columns: [  
                    { data: 'field_order', className: 'reorder' },
                    { data: 'id', render: function ( data, type, row ) {
                        console.log('Data  = '+  data   + ' / Type = ' + type + ' / Full = ' + row );
                        return '<div class="form-check tr_field"><label class="form-check-label"><input type="checkbox" name="store_field_id[]" value="'+ data +'" class="delete_store_field" onclick="delete_store_field_fn('+ data +');"><i class="input-helper"></i></label> </div>';
                    }, className: '' },
                    { data: 'field_personal_name', className: 'editable' }, 
                    { data: 'created_at', render: function ( data, type, row ) {
                        console.log('Data  = '+  data   + ' / Type = ' + type + ' / Full = ' + row );
                        return '<button type="button" class="cbtn cdanger btn-icon delete_store  datatabel_remove_field"><i class="mdi mdi-delete"></i></button>';
                    }, className: 'action_column'}
                ],
                columnDefs: [
                    { orderable: false, targets: [ 1,2,3 ] }
                ],
                rowReorder: {
                    dataSrc: 'field_order',
                    editor:  editor
                } 
            } );
            $('#manages_store_fields tbody').on( 'click', '.datatabel_remove_field', function () {
                table.row( $(this).parents('tr') ).remove().draw();
                var ID = $(this).parents('tr').attr('id');  
                var f_token = $('input[name="_token"]').val(); 
                var dataString = 'action=delete&id='+ID+'&_token='+f_token;
                $.ajax({
                    type: "GET",
                    url: "{{URL::to('/')}}/admin/store/'"+store_id+'/view/fieldslist/ajax?storeId='+store_id,
                    data: dataString,
                    cache: false,
                    success: function(html) { 
                        $("#Result").html( html );
                        table.ajax.reload();
                    }
                }); 
            } );

            editor
                .on( 'postCreate postRemove', function () {
                     
                    table.ajax.reload( null, false );
                } )
                .on( 'initCreate', function () {
                    // Enable order for create
                    editor.field( 'field_order' ).enable();
                } )
                .on( 'initEdit', function () {
                    // Disable for edit (re-ordering is performed by click and drag)
                    editor.field( 'field_order' ).disable();
                } );
                $(".loader_cls").hide();
                $(".table_store_fields").show(); 
        }

        var editor; 
        function loaddatatable_gamelisting(){
            $(".table_store_fields").hide();

            $('#games-listing').dataTable().fnDestroy();
            editor = new $.fn.dataTable.Editor( {
                 ajax:  {
                    "url":"{{URL::to('/')}}/admin/store/{{ $store->id }}/view/gamelistss",
                    method : "GET",

                 },
                 table: '#games-listing',
                 fields: <?php echo $headings; ?>
            } );

            $('#games-listing thead th').each( function (i, val) {
                var title = $(this).text();
                var slugtext = title.toLowerCase();
                slugtext = slugtext.replace(" ", "-"); 
                if(i ==2 || i == 3 || i==4 ){ 
                    $(this).prepend( '<input type="text" placeholder="Search '+title+'" name="'+slugtext+'" class="form-control mb-4"/>' );
                }
            } );

            var table = $('#games-listing').DataTable( {
                "bInfo" : false,
                 dom: 'Bfrtip',
                 // "searching": false,
                 "paging": false,
                 ajax:  {
                    "url":"{{URL::to('/')}}/admin/store/{{ $store->id }}/view/gamelistss",
                    method : "GET",
                 },

                 columns:  <?php echo $headingsTable; ?>,
                 columnDefs: [
                    { targets: 0 ,"width": "2%", orderable: false }, 
                    { targets: 1,"width": "15%" }, 
                    { targets: 2,"width": "15%" }, 
                    { targets: 3,"width": "15%" }, 
                    { targets: 4,"width": "15%" }, 
                    { targets: 5,"width": "10%" }, 
                    { orderable: false, targets: <?php echo $targets; ?> },  
                    { targets : <?php echo $target_count   ; ?>, "width": "10%",
                        "render": function(data, type, row) { 
                            var jsonData = JSON.stringify(row);
                            var jsonstringify = JSON.parse(jsonData);
                            var store_ids = jsonstringify.assigned_stores ;  

                            var mult_stores = [];
                            $.each(store_ids, function(i, val){   
                                mult_stores.push(val.store_id);
                            });  
                            return '<button type="button" class="cbtn cinfo btn-icon text-white popup_store" data-stores="['+ mult_stores  +']" data-toggle="modal" data-target="#gamestore_modal" data-id="'+ data +'" ><i class="mdi mdi-send"></i> </button>';
                        }, 

                    }
                 ], 
                rowReorder: {
                     dataSrc: 'f_order',
                     editor:  editor,

                 },
                 "serverSide":true,
              });
                
                table.columns().every( function () {
                    var that = this; 
                    $( 'input', this.header() ).on( 'keyup change', function () {
                        if ( that.search() !== this.value ) {
                            that
                                .search( this.value )
                                .draw();
                        }
                    });
                });

                editor
                .on( 'postCreate postRemove', function () {
     
                    table.ajax.reload( null, false );
                } )
                .on( 'initCreate', function () {
                    editor.field( 'f_order' ).enable();
                } )

                .on( 'initEdit', function (e, node, data, items, type) {
                     //console.log(e);
                     // Disable for edit (re-ordering is performed by click and drag)
                     editor.field( 'f_order' ).disable();
                } );

                table.on( 'row-reorder', function ( e, diff, edit ) {
                    setTimeout(function(){
                        //alert("hell");
                        reorder(table);
                       return false;
            }, 500);
         
            } );

            $(".loader_cls").hide();
            $(".table_store_fields").show(); 

            return table; 
        }
        function redirectView(id){
            window.location.href="{{URL::to('/')}}/admin/game/"+ id +"/view/";
        }
        $(document).ready(function($) {
            
            setTimeout(function(){ 
                load_fieldsData();
            }, 2000); 

            $.ajax({
                "url": "{{URL::to('/')}}/admin/store/{{ $store->id }}/view/gamelistss",
                "success": function(json) {
                    var tableHeaders = '<table id="games-listing" class="table"  style="width:100%;"><thead>';
                    $.each(json.columns, function(i, val){
                        //if(i <= 10){
                            tableHeaders += "<th>" + val + "</th>";
                       // }
                    });
                    tableHeaders +='<th data-orderable="false">Action</th></thead></table>';
                   $("#tableDiv").empty();
                   $("#tableDiv").html(tableHeaders); 
                },
                "dataType": "json"
            });

            setTimeout(function(){
               loaddatatable_gamelisting(); 
               
            }, 2000); 
            

        } ); 

        function reorder(table){
            loaddatatable();
            $('#games-listing th:first').removeClass('sorting_asc');
        } 
    </script>
@endpush 
@endsection
