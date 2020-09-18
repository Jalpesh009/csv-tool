$(document).ready( function() {
    $(document).on('click', '.delete_modal', function() {

        $('.delete_modal_field').val($(this).data('id'));
        $('.modal_fieldName').text('"'+ $(this).data('name')+'"');

        if($(this).parents('#fields_listTable tr').find('input').is(':checked')  ){
            $("#delete_field_modal").modal('show');
        }else{
            $("#delete_beforeValidation_modal").modal('show');
        }

        
    });
    $(document).on('click', '.delete_modal_1', function() {
        $('.deleterole').attr("href",$(this).data('url'));
        //$('.modal_fieldName').text('"'+ $(this).data('name')+'"');
    });
    $(document).on('click', '.delete_store', function() {
        $('.delete_modal_store').val($(this).data('id'));
        $('.modal_storeName').text('"'+ $(this).data('name')+'"');
    });
    $(document).on('click', '.delete_field', function() {
        $('.storeField_id').val($(this).data('id'));
        // $('.modal_storeName').text('"'+ $(this).data('name')+'"');
    });
    $(".cancel_btn").click(function () {
        $(".new-store-form").slideToggle(500);
        $(".new-store-form .alert").remove();
    });
    $('.dropdown-menu.dropdown-menu-right').attr('aria-labelledby', 'navbarDropdownLanguageLink').addClass('navbar-dropdown');
    allCheckboxesSelected();
    // allFieldsCheckboxesSelected();
});

$(document).on('click', ".delete-user", function() {
	$('.FormDeleteTime').on('submit',function(e){
        if(!confirm('Do you want to delete this item?')){
            e.preventDefault();
        }
    });
});

$(document).on('click', '.popup_store', function() {
    var gameId = $(this).data('id');
    $('.gameId').val(gameId);
});
$(document).on('click', '.csv_game', function() {
    var gameId = $(this).data('id');
    $('.csv_game_id').val(gameId);
    $('table#csv_storeGames').find('.downlod_gamecsv').each(function(){
        if ($(this).hasClass(gameId)) {
        }
        else
        {
            $(this).prop("disabled", true);
            $(this).addClass("cdark" );
            $(this).removeClass("cwarning" );
        }
    });
    $('table#csv_storeGames').find('.view_store_game').each(function(){
        if ($(this).hasClass(gameId)) {
        }
        else
        {
            $(this).prop("disabled", true);
            $(this).addClass("cdark" );
            $(this).removeClass("cprimary" );
        }
    });

    $('table#csv_storeGames').find('.mail_store').each(function(){
        if ($(this).hasClass(gameId)) {
            $(this).prop("disabled", true);
            $(this).addClass("cdark" );
            $(this).removeClass("cprimary" );
        }
    });
});

$(document).on('click', '.downlod_gamecsv', function() {
    var storeId = $(this).data('storeid');
    $('.csv_store_id').val(storeId);
});

$('.select_is_fieldunic').on('change', function (e) {
    var optionSelected = $(".select_is_fieldunic option:selected").val();
    // alert(optionSelected);
    if(optionSelected == 'yes'){
        $(".unicName_text").removeClass('d-none');
    }else{
        $(".unicName_text").addClass('d-none');
        $(".unicText").val('');
    } 
    
});
$('.select_default_field').on('change', function (e) {
    var optionSelected = $(".select_default_field option:selected").val();
    // alert(optionSelected);
    if(optionSelected == 'game_name' || optionSelected == 'game_logo' || optionSelected == 'price'){
        $(".select_field_show").find("option[value='yes']").attr("selected", true); 
        $(".select_field_required").find("option[value='yes']").attr("selected", true);
        $(".select_field_in_form").find("option[value='yes']").attr("selected", true); 
        $(".select_field_in_form").attr("disabled", true);
        
    }else{
        $(".select_field_show").find("option[value='yes']").attr("selected", false); 
        $(".select_field_required").find("option[value='yes']").attr("selected", false);  
        $(".select_field_in_form").find("option[value='yes']").attr("selected", false); 
        $(".select_field_in_form").attr("disabled", false);
    } 
    
});

