@extends('backend.layouts.app')
@push('before-styles')  
    <link rel="stylesheet" href="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"> 
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.2.5/css/rowReorder.dataTables.min.css">
    <!--<style type="text/css"> .new-store-form { display: none; }</style> -->
@endpush 
@section('title',  'Manage Games'. ' | '. app_name()) 
@section('description', 'Manage Games')
@section('keywords', 'Manage Games')
@section('content')


<div class="col-12 grid-margin stretch-card p-0">
    <div class="card">
        <div class="card-body py-3"> 
          
            <div class="row">
                <div class="col-sm-5 justify-content-center align-self-center">
                    <h4 class="card-title mb-0">Add New Game</h4>
                </div><!--col--> 
                <div class="col-sm-7 pull-right header">
                    <div class="btn-toolbar float-right" role="toolbar" aria-label="@lang('labels.general.toolbar_btn_groups')">
                        <button type="button" class="cbtn cprimary btn-icon"><i class="mdi mdi-plus"></i></span></button> 
                    </div> 
                </div><!--col-->  
                <div class="col-sm-12 new-store-form" >
                    @include('backend.game.create')
                </div>
            </div><!--row-->     
        </div><!--card-body-->
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0-->


<div class="col-12 grid-margin stretch-card p-0 store_listing">
    <div class="card">
        <div class="card-body"> 

            <div class="row">
                <div class="col-sm-4">
                    <h4 class="card-title mb-4">Game List</h4>
                </div> 
            </div>
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

                <div class="col fields_list">
                    <div id="tableDiv" class="table-responsive table_store_fields" style="display:none;"></div>
                    
                </div><!--col-->
            </div><!--row--> 
        </div><!--card-body-->
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0-->

