<?php

namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fields;
use App\Models\StoreField;
use App\Models\DefaultFields;
use App\Http\Requests\Backend\AddfieldRequest;
use App\Models\GameFields;
use Validator;
use Illuminate\Support\Facades\View;

/**
 * Class RoleController.
 */
class FieldsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:field-list');
        $this->middleware('permission:field-create', ['only' => ['create','store']]);
        $this->middleware('permission:field-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:field-delete', ['only' => ['destroy']]); 
    }

    public function index( ){ 
        $fields = Fields::all()->sortByDesc("f_order");
        $defaultFields = DefaultFields::all()->toArray();
        $pagetitle = 'Master Matrix';
        $count_fields = count($fields); 
        View::share('menu_item', 'masterfileds');
        return view('backend.fields.index',compact(['fields', 'pagetitle', 'defaultFields', 'count_fields' ]));
    }
    public function create(AddfieldRequest $request){
        $sku = 0;
        $field = new Fields();
        $field->master_field_name = $request->input('f_name');
        $field->master_field_type = $request->input('f_type');
        $field->master_field_sku = '';
        $field->master_field_slug = $request->input('f_default');
        if($request->input('f_default') == 'game_name' || $request->input('f_default')  == 'game_description' || $request->input('f_default')  == 'price' || $request->input('f_default') == 'game_ean' ){
            $field->master_field_required = 'yes';
        }else{
            $field->master_field_required = $request->input('f_required');
        }
        $field->master_field_show = $request->input('f_show');
        if($request->input('f_required') == "yes"){
            $field->master_field_in_form = 'yes';
        }else{
            $field->master_field_in_form = $request->input('f_inform');
        }

        $field->master_field_isunic = $request->input('f_fieldunic'); 
        $field->master_field_unicname = $request->input('f_unicname');

        $maxOrderValue = Fields::max('f_order');
        $field->f_order = ($maxOrderValue + 1);
        $field->save();
        $sku = $field->id ; 
        $game_fields = GameFields::select('game_id', 'f_order')->groupBy('game_id')->get()->toArray();  
        foreach ($game_fields as $key ) {
            // echo '<pre>';
            // print_r( $key );
            $game_field = new GameFields();
            $game_field->game_id = $key['game_id'];
            $game_field->field_id = $field->id;
            $game_field->f_order = $key['f_order'];
            $game_field->save();
        } 
        if($field ){
            $def_fields = Fields::where('id','!=' ,$field->id)->where('master_field_slug',$request->input('f_default') )->get();
            foreach ($def_fields as $key => $value) {
                $defFields = Fields::where('id', $value->id)->first();
                $defFields->master_field_slug = 'other';
                $defFields->save();
            }
            $sku = $field->id;
            $field->master_field_sku = 'f'.str_pad($sku,5,"0", STR_PAD_LEFT);
            $field->save();
            return response()->json(['success'=>'New Field added successfully.', 'sku' => $field->master_field_sku ]);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
    public function edit(AddfieldRequest $request) {
        $field = Fields::find($request->input('id'));
        $field->master_field_name = $request->input('f_name');
        $field->master_field_type = $request->input('f_type');
        if($request->input('f_default') == 'game_name' || $request->input('f_default')  == 'game_description' ||$request->input('f_default')  == 'price' || $request->input('f_default') == 'game_ean' ){
            $field->master_field_required = 'yes';
        }else{
            $field->master_field_required = $request->input('f_required');
        }
        if($request->input('f_required') == "yes"){
            $field->master_field_in_form = 'yes';
        }else{
            $field->master_field_in_form = $request->input('f_inform');
        }
        $field->master_field_show = $request->input('f_show');
        $field->master_field_slug = $request->input('f_default');
        $field->master_field_isunic = $request->input('f_fieldunic');
        $field->master_field_unicname = $request->input('f_unicname');
        $field->save();

        $def_fields = Fields::where('id','!=' , $request->input('id'))->where('master_field_slug',$request->input('f_default') )->get();
        foreach ($def_fields as $key => $value) {
            $defFields = Fields::where('id', $value->id)->first();
            $defFields->master_field_slug = 'other';
            $defFields->save();
        }

        $store_fields = StoreField::where('field_id',$request->input('id'))->get(); 
        foreach ($store_fields as $store_f_key => $store_f_val ) {  
            // echo '<pre>';
            // print_r($request->input('f_unicname'));
            if($field->master_field_isunic == 'yes'){
                $store_f_val->field_unicDefault_name =  $request->input('f_unicname');  
            }else{
                $store_f_val->field_unicDefault_name =  NULL;
                $store_f_val->field_unicPersonal_name =  NULL;
            } 
            $store_f_val->save();
        }

        $game_fields = GameFields::where('field_id',$request->input('id'))->get();  
        if(count($game_fields) == 0){ 
            $new_game_fields = GameFields::select('game_id', 'f_order')->groupBy('game_id')->get()->toArray();  
            foreach ($new_game_fields as $key ) { 
                $game_field = new GameFields();
                $game_field->game_id = $key['game_id'];
                $game_field->field_id = $field->id;
                $game_field->field_value = $request->input('f_unicname');
                $game_field->f_order = $key['f_order'];
                $game_field->save();
            } 
        } else{
            foreach ($game_fields as $game_f_key => $game_f_val ) {   
                if($field->master_field_isunic == 'yes'){
                    $game_f_val->field_value =  $request->input('f_unicname');  
                }
                // else{
                //     GameFields::where('field_id',$game_f_val->field_id )->delete(); 
                // } 
                $game_f_val->save();
            }
        }
        // die;
        if($field){
            return response()->json(['success'=>'Field updated successfully.']);
        }
        return response()->json( ['error'=>$validator->errors()->all()] );
    }
    public function destroy($id){
        Fields::find($id)->delete($id);
        StoreField::where('field_id', $id)->delete();
        GameFields::where('field_id', $id)->delete();
        $fieldsData = Fields::all();
        $i = 1;
        foreach ($fieldsData as $key => $value) {
            $field = Fields::where('id', $value->id)->first();
            $field->f_order = $i;
            $field->update();
            $i++;
        }
        return response()->json(['success' => 'Field deleted successfully!' ]);
    }
    public function deleteAll(Request $request)
    {
        $ids = $request->input('fields_ids_arr');
        $ids_fields = explode(",", $ids);
        if($ids_fields != null){
            foreach ($ids_fields as $value) {
                if($value != null && !empty($value)){
                    $fields = Fields::find($value)->delete();
                    $storefield = StoreField::where('field_id', $value)->delete();
                    GameFields::where('field_id', $value)->delete();
                }
            }
        }
        return redirect()->route('admin.masterfields.index');
    }

}