$('#edit_f_isunic').on('change', function (e) {
    var optionSelected = $("#edit_f_isunic option:selected").val();
    // alert(optionSelected);
    if(optionSelected == 'yes'){
        $(".edit_unicName_text").removeClass('d-none');
    }else{
        $(".edit_unicName_text").addClass('d-none');
        $(".editUnicText").val('');
    }  
});


$(document).on('click', '.edit_modal', function() {
    $('#footer_action_button').text(" Update");
    $('#footer_action_button').addClass('glyphicon-check');
    $('#footer_action_button').removeClass('glyphicon-trash');
    $('.actionBtn').addClass('btn-success');
    $('.actionBtn').removeClass('btn-danger');
    $('.actionBtn').addClass('edit');
    $('.deleteContent').hide();
    $('.form-horizontal').show();
    $('#edit_field_id').val($(this).data('id'));
    $('#edit_field_name').val($(this).data('name'));
    $('#edit_field_type').val($(this).data('type'));
    $('#edit_field_sku').val($(this).data('sku'));
    $('#edit_field_required').val($(this).data('required'));
    $('#edit_field_show').val($(this).data('show'));
    $('#edit_f_inform').val($(this).data('inform'));
    $('#edit_field_default').val($(this).data('default')); 
    $('#edit_f_isunic').val($(this).data('isunic'));

    if($(this).data('isunic') == 'yes'){
        $(".edit_unicName_text").removeClass('d-none');
    }else{
        $(".edit_unicName_text").addClass('d-none');
        // $(".editUnicText").val('');
    } 

    $('#edit_funic_name').val($(this).data('unicname'));

    // alert($(this).data('show'));
    $('#myModal').modal('show');
});
$(document).on('click', ".add_field", function(e) {

    e.preventDefault();
    add_remove_div();
    var f_token = $('input[name="_token"]').val();
    var f_name = $('#field_name').val();
    var f_type = $('#field_type option:selected').val();
    var f_required = $('#field_required option:selected').val();
    var f_show = $('#field_show option:selected').val();
    var f_default = $('#default_field option:selected').val();
    var f_inform = $('#select_field_in_form option:selected').val();
    var f_fieldunic = $('#select_is_fieldunic option:selected').val(); 
    var f_unicname = $('#fieldunic_name').val();
    // alert(f_show);
    var f_sku = $('#sku').val();
    $.ajax({
        url: "masterfields/create",
        type:'POST',
        data: {_token:f_token,f_name:f_name,f_type:f_type,f_required:f_required,f_show:f_show,f_default:f_default,f_inform:f_inform,f_fieldunic:f_fieldunic,f_unicname:f_unicname},
        success: function(data) {
            if (data) {
                $('.success_error_message').show();
                $("body .store_listing .row .fields_list").load(window.location.href + " .store_listing .table-responsive" );
                $("#fieldsform").load(window.location.href + " #fieldsform" );
                $(".success_error_message .alert button").after('<span class="field_added">'+ data.success+ '</span>');
                $(".success_error_message .alert").addClass('alert-success');
                $(".success_error_message .alert").removeClass('alert-danger');
                $(".error-list").remove();
                $('.select_default_field').trigger('change');


            }
        },
        error: function (xhr) {
            $(".error-list").remove();
            $(".success_error_message .alert button").after('<ul class="list list-check m-0 error-list"> </ul>');
            if(xhr.responseJSON.errors){

                $.each(xhr.responseJSON.errors, function(key,value) {
                    if(value != 'undefined'){
                        $(".success_error_message .alert ").addClass('alert-danger');
                        $(".success_error_message .alert").removeClass('alert-success');
                        $('.success_error_message').show();
                        $(".error-list").append('<li>'+ value +'</li>');
                    }
                });
            }else{
                $('.success_error_message').hide();
            }
        }
    });
});
$('.modal-footer').on('click', '.edit_field', function() {
    add_remove_div();
    $.ajax({
        type: 'post',
        url: 'masterfields/edit',
        data: {
            '_token': $('input[name=_token]').val(),
            'id': $("#edit_field_id").val(),
            'f_name': $('#edit_field_name').val(),
            'f_type': $('#edit_field_type').val(),
            'f_required': $('#edit_field_required').val(),
            'f_show': $('#edit_field_show').val() ,
            'f_default': $('#edit_field_default').val(),
            'f_inform': $('#edit_f_inform').val(),
            'f_isunic': $('#edit_f_isunic').val(),
            'f_unicname': $('#edit_funic_name').val()

        },
        success: function(data) {
            $('#myModal').modal('hide');
            // $("#order-listing").load(window.location.href + " #order-listing" );
            // $("body").load(window.location.href  );
            $("body .store_listing .row .fields_list").load(window.location.href + " .store_listing .table-responsive" );
            $('.success_error_message').show();
            $(".success_error_message .alert button").after('<span class="field_added">'+ data.success+ '</span>');
            $(".success_error_message .alert").addClass('alert-success');
            $(".success_error_message .alert").removeClass('alert-danger');


        },
        error: function (xhr) {
            $(".error-list_edit").remove();
            $(".success_error_message_edit .alert button").after('<ul class="list list-check m-0 error-list_edit"> </ul>');
            if(xhr.responseJSON.errors){

                $.each(xhr.responseJSON.errors, function(key,value) {
                    if(value != 'undefined'){
                        $(".success_error_message_edit .alert ").addClass('alert-danger');
                        $(".success_error_message_edit .alert").removeClass('alert-success');
                        $('.success_error_message_edit').show();
                        $(".error-list_edit").append('<li>'+ value +'</li>');
                    }
                });
            }else{
                $('.success_error_message_edit').hide();
            }
        }
    });
});
$(document).on('click', ".delete_modal_field", function() {
    add_remove_div();
    var id = $(this).val();
    var token = $("meta[name='csrf-token']").attr("content");
    $.ajax({
        url: "masterfields/"+ id +"/delete",
        type:'POST',
        data: {id:id, _token: token},
        success: function(data) {
            // $("#order-listing").load(window.location.href + " #order-listing" );
            // $("body").load(window.location.href  );
            $("body .store_listing .row .fields_list").load(window.location.href + " .store_listing .table-responsive" );
            $('.success_error_message').show();
            $(".error-list").remove();
            $(".success_error_message .alert button").after('<span class="field_added">'+ data.success+ '</span>');
            $(".success_error_message .alert").addClass('alert-success');
            $(".success_error_message .alert").removeClass('alert-danger');
        }
    });
});
$(document).on('click', ".delete_modal_store", function() {
    var id = $(this).val();
    window.location.href="store/"+id+"/delete";
});

