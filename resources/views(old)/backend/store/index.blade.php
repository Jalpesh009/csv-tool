@extends('backend.layouts.app')
@push('before-styles')  
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"> 
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.2.5/css/rowReorder.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <!--<style type="text/css"> .new-store-form { display: none; } </style>--> 
@endpush 
@section('title',  'Manage Stores'. ' | '. app_name()) 
@section('description', 'Manage Stores')
@section('keywords', 'Manage Stores')
@section('content')
 
<div class="col-12 grid-margin stretch-card p-0 van">
    <div class="card">
        <div class="card-body py-3">  
            <div class="row">
                <div class="col-sm-5 justify-content-center align-self-center">
                    <h4 class="card-title mb-0">Add a New Matrix</h4>
                </div><!--col--> 
                <div class="col-sm-7 pull-right header">
                        <div class="btn-toolbar float-right" role="toolbar" aria-label="@lang('labels.general.toolbar_btn_groups')">
                            <button type="button" class="cbtn cprimary btn-icon"><i class="mdi mdi-plus"></i></span></button> 
                        </div> 
                    </div><!--col-->   
                <div class="col-sm-12 new-store-form"> 
                    @include('backend.store.create')
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
                    <h4 class="card-title mb-0">Retailer List</h4>
                </div> 
            </div>
            <div class="row">

                <div class="col fields_list">
                    <div class="table-responsive">
                        <table class="table" id="retailer-listing">
                            <thead>
                            <tr>  
                                <th width='10%'>Logo</th>
                                <th width='15%'>Retailer Name</th>
                                <th width='15%'>City</th> 
                                <th width='23%'>Contact Email</th> 
                                <th width='15%'>Contact Number</th> 
                                <th width='20%' class="text-center">@lang('labels.general.actions')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($stores as $store)
                                <tr>  
                                    <td width='8%'> 
                                        <a href="{{ url('/') }}/admin/store/{{ $store->id }}/view"><img class="img-responsive logo_img" src="{{ $store->logo }}" alt="{{$store->logo}}" ></a>
                                    </td>
                                    <td width='15%'>
                                        <a href="{{ url('/') }}/admin/store/{{ $store->id }}/view">{{ $store->store_name }}</a>
                                    </td> 
                                    <td width='15%'>{{ $store->city }}</td>  
                                    <td width='20%'>{{ $store->contact_email }}</td> 
                                    <td width='15%'>{{ $store->contact_number }}</td>
                                    <td width='25%' class=" text-right ">  
                                        <button type="button"   class="cbtn cprimary btn-icon text-white load_game_data" data-toggle="modal" data-storeid="{{ $store->id }}" data-storename="{{ $store->store_name }}" data-target="#assigned_games_modal" ><i class="mdi mdi-eye"></i></button> 
										<button type="button" class="cbtn cwarning btn-icon csv_download text-white wide" data-storeid="{{ $store->id }}"><i class="mdi mdi-download"></i> XLS </button> 
                                        
                                        <button onclick="location.href='store/{{ $store->id }}/edit'" type="button" class="cbtn csuccess btn-icon text-white"><i class="mdi mdi-lead-pencil"></i></button> 
                                        <button type="button" class="cbtn cdanger btn-icon delete_store"  data-id="{{ $store->id }}" data-name="{{ $store->store_name }}" data-toggle="modal" data-target="#delete_store_modal"><i class="mdi mdi-delete"></i></button> 
                                        
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div><!--col-->
            </div><!--row--> 
        </div><!--card-body-->
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0-->

<div class="modal fade" id="delete_store_modal" role="dialog">
    <div class="modal-dialog modal-md w-50">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Confirm Delete Store</h4>
            <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>         
        </div>
        <div class="modal-body text-center"> 
            <span>Are you sure want to delete <span class="modal_storeName" style="color:#e94437;"></span>?</span>
        </div>
        <div class="modal-footer py-3"> 
            <button class="cbtn cdanger edit_field delete_modal_store"   data-dismiss="modal" type="submit"><i class="mdi mdi-delete"></i></button> 
            <button type="button" class="cbtn cprimary edit_field" data-dismiss="modal"><i class="mdi mdi-close"></i></button>
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="assigned_games_modal" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title store_name"></h4>
            <div class="text-right d-flex">
                <form  class="csv_download_form" method="POST" >
                    {{ csrf_field() }} 
                    <input type="hidden" name="_method" value="POST">
                <!-- <input name="gameId" class="gameId" id="gameId" type="hidden"> -->
                <button type="submit" name="download_csv" value="csv_download" class="cbtn cwarning btn-icon csv_store text-white wide"><i class="mdi mdi-download"></i> XLS </button>
                </form>
                <button type="button" class="cbtn cdanger btn-icon " data-dismiss="modal">&times;</button> 
            </div>        
        </div>
        <div class="modal-body py-0">   
            <div class="row">

                <div class="col fields_list">
                    <div id="tableDiv123" class="table-responsive table_store_fields"></div> 
                </div><!--col-->
            </div><!--row-->  
        </div> 
      </div>
    </div>
</div>

<form  class="csv_download_btnForm" method="POST" style="display:none">
    {{ csrf_field() }} 
    <input type="hidden" name="_method" value="POST">
    <!-- <input name="gameId" class="gameId" id="gameId" type="hidden"> -->
    <button type="submit" name="download_csv" id="download_csv" value="csv_download" class="cbtn cwarning btn-icon csv_store text-white wide"><i class="mdi mdi-download"></i> XLS </button>
