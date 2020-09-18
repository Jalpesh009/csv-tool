@extends('backend.layouts.app')
@push('before-styles')  
    <!-- <link rel="stylesheet" href="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}"> -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"> 
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.2.5/css/rowReorder.dataTables.min.css">
     
@endpush 
@section('title',  'View Game'. ' | '. app_name()) 
@section('description', 'View Game')
@section('keywords', 'View Game')
@section('content')

<div class="col-12 grid-margin stretch-card p-0 "> 
    <div class="card">
        <div class="card-body">     
            <div class="row"> 
                <div class="col-md-3 col-lg-3 col-xl-2 game-thumbnail">
                <?php 
                // echo '<pre>';
                // print_r($game_fields); die;

               $islogo = false;
                foreach($game_fields as $game_field) {
                   

                    if($game_field['master_fields']['master_field_slug'] == 'game_logo' && isset($game_field['field_value']) ) { 
                        $islogo = true;
                        echo str_replace('game_logo_img', 'img-fluid', $game_field['field_value']);
                    }  
                    
                }
                if($islogo == false){ ?> 
                    <img src="<?php echo url('/public/uploads/default-game.jpg'); ?>" class="img-fluid">
                <?php 
                }
                 ?>

                </div>  
                <div class="col-md-6 col-lg-6 col-xl-8"> 
                    <?php 
                     foreach($game_fields as $game_field_title) {
                     if($game_field_title['master_fields']['master_field_slug'] == 'game_name') { ?>
                            
                        <div class="title-action d-flex justify-content-between">
                            <h2 class="game-title mt-3"><?php echo $game_field_title['field_value']; ?></h2>.
                            
                        </div>
                    <?php
                    }  } ?>   


                    <?php foreach($game_fields as $game_field_desc) {
                    if($game_field_desc['master_fields']['master_field_slug'] == 'game_description'){ ?>
                            <p class="game-discription"> <?php echo $game_field_desc['field_value']; ?> </p>
                    <?php }  } ?>

                    <?php foreach($game_fields as $game_field_price) {
                    if($game_field_price['master_fields']['master_field_slug'] == 'price') { ?>
                        <button type="button" class="gredient-btn mt-3"> 
                            <?php 
                            if (is_numeric($game_field_price['field_value'])){
                                echo '€ '. number_format($game_field_price['field_value'],2) ;  
                            }else{
                                echo $game_field_price['field_value'];
                            } ?>
                        </button>
                    <?php }  } ?> 
                </div>  

                <div class="col-md-3 col-lg-3 col-xl-2">
                    <div class="action-btn text-right">
                        <button type="button" class="cbtn csuccess btn-icon" onclick="location.href='edit'" ><i class="mdi mdi-lead-pencil"></i></button>
                        <button type="button" class="cbtn cdanger btn-icon" data-id="16" data-name="Price" data-toggle="modal" data-target="#delete_field_modal" value="Delete"><i class="mdi mdi-delete"></i></button>
                        <button type="button" class="cbtn cprimary btn-icon" onclick="window.location.href = '{{ route('admin.game.index') }}'"><i class="mdi mdi-close"></i></button> 
                    </div>
                </div>
            </div><!--row-->   
        </div><!--card-body-->
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0--> 

