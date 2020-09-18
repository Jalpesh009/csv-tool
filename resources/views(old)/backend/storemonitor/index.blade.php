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

<div class="col-12 grid-margin stretch-card p-0 store_listing">
    <div class="card">
        <div class="card-body"> 
            <div class="row">
                <div class="col-sm-4">
                    <h4 class="card-title mb-4">Game List</h4>
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

                <div class="col fields_list">
                    <div id="tableDiv" class="table-responsive table_store_fields" style="display:none;"></div>
                    
                </div><!--col-->
            </div><!--row--> 
        </div><!--card-body-->
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0-->



<div class="modal fade" id="storemonitor_modal" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Retailer List</h4>
            <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>         
        </div>
        <div class="modal-body py-0"> 
            <div class="table-responsive">
               
            </div>
        </div>
        <div class="modal-footer py-3"> 
            <button class="cbtn cdanger btn-icon delete_modal_field" data-dismiss="modal" type="submit"><i class="mdi mdi-delete"></i></button> 
            <button type="button" class="cbtn cprimary btn-icon" data-dismiss="modal"><i class="mdi mdi-close"></i></button>
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
    //     print_r( $targets  );
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
                    "url":"{{URL::to('/')}}/admin/storemonitor/game_list/ajaxgame",
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
                    "url":"{{URL::to('/')}}/admin/storemonitor/game_list/ajaxgame",
                    method : "GET",
                 },

                 columns:  <?php echo $headingsTable; ?>,
                 columnDefs: [
                    { targets: 0,"width": "2%" },
                    /*{ targets: 1,"width": "12%" }, 
                    { targets: 3,"width": "15%" }, */
                    { orderable: false, targets: <?php echo $targets; ?> },
                    
                    { targets : <?php echo $target_count; ?>,  
                        "render": function(data, type, row) { 
                           console.log('Data  = '+  data      );
                            var url = '/view';
                            return ' <button type="button" class="cbtn csuccess btn-icon" data-id="'+ data +'" data-toggle="modal" data-target="#storemonitor_modal" ><i class="mdi mdi-information-variant"></i></button>';
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
            $('#games-listing th.reorder').removeClass('sorting_asc');
            return table; 
        }
      
        $(document).ready(function() {
             $('#games-listing th.reorder').removeClass('sorting_asc');
            $.ajax({
                    "url": "{{URL::to('/')}}/admin/storemonitor/game_list/ajaxgame",
                    "success": function(json) {
                        var tableHeaders = '<table id="games-listing" class="table"  style="width:100%;"><thead>';
                        $.each(json.columns, function(i, val){
                            //if(i <= 10){
                                tableHeaders += "<th>" + val + "</th>";
                           // }
                        });
                        tableHeaders +='<th data-orderable="false">Details</th></thead></table>';
                       $("#tableDiv").empty();
                       $("#tableDiv").html(tableHeaders); 
                    },
                    "dataType": "json"
                });

            setTimeout(function(){
                loaddatatable(); 
                $('#games-listing th.reorder').removeClass('sorting_asc');
            }, 2000);  
             
        } );
        function reorder(table){
            loaddatatable();
            $('#games-listing th.reorder').removeClass('sorting_asc');
        } 

    </script>
@endpush 
@endsection