</form>
<?php
    $headings = [];
    $headingsTable = [];  
    $targets =[];
    $i=0;
    foreach($headers as $k=>$header){ 
        $headings[$i]['label'] = $header->master_field_name;
        $headings[$i]['name'] = 'field_'.$header->id;  
        $headingsTable[$i]['data'] =  'field_'.$header->id;
        $headingsTable[$i]['className'] = '';
        $targets[]=$k;
        $i++;
    }   
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
     
    <script src="{{ asset('public/assets/js/data-table.js') }}"></script>  

    <script src="{{ asset('/public/assets/js/file-upload.js') }}"></script> 
    <script src="{{ asset('/public/assets/js/tabs.js') }}"></script> 

    <script src="{{ asset('public/assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script> 
     <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script> 
    <script src="https://cdn.datatables.net/select/1.3.0/js/dataTables.select.min.js"></script>
    <script src="{{ url('public/js/dataTables.editor.js') }}"></script>  
    <script type="text/javascript"> 
        var editor; 
        function loaddatatable( id ){ 
            $('#gamesListing').dataTable().fnDestroy();
            editor = new $.fn.dataTable.Editor( {
                 ajax:  {
                    "url":"{{URL::to('/')}}/admin/store/"+id+"/games",
                    method : "GET", 
                 },
                 table: '#gamesListing',
                 fields: <?php echo $headings; ?>
            } );  
            var table = $('#gamesListing').DataTable( {
                "autoWidth": true,
                "bSort" : false, 
                "bInfo" : false,
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                // fixedColumns: true,
                ajax:  {
                    "url":"{{URL::to('/')}}/admin/store/"+id+"/games",
                    method : "GET",
                }, 
                columns:  <?php echo $headingsTable; ?>, 
                "language": { 
                    emptyTable : "No data found!"
                },
                
            });  
            editor
            .on( 'postCreate postRemove', function () { 
                table.ajax.reload( null, false );
            } )
            .on( 'initCreate', function () {
                editor.field( 'f_order' ).disable();
            } ) 
            .on( 'initEdit', function (e, node, data, items, type) { 
                editor.field( 'f_order' ).disable();
            } );  
            return table; 
        }  
        jQuery(document).ready(function($){            
            $(".load_game_data").click(function () {
                var storeid = $(this).data('storeid');
                var storename = $(this).data('storename');
                $('.csv_download_form').attr('action', "store/"+ storeid +"/games/downloacsv");
                $('.store_name').text(storename);
                $.ajax({
                    "url": "{{URL::to('/')}}/admin/store/"+ storeid +"/games",
                    "success": function(json) {
                        var tableHeaders = '<table id="gamesListing" class="table table-bordered"  style="width:100%;"><thead>';
                        var tableName = ''; 
                        $.each(json.columns , function(i, val){ 
                            if(val.master_field_required == 'yes'){
                                tableHeaders += "<th class='bg-orange'>Requis</th>";
                                tableName += "<td class='bg-orange'>"+val.master_field_name+"</td>";
                            }else{
                                tableHeaders += "<th class='bg-green'>Facultatif</th>"; 
                                tableName += "<td class='bg-green'>"+val.master_field_name+"</td>";
                            }
                            
                        });
                        tableHeaders += "<tr>"; 
                        tableHeaders += tableName;
                        tableHeaders += "</tr>";
                        tableHeaders +='</thead></table>';
                        $("#tableDiv123").html(tableHeaders); 
                    },
                    "dataType": "json"
                }); 
                setTimeout(function(){ 
                    loaddatatable(storeid); 
                    $('#gamesListing').dataTable().fnDestroy();
                }, 500);  
            });

            $(".csv_download").click(function () {
                var storeid = $(this).data('storeid'); 
                $('.csv_download_btnForm').attr('action', "store/"+ storeid +"/games/downloacsv");
                $('#download_csv').trigger('click'); 
            }); 
            $(".header").click(function () {

                $header = $(this);
                //getting the next element
                $content = $header.next();
                //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
                $content.slideToggle(500, function () {
                    //execute this after slideToggle is done
                    //change text of header based on visibility of content div
                    $header.html(function () {
                        //change text based on condition
                        return $content.is(":visible") ? '<div class="btn-toolbar float-right" role="toolbar" aria-label="@lang("labels.general.toolbar_btn_groups")"><button type="button" class="cbtn csuccess btn-icon"><i class="mdi mdi-content-save"></i></span></button> <button type="button" class="cbtn cdanger btn-icon cancel_btn mr-0"><i class="mdi mdi-close"></i></span></button></div>' : '<div class="btn-toolbar float-right" role="toolbar" aria-label="@lang("labels.general.toolbar_btn_groups")"><button type="button" class="cbtn cprimary btn-icon mr-0"><i class="mdi mdi-plus"></i></span></button> </div>';
                    });
                });

            }); 
            $(".new-store-form .alert").is(":visible") ?  $(".new-store-form").show() : $(".new-store-form").hide();
            $(".new-store-form").is(":visible") ? $(".header").html('<div class="btn-toolbar float-right" role="toolbar" aria-label="@lang("labels.general.toolbar_btn_groups")"><button type="button" class="cbtn csuccess btn-icon"><i class="mdi mdi-content-save"></i></span></button><button type="button" class="cbtn cdanger btn-icon cancel_btn mr-0"><i class="mdi mdi-close"></i></span></button></div>') : $(".header").html('<div class="btn-toolbar float-right" role="toolbar" aria-label="@lang("labels.general.toolbar_btn_groups")"><button type="button" class="cbtn cprimary btn-icon mr-0"><i class="mdi mdi-plus"></i></span></button> </div>') ;


        });

    </script>
@endpush 
@endsection