<div class="modal fade" id="gamestore_modal" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Retailer List<span class="modal_gameName"></span></h4>
            <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>         
        </div>
        <div class="modal-body py-0"> 

            <div class="row">
                <div class="col-md-12 store_assign_error d-none"  > 
                    <div class="alert alert-danger my-2">
                         
                        <span>It's wrong assignment, please assign correct store.</span>
                    </div> 
                </div><!--col-md-12-->
            </div><!--row--> 


            <div class="table-responsive">
                <form action="{{ route('admin.get_gameslisting') }}" method="POST" >
                    {{ csrf_field() }} 
                    <input type="hidden" name="_method" value="GET"> 
                    <input type="hidden" name="game_id" class="gameId">
                    <input type="hidden" name="store_ids_arr" id="store_ids_arr"> 
                    <table class="table" id="assign_storeGames">
                        <thead>
                        <tr>
                            <?php if($count_stores > 0 ){  ?>
                            <th width="3%"> 
                                <div class="form-check my-0">
                                    <label class="form-check-label"> 
                                        <input type="checkbox" name="store_ids" value="" class="form-check-input assign_store_game" >   
                                    <i class="input-helper"></i></label>
                                </div>
                            </th>
                            <?php } ?>
                            <th>Retailer Logo</th>
                            <th>Retailer Name</th> 
                             
                            <?php if($count_stores > 0 ){  ?>
                             <th data-orderable="false" class="nosort text-right pr-1">  
                               <button type="button" class="cbtn cinfo btn-icon text-white store_game_btn"><i class="mdi mdi-send"></i> </button>
                            </th>
                            <?php } ?> 
                        </tr>
                        </thead>
                        <tbody> 
                            <?php if (count($stores) > 0 ) {  $i = 1; 
                                foreach($stores as $store) {    ?> 
                                    <tr id="iteam{{ $store->id }}" class="game_tr"> 
                                        <td width="5%">
                                            <div class="form-check tr_field">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="store_id" value="{{$store->id}}" class="form-check-input single_assign" ><i class="input-helper"></i></label>
                                            </div>
                                        </td> 
                                        <td width='20%'> 
                                            <a href="{{ url('/') }}/admin/store/{{ $store->id }}/view"><img class="img-responsive logo_img" src="{{ $store->logo }}" alt="{{$store->logo}}" ></a>
                                        </td>
                                        <td width="20%">{{ $store->store_name }}</td>  
                                        <td width="20%" class="p-1 text-right">  
                                            <button type="button" class="cbtn cinfo btn-icon text-white single_store_submit  " value="{{ $store->id }}"><i class="mdi mdi-send"></i> </button> 
                                        </td>
                                    </tr>
                                <?php $i++;  }       
                                // die;
                            } else { ?>
                                <tr>
                                    <td colspan="5" align="center">No data found!</td> 
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table> 

                </form>
            </div>
        </div>
        <div class="modal-footer py-3"> 
            <button class="cbtn cdanger btn-icon" data-dismiss="modal" ><i class="mdi mdi-close"></i></button>  
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
    $headingsTable[0]['className']='reorder'; 

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
 
     
        var editor;

        function loaddatatable(){
            $(".table_store_fields").hide();

            $('#games-listing').dataTable().fnDestroy();
            editor = new $.fn.dataTable.Editor( {
                 ajax:  {
                    "url":"{{URL::to('/')}}/admin/game/index/gameslist/ajax",
                    method : "GET",

                 },
                 table: '#games-listing',
                 fields: <?php echo $headings; ?>
            } );

            var table = $('#games-listing').DataTable( {
				"bInfo" : false,
                 dom: 'Bfrtip',
				 "searching": false,
                 "paging": false,
                 ajax:  {
                    "url":"{{URL::to('/')}}/admin/game/index/gameslist/ajax",
                    method : "GET",
                 }, 
                 columns:  <?php echo $headingsTable; ?>,
                 columnDefs: [ 
                    { targets: 0 ,"width": "1%" }, 
                    { targets: 1 ,"width": "13%" }, 
                    { targets: 2 ,"width": "13%" }, 
                    { targets: 3 ,"width": "13%" }, 
                    { targets: 4 ,"width": "10%" }, 
                    { targets: 5 ,"width": "10%" }, 
                    { targets: 6 ,"width": "7%" }, 
                    { targets: 7 ,"width": "15%" }, 
                    { targets: 8,"width": "20%" },  
                    { orderable: false, targets: <?php echo $targets; ?> },
                    { targets : <?php echo $target_count   ; ?>,  
                        "render": function(data, type, row) { 
                            var jsonData = JSON.stringify(row);
                            var jsonstringify = JSON.parse(jsonData);
                            var store_ids = jsonstringify.assigned_stores ;  

                            var mult_stores = [];
                            $.each(store_ids, function(i, val){   
                                mult_stores.push(val.store_id);
                            });  
                            return '<button class="cbtn cwarning btn-icon wide" type="button" name="store_submit" id="store_submit" ><i class="mdi mdi-download"> XLS</i></button> <button type="button" class="cbtn csuccess btn-icon" data-id="'+ data +'" onclick="redirectView('+data+');"><i class="mdi mdi-information-variant"></i></button><button type="button" class="cbtn cinfo btn-icon text-white popup_store" data-stores="['+ mult_stores  +']" data-toggle="modal" data-target="#gamestore_modal" data-id="'+ data +'"><i class="mdi mdi-send"></i> </button>';
                        },  
                    }
                 ],
                 rowReorder: {
                     dataSrc: 'f_order',
                     editor:  editor,

                 },
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
            window.location.href="{{URL::to('/')}}/admin/game/"+id+"/view";
        }
        $(document).ready(function() {
            $.ajax({
                    "url": "{{URL::to('/')}}/admin/game/index/gameslist/ajax",
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
               loaddatatable(); 
               
            }, 2000); 
            

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
        function reorder(table){
            loaddatatable();
            $('#games-listing th:first').removeClass('sorting_asc');
        } 

    </script>
@endpush 
@endsection