function add_remove_div(){
    $(".field_added").remove('');
    $(".error-list").empty();
    $('.success_error_message').hide();
}

// Image Upload in Edit Store
$('.store-logo-upload').hide();
function readURL(input) {
    // console.log($(input).attr("name"));
    if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src',  e.target.result  );
                $('#logo').val( input.files[0]['name'] );
                // $(".file-upload-info").val(input.files[0]['name']);
                $('#imagePreview').removeAttr('alt');
                $('.store-logo-upload').show();
                $('#imagePreview').fadeIn(650);
                $(".delete_image").show();
                $(".game_logo_img").attr('src', e.target.result );
                $(".game_logo_img").removeAttr('alt');
                $(".game_logo_img").fadeIn(650);
                $(".delete_game_image").show();
            }
            reader.readAsDataURL(input.files[0]);
        }
}
$("#old_store_logo").change(function() {
    readURL(this);
});

$('.store_logo').each(function(i){
  // event.stopPropagation();
  //   event.stopImmediatePropagation();

    $(this).change(function() {
        console.log(this.files);
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src',  e.target.result  );
                // $('#logo').val( this.files[0]['name'] );
                $(this).parents(".edit_image").find(".file-upload-info").val(this.files[0]['name']);
                // $('#imagePreview').removeAttr('alt');
                // $('.store-logo-upload').show();
                // $('#imagePreview').fadeIn(650);
                $(this).parents(".edit_image").find(".delete_image").show();
                $(this).parents(".edit_image").find(".game_logo_img").attr('src', e.target.result );
                $(this).parents(".edit_image").find(".game_logo_img").removeAttr('alt');
                $(this).parents(".edit_image").find(".game_logo_img").fadeIn(650);
                // $(".delete_game_image").show();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
$(".delete_image").click(function() {
    $(".logo_img1").attr('src', '');
    $('#imagePreview').hide();
    $("#logo").val('');
    $(this).hide();
});
$(".delete_game_image").click(function() {
    $(".game_logo_img").attr('src', '');
    $("#logo").val('');
     $(".game_logo_img").hide();
    $(this).hide();
});
function allCheckboxesSelected(){
    if($('.field_tr input[type="checkbox"]:checked').length == $('.field_tr input[type="checkbox"]').length){
        $(".all_field_tr").find('input[type="checkbox"]').attr('checked', 'checked');
    }else{
        $(".all_field_tr").find('input[type="checkbox"]').removeAttr('checked', 'checked');
    }
}
$('.field_tr input[type="checkbox"]').click(function(){
    allCheckboxesSelected();

});
$('.all_field_tr input[type="checkbox"]').click(function(){
    if($(this).is(':checked')){
        $('.field_tr input[type="checkbox"]').attr('checked', 'checked');
    }else{
        $('.field_tr input[type="checkbox"]').removeAttr('checked', 'checked');
    }

});

//To delete all Fields
function delete_fields(){
    var all_fields = [];
    $.each($("input[name='field_id']:checked"), function(){
        all_fields.push($(this).val());
    });
    var total_data =  all_fields.join(", ");

    $('#fields_ids_arr').val(total_data);
}
$('.delete_f_modal').click(function(e){
    if($('.delete_all_fields').is(':checked') || $('#fields_ids_arr').val() != ''  ){
        $("#delete_storefield_modal").modal('show');
    }else{
        $("#delete_beforeValidation_modal").modal('show');
    }
});

$('.delete_all_fields').click(function(e){
    var table= $(e.target).closest('table');
    if($(this).is(':checked')){
        $('td input:checkbox',table).attr('checked',this.checked);
    }else{
        $('td input:checkbox',table).removeAttr('checked');
    }
    delete_fields();
});
$('.tr_field input[type="checkbox"]').click(function(){
    delete_fields();
});

//To delete all Stores
function delete_stores(){
    var all_fields = [];
    $.each($("input[name='stores_id']:checked"), function(){
        all_fields.push($(this).val());
    });
    var total_data =  all_fields.join(", ");

    $('#stores_ids_arr').val(total_data);
}
$('.delete_all_stores').click(function(e){
    // alert(e.target);
    var table= $(e.target).closest('table');
    if($(this).is(':checked')){
        $('td input:checkbox',table).attr('checked',this.checked);
    }else{
        $('td input:checkbox',table).removeAttr('checked');
    }
    delete_stores();
});
$('.tr_store input[type="checkbox"]').click(function(){
    delete_stores();
});

//To delete all store's Fields
function delete_stores_fields(){
    var all_fields = [];
    $.each($("#manages_store_fields tbody input[type='checkbox']:checked"), function(){
        all_fields.push($(this).val());
    });
    var total_data =  all_fields.join(", ");
    // alert(total_data);
    $('#delete_store_fields_arr').val(total_data);
}
$('.delete_sf_modal').click(function(e){
    if($('#delete_store_fields').is(':checked') || $('#delete_store_fields_arr').val() != ''  ){
        $("#delete_storefield_modal").modal('show');
    }else{
        $("#delete_beforeValidation_modal").modal('show');
    }
});

$('#delete_store_fields').click(function(e){
    // alert(e.target);
    var table= $(e.target).closest('table');
    if($(this).is(':checked')){
        $('td input:checkbox',table).attr('checked',this.checked);
    }else{
        $('td input:checkbox',table).removeAttr('checked');
    }
    delete_stores_fields();
});


function delete_store_field_fn(id){
    delete_stores_fields();
}

//To assign store to game
function assign_storeGames(){
    var mult_stores = [];
    $.each($("#assign_storeGames tbody input[type='checkbox']:checked"), function(){
        mult_stores.push($(this).val());
    });
    var total_data =  mult_stores.join(", ");
    $('#store_ids_arr').val(total_data);
}

$(document).on('click', '.popup_store', function(e){
    var dataStores = $(this).data('stores');
    var dataGame = $(this).data('id');
    var game_id = $('.gameId').val();
    var gameName = $(this).parents("tr").find('.gameName').text();
    $('.modal_gameName').text(" : " + gameName);
    if(game_id == dataGame && dataStores != '' && dataStores.length > 0){
        $.each(dataStores, function(i, valu ){
            $(".tr_field input[name='store_id'][value='"+valu+"']").attr("checked", 'checked');

            if($(".tr_field input[value='"+valu+"']").is(':checked') ){
                $(".tr_field input[value='"+valu+"']:checked").parents(".game_tr").find(".single_store_submit").attr("disabled", 'disabled');
            }
        });
    }else{
        $(".game_tr .tr_field input[name='store_id']").removeAttr("checked", 'checked');
        $(".tr_field input[name='store_id']").parents(".game_tr").find(".single_store_submit").removeAttr("disabled", 'disabled');
    }
});
$('.assign_store_game').click(function(e){
    var table= $(e.target).closest('table');
    if($(this).is(':checked')){
        $('td input:checkbox',table).attr('checked',this.checked);
    }else{
        $('td input:checkbox',table).removeAttr('checked');
    }
    assign_storeGames();
});
$('.single_assign').click(function(e){
    assign_storeGames();
});
$('.single_store_submit').click(function(e){
    if($(this).parents('.game_tr').find('.single_assign').is(':checked')){
        $('#store_ids_arr').val($(this).val());
        $(this).attr('type', 'submit');
        $('.store_assign_error').empty();
    }   else{
        $('.store_assign_error').html("<div class='alert alert-danger my-2'><button type='button' class='close close_btn' data-dismiss='alert' aria-hidden='true'>&times;</button><span>It's wrong assignment, please assign correct store.</span></div>");
        $(this).attr('type', 'button');
    }
});

$('.store_game_btn').click(function(e){
    if($('#store_ids_arr').val() != '' ){
        $(this).attr('type', 'submit');
        $('.store_assign_error').empty();
    }else{
        $('.store_assign_error').html("<div class='alert alert-danger my-2'><button type='button' class='close close_btn' data-dismiss='alert' aria-hidden='true'>&times;</button><span>It's wrong assignment, please assign correct store.</span></div>");
        $(this).attr('type', 'button');
    }
});
$(document).on("click", '#gamestore_modal .modal_close', function(e){
    $(".close_btn").trigger("click");
});
$('.modal.fade.show').on("click", function(e){
    // $(".store_assign_error").empty();

});
$('#gamestore_modal').on('hide.bs.modal', function (e) {
    $(".store_assign_error").empty();
});

$("#checkAll").click(function(){
    $('.allcheckbox input:checkbox').not(this).prop('checked', this.checked);
});
function store_add_field(btnid){
    // alert(btnid);

    if($("#"+btnid).parents(".field_tr").find('.form-check-input').is(':checked')){
        $(".store_success_message").empty();
        var field_val = $("#"+btnid).data('fieldval' );
        var store_id = $("#"+btnid).data('storeid' );
        var token = $("meta[name='csrf-token']").attr("content");

        $.ajax({
            url: "view/addField",
            type:'POST',
            data: {_token:token, field_val:field_val, store_id:store_id},
            success: function(data) {

                $(".store_success_message").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><span class="field_added">'+ data.success +'</span></div>');
                setTimeout(function(){
                    location.reload(true);
                }, 1500);
            }
        });
    }

}
function store_remove_field(btnid){

    if($("#"+btnid).parents(".field_tr").find('.form-check-input').is(':checked')){
        $(".store_success_message").empty();
        var field_id = $("#"+btnid).data('fieldval' );
        var store_id = $("#"+btnid).data('storeid' );
        var token = $("meta[name='csrf-token']").attr("content");
        // alert(field_id);
        $.ajax({
            url: "view/removeField",
            type:'POST',
            data: {_token:token, field_id:field_id, store_id:store_id},
            success: function(data) {

                $(".store_success_message").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><span class="field_added">'+ data.success +'</span></div>');
                setTimeout(function(){
                    // location.reload(true);
                }, 1500);
            }
        });
    }

}