<div class="col-12 grid-margin stretch-card p-0">
    <div class="card">
        <div class="card-body">   
            <div class="row">
                <div class="col-md-12 mx-auto game-content-details">
                    <ul class="nav nav-pills nav-pills-custom text-center justify-content-center" id="pills-tab-custom" role="tablist">
                        <li class=" px-4 text-center">
                          <a class="active" id="pills-overview-tab-custom" data-toggle="pill" href="#game-overview" role="tab" aria-controls="pills-home" aria-selected="true">
                            Overview
                          </a>
                        </li>
                        <li class="px-4 text-center">
                          <a class="" id="pills-game-info-tab-custom" data-toggle="pill" href="#game-info" role="tab" aria-controls="pills-profile" aria-selected="false">
                            Game Information
                          </a>
                        </li> 
                    </ul>

                    <div class="tab-content tab-content-custom-pill pr-0" id="pills-tabContent-custom">
                        <div class="tab-pane fade active show" id="game-overview" role="tabpanel" aria-labelledby="pills-overview-tab-custom">
                            <div class="col-sm-12 game-maindis mb-4">
                                <?php foreach($game_fields as $game_field) {  
                                    if($game_field['master_fields']['master_field_slug'] == 'game_description'){ ?>
                                    <h3 class="content-title mb-3">Description</h3> 
                                    <p> <?php echo $game_field['field_value']; ?> </p>
                                <?php }  
                            } ?>
                            </div>
                            <div class="col-sm-12 mb-4 game-screenshots1">
                                
                                <?php 
                                $i = 0;
                                foreach($game_fields as $game_field) { 
                                    if($game_field['master_fields']['master_field_type'] == 'gallery') { 
                                        if($i == 0){ echo '<h3 class="content-title mb-3">Images</h3> ';} ?>
                                        
                                        <div class="galleryimages row"> 
                                        <?php $imgs = json_decode( $game_field['field_value']);
                                        if($imgs){
                                            foreach($imgs  as $img) {  
                                                echo '<div class="col-lg-3 col-md-6 col-xs-12 store-logo-upload-edit gallery_img_1"><img src="'. $img .'" class="game_gallery_img"> </div>';
                                            } 
                                        }  ?> 
                                        </div>
                                <?php } 
                                $i++;
                                } ?>   
                            </div> 
                        </div> 
                        <div class="tab-pane fade " id="game-info" role="tabpanel" aria-labelledby="pills-game-info-tab-custom">
                            <div class="col-sm-12">
                                <h4  class="card-title text-dark">All Information</h4>
                                <h6 class="mb-4">Product Information</h6> 
                                <ul class="row p-0 details-list">
                                <?php foreach($game_fields as $game_field_val) { 
                                            if(isset($game_field_val['field_value'])){
                                                if($game_field_val['master_fields']['master_field_slug'] != 'game_logo' && $game_field_val['master_fields']['master_field_slug'] != 'game_name' && $game_field_val['master_fields']['master_field_slug'] != 'game_description' && $game_field_val['master_fields']['master_field_slug'] != 'price' && $game_field_val['master_fields']['master_field_type'] != 'gallery') { ?>
                                                    <li class="list list-group-item">
                                                        <label  class="col-md-6 font-weight-bold m-0"><?php echo $game_field_val['master_fields']['master_field_name']; ?></label >
                                                        <span class="col-md-6"><?php echo $game_field_val['field_value']; ?></span>
                                                    </li> 
                                        <?php } 
                                        }  
                                    }  ?>
                                </ul>
                            </div> 
                        </div> 
                    </div>  
                </div> 
            </div>             
        </div><!--card-body-->
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0-->  

<div class="modal fade" id="delete_field_modal" role="dialog">
    <div class="modal-dialog modal-md w-50">
      <div class="modal-content">
        <div class="modal-header py-3">
            <h4 class="modal-title">Confirm Delete Game</h4>
            <button type="button" class="close m-0 p-0" data-dismiss="modal">&times;</button>         
        </div>
        <div class="modal-body text-center"> 
            <span>Are you sure want to delete <span class="modal_storeName" style="color:#e94437;">
			<?php foreach($game_fields as $game_field) {
                    if($game_field['master_fields']['master_field_slug'] == 'game_name') { ?>
                            
                           <?php echo $game_field['field_value']; ?>
                    <?php }  } ?>
			</span>?</span>
        </div>
        <div class="modal-footer py-3">
            <form action="{{ url('admin/game/' . $id. '/view/' ) }}" method="POST"> 
                <input type="hidden" name="_method" value="GET"> 
    			<input type="hidden" name="game_id" value="{{$game_id}}"/>

                <button class="cbtn cdanger"  type="submit"><i class="mdi mdi-delete"></i></button> 
                <button type="button" class="cbtn cprimary edit_field" data-dismiss="modal"><i class="mdi mdi-close"></i></button>
            </form>
        </div>
      </div>
    </div>
</div>
@push('before-scripts') 
    <style type="text/css">
        .details-list .list-group-item {
            width: 48%;
            float: left;
            margin: 0 1%;
            border: 0;
            border-top: 1px solid rgba(0, 0, 0, .125);
        }
        
    </style>
@endpush 
@endsection
