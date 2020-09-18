@push('before-styles') 
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
@endpush
<div class="mt-3"> 
    @include('includes.partials.messages') 
    @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
    @endif 
    <form class="forms-sample" id="eventform" method="POST" action="{{ route('admin.game.store') }}"  enctype="multipart/form-data">
        {{ csrf_field() }}

        <div class="row">
            <?php
            if($fields){
                foreach($fields as $field){ 
                    if($field->master_field_required == 'yes'){
                        $req = 'required';
                        $star = '<span class="text-danger">*</span>';
                    }  else{
                        $req = '';
                        $star = '';
                    }
                    if($field->master_field_type == 'image'){  ?>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="edit_field_name">{{ $field->master_field_name }}<?php echo $star ?></label>
                                    <input type="file" name="photos_{{ $field->id }}" id="game_{{ $field->id }}" class="file-upload-default">

                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled placeholder="Upload Image">
                                        <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-success" type="button"> Choose Image</button>
                                        </span>
                                    </div>
                                </div>
                                <input type="hidden" name="imagesids[]"  class="form-control" value="{{ $field->id }}">

                            </div><!--form-group row-->
                        </div><!--col-md-6-->
                    <?php } elseif($field->master_field_type != 'gallery'){
                        $inputtype = 'text';
                        $inputclass = '';
                        $inputid = 'game_'.  $field->id ;
                        if( $field->master_field_type == 'readonly' ){
                            $inputtype = 'hidden';
                            $inputclass = 'd-none';
                        }
                        if( $field->master_field_type == 'date' ){ 
                            $inputid = 'datepicker_'. $field->id;
                        }
                        if($field->master_field_in_form == 'no'){// add new field for in form 04-Nov
                            $inputclass = 'd-none';
                        }

                        if($field->master_field_slug == 'game_description'){ ?>
                            <div class="col-md-6 {{ $inputclass }}">
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <label for="edit_field_name">{{ $field->master_field_name }} <?php echo $star ?></label>
                                        <textarea class="form-control" name="input[game][{{ $field->id }}]" id="game_{{ $field->id }}" ><?php echo old('input.game.'.$field->id); ?></textarea>
                                    </div><!--col-md-12-->
                                </div><!--form-group row-->
                            </div><!--col-md-6-->
                        <?php } elseif($field->master_field_isunic == 'yes'){ ?> 
                            <input class="form-control" type="hidden" name="input[game][{{ $field->id }}]" id="game_{{ $field->id }}" value="<?php echo $field->master_field_unicname; ?>">
                        <?php }else{  ?>
                            <div class="col-md-6 {{ $inputclass }}">
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <label for="edit_field_name">{{ $field->master_field_name }} <?php echo $star ?></label>
                                        <input class="form-control" type="{{ $inputtype }}" name="input[game][{{ $field->id }}]" id="{{ $inputid }}" value="<?php echo old('input.game.'.$field->id); ?>">
                                    </div><!--col-md-12-->
                                </div><!--form-group row-->
                            </div><!--col-md-6-->
                        <?php } ?>
                        @push('before-scripts')  
                            <script> 
                                jQuery(document).ready(function($){  
                                    // alert("Hello");
                                    jQuery("#datepicker_<?php echo $field->id; ?>" ).each( function($){ 
                                        jQuery( this ).datepicker({
                                            dateFormat: "dd/mm/yy",
                                            minDate: 0
                                        });
                                    });
                                }); 
                            </script> 
                        @endpush
                        <?php  
                    } 
                }
            } ?>
        </div><!--row-->

        <div class="row">
            <?php
            foreach($fields as $field){
                if($field->master_field_required == 'yes'){
                    $req = 'required';
                    $star = '<span class="text-danger">*</span>';
                }  else{
                    $req = '';
                    $star = '';
                }
                if($field->master_field_type == 'gallery'){  ?>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <h3  class="card-title text-dark">Add Images</h3>
                                <label for="edit_field_name">{{ $field->master_field_name }}<?php echo $star ?></label>
                             <!--    <input type="file" multiple name="images[]" id="game_{{ $field->id }}" class="file-upload-default"> -->
                                <input type="file" name="filename[]" multiple class="file-upload-default">

                                <div class="input-group col-xs-12">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Upload Image">
                                    <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-success" type="button"> Choose Image</button>
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="multi_images[]"  class="form-control" value="{{ $field->id }}">

                        </div><!--form-group row-->
                    </div><!--col-md-6-->
                <?php }
            }?>
        </div><!--row-->

        <div class="row">
            <div class="col-md-12">
                <div class="form-group text-right">
                    <button class="cbtn csuccess btn-icon" type="submit" name="store_submit" id="store_submit" value="SAVE"><i class="mdi mdi-content-save"></i></button>
                    <button type="button" class="cbtn cdanger btn-icon mr-0" onclick="window.location.href = '{{ url('admin/game') }}'"><i class="mdi mdi-close"></i></button>
                </div><!--form-group-->
            </div><!--col-md-12-->
        </div><!--row-->

    </form>
</div><!--col-->
@push('before-scripts')
    <!-- <script src="https://code.jquery.com/jquery-1.12.4.js"></script> -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="{{ asset('/public/assets/js/file-upload.js') }}"></script>
@endpush

