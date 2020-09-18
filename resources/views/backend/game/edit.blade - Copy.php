@extends('backend.layouts.app')
@push('before-styles')
    <link rel="stylesheet" href="{{ asset('public/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.2.5/css/rowReorder.dataTables.min.css">

@endpush
@section('title',  'Manage Games'. ' | '. app_name())
@section('description', 'Manage Games')
@section('keywords', 'Manage Games')
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
        <div class="card-body py-3">

            <div class="row">

                <div class="col-12">
                    <form class="forms-sample" id="eventform" method="POST" action="{{ route('admin.game.edit', $id ) }}"  enctype="multipart/form-data">

                        <div class="row">
                            <div class="col-sm-10  justify-content-center align-self-center">
                                <h4 class="card-title  ">Edit Game Information</h4>
                            </div><!--col-->
                            <div class="col-sm-2">
                                <div class="form-group text-right">
                                    <button class="cbtn csuccess btn-icon" type="submit" name="edit_game_submit" id="store_submit" value="SAVE"><i class="mdi mdi-content-save"></i></button>
                                    <button type="button" class="cbtn cdanger btn-icon" onclick="window.location.href = '{{ route('admin.game.index') }}'"><i class="mdi mdi-close"></i></button>
                                </div><!--form-group-->
                            </div><!--col-md-12-->
                        </div><!--row-->

                        {{ csrf_field() }}
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="gameId" value="{{ $id }}">
                        <div class="row">
                            <?php
                             // echo '<pre>';
                            // print_r($game_fields); die;
                            if($game_fields){
                                foreach($game_fields as $game_field) {
                                    // if($game_field['master_fields']['master_field_type'] != 'readonly'){
                                    if($game_field['master_fields']['master_field_required'] == 'yes'){
                                        $req = 'required';
                                        $star = '<span class="text-danger">*</span>';
                                    } else {
                                        $req = '';
                                        $star = '';
                                    }

                                    if($game_field['master_fields']['master_field_type'] == 'image'){

                                    if( $game_field['field_value'] != '' ){$req = '';}
                                     ?>

                                        <div class="col-md-6">
                                            <input type="hidden" name="imagesids[]" class="form-control" value="<?php echo $game_field['id']; ?>">
                                            <div class="form-group row edit_image">
                                                <div class="col-md-8">
                                                <label for="store_logo_<?php echo $game_field['id']; ?>"> {{ $game_field['master_fields']['master_field_name'] }} <?php echo $star; ?> </label>
                                                    <input type="file" name="photos_<?php echo $game_field['id']; ?>" id="store_logo_<?php echo $game_field['id']; ?>" class="file-upload-default store_logo" {{ $req }}>
                                                    <div class="input-group col-xs-12">
                                                        <input type="text" class="form-control file-upload-info" disabled placeholder="Upload Image">
                                                        <span class="input-group-append">
                                                        <button class="file-upload-browse btn btn-success" type="button">Choose Image</button>
                                                        </span>
                                                    </div>
                                                    <?php
                                                    if(!empty($game_field['field_value'])){
                                                        // $img =  str_replace('<img src=', '', $game_field['field_value']);
                                                        // $img1 =  str_replace('"http://matrices.pixodeo.fr/public/uploads/', '', $img );
                                                        // $img2 =  str_replace('" class="game_logo_img">', '', $img1 );
                                                        $imgpath = $game_field['field_value'];
                                                        $imgname = basename($imgpath);
                                                        $img2 =  str_replace('" class="game_logo_img">', '', $imgname );

                                                        ?>
                                                        <input type="hidden" name="edit_image" value="<?php echo $img2; ?>">
                                                    <?php } ?>

                                                </div>
                                                <div class="col-md-4 col-lg-3 store-logo-upload-edit">
                                                    <label class="invisible"> {{ $game_field['master_fields']['master_field_name'] }} <?php echo $star; ?> </label>
                                                    <?php if( $game_field['field_value'] != '' ){ ?>
                                                        <a href="javascript:void(0);" class="delete_game_image" ><div class="badge badge-pill badge-warning "><i class="mdi mdi-close"></i></div> </a>
                                                        <?php echo $game_field['field_value']; ?>

                                                    <?php } else { ?>
                                                    <a href="javascript:void(0);" class="delete_game_image" ><div class="badge badge-pill badge-warning "><i class="mdi mdi-close"></i></div> </a>
                                                        <img src="<?php echo  url('/public/img/backend/placeholder/default-game.jpg'); ?>" class="game_logo_img">
                                                    <?php }?>
                                                </div><!--col-md-6-->

                                            </div>
                                        </div>

                                    <?php } else if($game_field['master_fields']['master_field_type'] != 'gallery'){

                                            $inputtype = 'text';
                                            $inputclass = '';
                                            if($game_field['master_fields']['master_field_type'] == 'readonly' ){
                                                 $inputtype = 'hidden';
                                                 $inputclass = 'd-none';
                                            }
                                            if($game_field['master_fields']['master_field_in_form']== 'no'){// add new field for in form 04-Nov
                                                $inputclass = 'd-none';
                                            }
                                            if($game_field['master_fields']['master_field_slug'] == 'game_description'){ ?>
                                                <div class="col-md-6 {{ $inputclass }}">
                                                    <div class="form-group row">
                                                        <div class="col-md-12">

                                                            <label for="edit_field_name"> {{ $game_field['master_fields']['master_field_name'] }} <?php echo $star; ?> </label>
                                                            <textarea class="form-control"  name="input[game][{{ $game_field['id']}}]" id="fieldid_{{ $game_field['id'] }}"  {{ $req }}>{{  $game_field['field_value']  }}</textarea>
                                                        </div><!--col-md-12-->
                                                    </div><!--form-group row-->
                                                </div><!--col-md-6-->
    									<?php } else { ?>

                                            <div class="col-md-6 {{ $inputclass }}">
                                                <div class="form-group row">
                                                    <div class="col-md-12">

                                                        <label for="edit_field_name"> {{ $game_field['master_fields']['master_field_name'] }} <?php echo $star; ?> </label>
                                                        <input class="form-control" type="{{ $inputtype }}" name="input[game][{{ $game_field['id']}}]" value="{{  $game_field['field_value']  }}" id="fieldid_{{ $game_field['id'] }}"  {{ $req }}>
                                                    </div><!--col-md-12-->
                                                </div><!--form-group row-->
                                            </div><!--col-md-6-->
                                        <?php }

                                    }
                                }
                            }
                            ?>
                        </div><!--row-->

                        <div class="row">
                            <?php
                            if($game_fields){
                                foreach($game_fields as $game_field) {
                                    if($game_field['master_fields']['master_field_required'] == 'yes'){
                                        $req = 'required';
                                        $star = '<span class="text-danger">*</span>';
                                    } else {
                                        $req = '';
                                        $star = '';
                                    }

                                    if($game_field['master_fields']['master_field_type'] == 'gallery') { ?>
                                    <div class="col-md-12">
                                        <div class="form-group col-md-6 p-0 row">
                                            <div class="col-md-12">
                                                <h3  class="card-title text-dark">Add Images</h3>
                                                <label for="edit_field_name">{{ $game_field['master_fields']['master_field_name'] }}<?php echo $star ?></label>

                                                <input type="file" name="filename[]" multiple class="file-upload-default" {{ $req }}>

                                                <div class="input-group col-xs-12">
                                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Upload Image">
                                                    <span class="input-group-append">
                                                    <button class="file-upload-browse btn btn-success" type="button"> Choose Image</button>
                                                    </span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="multi_images[]"  class="form-control" value="<?php echo $game_field['id']; ?>">

                                        </div><!--form-group row-->
                                        <div class="galleryimages row">
                                        <?php

                                        if(json_decode($game_field['field_value'] )){
                                            foreach(json_decode($game_field['field_value']) as $ki=>$img){ ?>

                                            <div class="col-lg-3 col-md-6 col-xs-12 store-logo-upload-edit gallery_img_<?php echo $ki+1?>">

                                                <a href="javascript:void(0);" class="delete_game_image1">
                                                    <div class="badge badge-pill badge-warning" onclick="removegalleryImag('gallery_img_<?php echo $ki+1?>')"><i class="mdi mdi-close"></i></div>
                                                </a>
                                                <img src="<?php echo $img?>" class="game_gallery_img">
                                                <input class="form-control" type="hidden" name="input[game][{{ $game_field['id']}}][]" value="<?php echo $img?>" id="fieldid_{{ $ki }}"  {{ $req }}>
                                            </div>
                                            <?php
                                            }
                                        }?>
                                        </div>
                                    </div><!--col-md-6-->
                            <?php } } } ?>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <button class="cbtn csuccess btn-icon" type="submit" name="edit_game_submit" id="store_submit" value="SAVE"><i class="mdi mdi-content-save"></i></button>
                                    <button type="button" class="cbtn cdanger btn-icon" onclick="window.location.href = '{{ route('admin.game.index') }}'"><i class="mdi mdi-close"></i></button>
                                </div><!--form-group-->
                            </div><!--col-md-12-->
                        </div><!--row-->

                    </form>
                </div><!--col-->

            </div><!--row-->
        </div><!--card-body-->
    </div><!--card-->
</div><!--col-12 grid-margin stretch-card p-0-->

@push('before-scripts')
    <script src="{{ asset('/public/assets/js/file-upload.js') }}"></script>
	<script>
		function removegalleryImag(divRemoveClass){
			$("."+divRemoveClass).remove();
		}
	</script>
@endpush
@endsection
