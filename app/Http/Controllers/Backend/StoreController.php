<?php


namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Fields;
use App\Models\StoreField;
use App\Models\GameFields;
use App\Models\StoreGame;
use DB;
use App\Models\Auth\User;
use App\Http\Requests\Backend\AddstoreRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Response;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;

use Box\Spout\Writer\CSV\Writer;
use Illuminate\Support\Facades\View;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StoreController extends Controller
{
    public function __construct()
    {
        View::share('menu_item', 'store');
        $this->middleware('permission:store-list',['only' => ['index']]);
        $this->middleware('permission:store-create', ['only' => ['create','store']]);
        $this->middleware('permission:store-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:store-delete', ['only' => ['destroy']]);
        $this->middleware('permission:store-show',['only' => ['show']]);
        $this->middleware('permission:store-manage-field',['only' => ['get_fieldslisting']]);
        //get_fieldslisting
    }

    public function index() {
        $remove_fields = array('readonly', 'image', 'gallery');
        $headers = Fields::whereNotIn('master_field_type',  $remove_fields)->orderBy('f_order', 'ASC')->get();
        $stores = Store::orderBy('id','DESC')->get();
        $pagetitle = 'Manage Retailer Matrix';
        // View::share('menu_item', 'store');
        return view('backend.store.index', compact( 'stores', 'pagetitle', 'headers'));
    }
    public function create() {
        return view('backend.store.create' );
    }
    public function store(AddstoreRequest $request) {
        $requestData = $request->all();
        if( $request->hasFile('store_logo') ) {
            $logo = $request->file('store_logo');
            $extension = $logo->getClientOriginalExtension();
            Storage::disk('public')->put($logo->getClientOriginalName(), File::get($logo));
            $requestData['logo'] = url('/public/uploads/' . $logo->getClientOriginalName());
        }

        $store = new Store();
        $id =  $store->create($requestData)->id;
        if( $request->hasFile('store_csv') ) {

            $store_csv = $request->file('store_csv');
            $name = $store_csv->getClientOriginalName();
            $file_name = pathinfo($name, PATHINFO_FILENAME);
            $store_csv->move(public_path().'/store_csvs/'.$id.'/', $name );
            $csv_store = Store::where('id', $id )->first();
            $csv_store->static_csv = url('/public/store_csvs').'/'.$id.'/'.$name ;
            $csv_store->save();
        }
        return redirect()->route('admin.store.index')->with( 'success', 'Store added successfully' );
    }
    public function show($id) {
        $store = Store::find($id);
        if(!$store){
            return redirect()->route('admin.dashboard');
        }

        $pagetitle = 'Manage Retailer Matrix';
        $store_fields = StoreField::where('store_id',  $id)->get()->toArray();
        $count_all_fields = count($store_fields);
        $remove_fields = array('readonly', 'image', 'gallery');
        $fields = Fields::whereNotIn('master_field_type', $remove_fields)->get();
        $selected_fields = StoreField::select('field_id')->where('store_id', $store->id)->get()->pluck('field_id')->toArray();
        // $headers = Fields::where('master_field_show', 'yes')->where('master_field_type', '!=' ,'readonly')->orderBy('f_order', 'ASC')->get();
        $headers1 = Fields::where('master_field_show', 'yes')->where('master_field_slug', 'game_logo')->get();
        $headers2 = Fields::where('master_field_show', 'yes')->where('master_field_slug', 'game_name')->get();
        $notin = ['game_name', 'game_logo'];
        $headers3 = Fields::where('master_field_show', 'yes')->whereNotIn('master_field_slug', $notin)->where('master_field_type', '!=' ,'readonly')->orderBy('f_order', 'ASC')->get();

        $headers11 = $headers1->merge($headers2);
        $headers = $headers11->merge($headers3);
        // echo "<pre>";
        // print_r($headers);
        // die;
        $stores = Store::orderBy('id','DESC')->get();
        $count_stores = count($stores);
        return view('backend.store.show', compact('store', 'pagetitle', 'count_all_fields', 'fields', 'count_stores', 'stores', 'selected_fields', 'headers' ));
    }
    public function edit( Store $store ) {
        $pagetitle = 'Manage Retailer Matrix';
        $fields = Fields::all();
        return view('backend.store.edit', compact('pagetitle', 'fields', 'store'));
    }

    public function update(AddstoreRequest $request, $id ) {

        $requestData = $request->all();
        $store = Store::find($id);
        if( $request->hasFile('store_logo') ) {
            $logo = $request->file('store_logo');
            $extension = $logo->getClientOriginalExtension();
            Storage::disk('public')->put($logo->getClientOriginalName(), File::get($logo));
            $requestData['logo'] = url('/public/uploads/' .$logo->getClientOriginalName());
        }

        if( $request->hasFile('edit_store_csv') ) {
            File::deleteDirectory(public_path().'/store_csvs/'.$id );
            File::deleteDirectory( url('/public/store_csvs').'/'.$id );
            $edit_store_csv = $request->file('edit_store_csv');
            $extension = $edit_store_csv->getClientOriginalExtension();
            $name=$edit_store_csv->getClientOriginalName();
            $file_name = pathinfo($name, PATHINFO_FILENAME);
            $path = public_path().'/store_csvs/'.$id.'/'.$name;
            $edit_store_csv->move(public_path().'/store_csvs/'.$id.'/', $name );
            $store->static_csv = url('/public/store_csvs').'/'.$id.'/'.$name ;

            $store->save();

        } else{
            $store->static_csv  = $request->input('old_store_csv') ;
            $store->save();
        }

        $store->update($requestData);
        return redirect()->to('admin/store/'.$id.'/view')->with('success','Store updated successfully');
    }
    public function destroy($id) {
        Store::find($id)->delete();
        StoreField::where('store_id', $id)->delete();
        return redirect()->route('admin.store.index')->with('success','Store deleted successfully');
    }
    public function update_manage_fields( Request $request, $id ) {

        $requestData = $request->input('field_id');
        if(!empty($requestData) && $request->input('store_addFields') == 'Add Fields'){

            foreach ($requestData as $key => $value) {
                $single_field_id = StoreField::where('store_id', $id)->where('field_id', $value) ;
                if ($single_field_id->count() == 0) {
                    $maxOrder = StoreField::select('field_order')->where('store_id', $id)->orderBy('field_order','DESC')->first();
                    if(empty($maxOrder)){
                        $maxOrder =1;
                    }else{
                        $maxOrder = $maxOrder->field_order+1;
                    }
                    $storefield = new StoreField();
                    $storefield->store_id = $id ;
                    $storefield->field_id = $value;
                    $field_name= Fields::select('master_field_name','master_field_unicname')->where('id', $value)->first();
                    $storefield->field_personal_name = $field_name->master_field_name;
                    $storefield->field_unicDefault_name = $field_name->master_field_unicname;
                    $storefield->field_unicPersonal_name = $field_name->master_field_unicname;
                    $storefield->field_order = $maxOrder;
                    $storefield->save();

                }

            }
            return redirect('admin/store/'.$id.'/view')->with('success','Fields added successfully');
        }elseif(!empty($requestData) && $request->input('store_deleteFields') == 'Delete Fields'){
            foreach ($requestData as $key => $value) {
                StoreField::where('field_id',$value )->where('store_id', $id)->delete();
            }
            return redirect('admin/store/'.$id.'/view')->with('success','Fields deleted successfully');
        }
    }
    public function single_add_field( Request $request  ) {

        $store_id = $request->input('store_id') ;
        $fieldval =  $request->input('field_val');

        $single_field_id = StoreField::where('store_id', $store_id)->where('field_id', $fieldval) ;

        if ($single_field_id->count() == 0) {
            $maxOrder = StoreField::select('field_order')->where('store_id', $store_id)->orderBy('field_order','DESC')->first();
            if(empty($maxOrder)){
                $maxOrder =1;
            }else{
                $maxOrder = $maxOrder->field_order+1;
            }
            $storefield = new StoreField();
            $storefield->store_id = $store_id ;
            $storefield->field_id = $fieldval;
            $field_name= Fields::select('master_field_name','master_field_unicname')->where('id', $fieldval)->first();
            $storefield->field_personal_name = $field_name->master_field_name;
            $storefield->field_unicDefault_name = $field_name->master_field_unicname;
            $storefield->field_unicPersonal_name = $field_name->master_field_unicname;
            $storefield->field_order = $maxOrder;
            $storefield->save();
        }
        return response()->json(['success' => 'Field added successfully']) ;
    }

    public function single_remove_field( Request $request  ) {

        $store_id = $request->input('store_id') ;
        $field_id =  $request->input('field_id');
        StoreField::where('store_id', $store_id)->where('field_id', $field_id)->delete();

        $stores = StoreField::where('store_id', $store_id)->get();
        $i = 1;
        if(count($stores) > 0 ){
            foreach($stores as $rowOrder=>$newOrder){
                $newOrder->field_order = $i;
                $newOrder->save();
                $i++;
            }
        }
        return response()->json(['success' => 'Field deleted successfully']) ;
    }
    public function get_fieldslisting( Request $request  ) {
        //
        if($request->get('storeId') && $request->get('storeId') != '' ){

            $store_fildsHeaders = StoreField::with('master_fields') ;
            $store_fildsHeaders = $store_fildsHeaders->whereHas('master_fields', function($query) use( $request) {
                $query->where('master_field_isunic', 'yes' )->where('store_id', $request->get('storeId') );
            });
            $store_fildsHeaders = $store_fildsHeaders->orderBy('field_order','ASC')->get()->toArray();
            $f_ids = [];
            foreach($store_fildsHeaders as $key => $value ){
                $f_ids[] = $value['field_id'];

            }
            if($request->input('action') == 'edit'){
                $newReorderData = $request->input('data');

                foreach($newReorderData as $rowOrder=>$newOrder){
                    $field_data = StoreField::where('id',str_replace("row_","",$rowOrder))->first();
                    if(isset($newOrder['field_order'])){
                        $field_data->field_order = $newOrder['field_order'];
                    }else{
                        if( isset($newOrder['field_personal_name']) && $newOrder['field_personal_name'] != null){
                            $field_data->field_personal_name = $newOrder['field_personal_name'];
                        }
                         if( isset($newOrder['field_unicPersonal_name']) && $newOrder['field_unicPersonal_name'] != null){
                            $field_data->field_unicPersonal_name = $newOrder['field_unicPersonal_name'];
                        }
                    }
                    $field_data->save();
                }

            }
            if($request->input('action') == 'delete'){
                // echo '<pre>';
                // print_r($request->all()); // die;
                StoreField::where('id',str_replace("row_","",$request->input('id')))->delete();
                $totaldata = StoreField::where('store_id',$request->input('storeId'))->orderBy('field_order','ASC')->get() ;
                $i=1;
                foreach($totaldata as $k=>$field){
                    $field->field_order = $i;
                    $field->update();
                    $i++;
                }
            }
            $all_fields = StoreField::select('id', 'field_id', 'field_order','field_personal_name','field_unicDefault_name', 'field_unicPersonal_name','created_at')->where('store_id',$request->input('storeId'))->orderBy('field_order','ASC')->get()->toArray();

            $ajaxResponce = [];
            foreach($all_fields as $k=>$field){
                $all_fields[$k]['DT_RowId']="row_".$field['id'];
            }
            $ajaxResponce['data']= $all_fields;
            $ajaxResponce['isunic']= $f_ids ;
            return response()->json(  $ajaxResponce );exit;
        }
        return view('backend.store.show') ;
    }
    public function delete_store_Field(Request $request){
       // echo '<pre>';
       // print_r($request->all()); die;
        StoreField::where('id', $request->input('id') )->delete();
        $totaldata = StoreField::where('store_id',$request->input('storeId'))->orderBy('field_order','ASC')->get() ;
        $i=1;
        foreach($totaldata as $k=>$field){
            $field->field_order = $i;
            $field->update();
            $i++;
        }
        return response()->json(['success' => 'Field deleted successfully']) ;

    }
    public function deleteAll(Request $request) {
        $ids = $request->input('stores_ids_arr');
        $ids_fields = explode(",", $ids);
        if($ids_fields != null ){
            foreach ($ids_fields as  $value) {
                if($value != null && !empty($value)){
                    $fields = Store::find($value)->delete($value);
                    $storefield = StoreField::where('store_id', $value)->delete();
                }
            }
        }
        return redirect()->route('admin.store.index');
    }
    public function deleteStoreFieldsAll(Request $request) {
        $ids = $request->input('delete_store_fields_arr');
        $store_id = $request->input('store_id');
        $ids_fields = explode(",", $ids);
        if($ids_fields != null ){
            foreach ($ids_fields as $value) {
                if($value != null && !empty($value)){
                    $fields = StoreField::find($value)->delete($value);
                    $storefield = StoreField::where('store_id', $value)->delete();
                }
            }
        }
        $totaldata = StoreField::where('store_id',$store_id)->get() ;
        $i=1;
        if($totaldata != null){
            foreach($totaldata as $k=>$field){
                $field->field_order = $i;
                $field->update();
                $i++;
            }
        }
        return redirect()->to('admin/store/'.$store_id.'/view');
    }
    public function gamelist(Request $request, $id, $type ){
        $data=[];
        if( $type == 'gamelistss'){
            $data['headers'] = Fields::where('master_field_show', 'yes')->where('master_field_type', '!=' ,'readonly')->orderBy('f_order', 'ASC')->get();
            $maxcase = [];
            $dataheaders = Fields::where('master_field_show', 'yes')->where('master_field_type', '!=' ,'readonly')->orderBy('f_order', 'ASC')->get();
            $floatFiled=[];
            $imgFiled=[];
            $nameFiled=[];
            $fieldsFiled=[];
            $i = 2;
            foreach($dataheaders as $k=>$header){
                if($header->master_field_type == 'float'){
                    $floatFiled[$header->id] = 'field_'.$header->id;
                }
                if($header->master_field_slug == 'game_logo' && $header->master_field_type == 'image'){
                    $imgFiled[$header->id] = 'field_'.$header->id;
                    $maxcase[0] ='MAX(CASE WHEN `field_id` = '.$header->id.' THEN `field_value` END) as field_'.$header->id;
                }
                if($header->master_field_slug == 'game_name'){
                    $nameFiled[$header->id] = 'field_'.$header->id;
                    $maxcase[1] ='MAX(CASE WHEN `field_id` = '.$header->id.' THEN `field_value` END) as field_'.$header->id;
                }
                if($header->master_field_slug != 'game_name' && $header->master_field_slug != 'game_logo'){
                    $maxcase[$i] ='MAX(CASE WHEN `field_id` = '.$header->id.' THEN `field_value` END) as field_'.$header->id;
                    $i++;
                }
            }
            ksort($maxcase);
            $maxcasestr = implode(", ",$maxcase);
            $assigned_games = StoreGame::select( "game_id")->where('store_id', $id)->get()->pluck('game_id')->toArray();
            $all_tak = GameFields::select("f_order", "game_id", DB::raw($maxcasestr))->whereIn('game_id', $assigned_games )->groupBy('f_order') ;

            $seachCloumn = $request->input('columns');
            if(isset($seachCloumn)){
                foreach($seachCloumn as $search){
                    $field_id = str_replace('field_', '', $search['data']);

                    if($search['search']['value'] !=''){
                        $all_tak = $all_tak->having($search['data'], 'like', '%' . $search['search']['value'] . '%');

                    }
                }
            }
            $all_tak = $all_tak->get();
            // echo '<pre>';
            // print_r($all_tak);
            // die;
            foreach($all_tak as $k=>$field){
                $selectedFields = StoreGame::select("store_id")->where('game_id',$field->game_id)->get()->toArray();

                foreach($floatFiled as $fid=>$fname){
                    if (is_numeric($field->$fname)){
                        $price = '€ '. number_format($field->$fname, 2) ;
                    }else{
                        $price = $field->$fname;
                    }
                    $all_tak[$k]->$fname="<span class='text-success font-weight-bold'>".  $price .'</span>';
                }
                foreach($imgFiled as $imgid=>$iname){
                    $all_tak[$k]->$iname='<a href="'. url('/') .'/admin/game/'.$field->game_id.'/view" >'.  $field->$iname .'</a>';
                }
                foreach($nameFiled as $nameid=>$gname){
                    $all_tak[$k]->$gname='<a href="'. url('/') .'/admin/game/'.$field->game_id.'/view" >'.  $field->$gname .'</a>';
                }
                $all_tak[$k]->DT_RowId="row_".$field->f_order;
                $all_tak[$k]->field_0=25/30;
                $all_tak[$k]->assigned_stores = $selectedFields;

            }
            $ajaxResponce = [];
            $ajaxResponce['columns'] = [];
            $ajaxResponce['columns'][0]='ID';
            $m=3;
            foreach($dataheaders as $k=>$header){

                if($header->master_field_slug == 'game_logo' ){
                    $ajaxResponce['columns'][1]=$header->master_field_name;
                }
                if($header->master_field_slug == 'game_name' ){
                    $ajaxResponce['columns'][2]=$header->master_field_name;
                }
                if($header->master_field_slug != 'game_name' && $header->master_field_slug != 'game_logo'){
                    $ajaxResponce['columns'][$m]=$header->master_field_name;
                    $m++;
                }

            }
            ksort($ajaxResponce['columns']);
            $ajaxResponce['data'] = $all_tak;
            return response()->json( $ajaxResponce );exit;

        } else if($type == 'csv_storegame'){
            $data=[];
            $remove_fields = array('readonly', 'image', 'gallery');
            $store_fildsHeaders = StoreField::with('master_fields');
            $store_fildsHeaders = $store_fildsHeaders->whereHas('master_fields', function($query) use($remove_fields, $id) {
                $query->whereNotIn('master_fields.master_field_type', $remove_fields )->where('store_id', $id  );
            });
            $data['headers'] = $store_fildsHeaders->orderBy('field_order', 'ASC')->get();
            $maxcase = [];
            $dataheaders = $data['headers'];
            $ajaxResponce = [];
            $ajaxResponce['columns'] = [];
            $m=1;
            $csv_headers = [];
            $is_required = [];
            $UnicFiled=[];
            // $csv_unicValues = [];
            // $n = 1;
            if(count($dataheaders->toArray()) > 0){
                $floatFiled=[];
                foreach($dataheaders as $k=>$header){
                    if($header->master_fields->master_field_type == 'float'){
                        $floatFiled[$header->field_id] = 'field_'.$header->field_id;
                    }
                    if($header->master_fields->master_field_isunic == 'yes'){
                        $UnicFiled[$header->field_id] = 'field_'.$header->field_id;
                    }
                    $maxcase[] ='MAX(CASE WHEN `field_id` = '.$header->field_id.' THEN `field_value` END) as field_'.$header->field_id;
                }
                $maxcasestr = implode(", ",$maxcase);
                foreach($dataheaders as $k=>$header){
                    $ajaxResponce['columns'][$m] = $header ;
                    $csv_headers[] = $header->field_personal_name ;
                    if( $header->master_fields->master_field_required == 'yes' ){
                        $is_required[] = 'Requis';
                    }else{
                        $is_required[] = 'Facultatif';
                    }
                    $m++;
                }
            }else{
                $data['headers'] = Fields::whereNotIn('master_field_type',  $remove_fields)->orderBy('f_order', 'ASC')->get();
                $maxcase = [];
                $dataheaders = $data['headers'];
                $floatFiled=[];
                foreach($dataheaders as $k=>$header){
                    if($header->master_field_type == 'float'){
                        $floatFiled[$header->id] = 'field_'.$header->id;
                    }
                    if($header->master_field_isunic == 'yes'){
                        $UnicFiled[$header->id] = 'field_'.$header->id;
                    }
                    $maxcase[] ='MAX(CASE WHEN `field_id` = '.$header->id.' THEN `field_value` END) as field_'.$header->id;
                }
                $maxcasestr = implode(", ",$maxcase);
                foreach($dataheaders as $k=>$header){
                    $ajaxResponce['columns'][$m] = $header ;
                    $csv_headers[] = $header->master_field_name ;
                    if( $header->master_field_required == 'yes' ){
                        $is_required[] = 'Requis';
                    }else{
                        $is_required[] = 'Facultatif';
                    }
                    $m++;
                }
            }

            $all_tak = GameFields::select( DB::raw($maxcasestr))->where('game_id', $request->input('csv_game_id') )->groupBy('f_order')->get()->toArray();

            foreach($all_tak as $k=>$field){
                foreach($UnicFiled as $nameid=>$uname){
                    $storeval = StoreField::where('store_id', $id)->where('field_id', $nameid)->get()->toArray();
                    if(isset($storeval[0]['field_unicPersonal_name'])){
                        $all_tak[$k][$uname]=$storeval[0]['field_unicPersonal_name'];
                    }
                }
            }
            $newHeader=[];
            $newHeader[]=$csv_headers;
            $game_fields = GameFields::with('master_fields');
            $game_fields = $game_fields->whereHas('master_fields', function($query) use($request) {
                $query->where('master_fields.master_field_slug', 'game_name' )->where('game_id', $request->input('csv_game_id') );
            });
            $game_fields1 = $game_fields->first();

            $newHeader=[];
            $newHeader[]=$csv_headers;
            $isReq_header[]=$is_required;
            $newdata_arr=array_merge($isReq_header,$newHeader);
            $newdata=array_merge($newdata_arr,$all_tak);

            $filePathnew = public_path()."/uploads/".$id."-".$game_fields1->field_value.".xlsx";
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($filePathnew);

            $req_arr = array_filter($newdata[0], function($k) {
                return $k == 'Requis';
            }, ARRAY_FILTER_USE_BOTH   );
            $req_arr1 = [];
            foreach($req_arr as $index => $key) {
                $req_arr1[] = $index ;
            }
            foreach ($newdata as $rowindex => $row) {
                $cellss = [];
                $zebraWhiteStyle = '';
                foreach ($row as $cellindex => $rowData) {
                    $border = (new BorderBuilder())
                    ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->build();
                    $fontcolor = '000000';
                    if($rowindex == 0 || $rowindex == 1) {
                        if(in_array($cellindex, $req_arr1 )){
                             $zebraWhiteStyle = (new StyleBuilder())
                            ->setBackgroundColor(Color::rgb(255, 153, 0))
                            ->setFontColor($fontcolor)
                            ->setBorder($border)
                            ->setFontName('Calibri')
                            ->build();
                        }else {
                             $zebraWhiteStyle = (new StyleBuilder())
                            ->setBackgroundColor(Color::rgb(0, 192, 0))
                            ->setFontColor($fontcolor)
                            ->setBorder($border)
                            ->setFontName('Calibri')
                            ->build();
                        }
                    } else{
                        $zebraWhiteStyle = (new StyleBuilder())
                        ->setBackgroundColor(Color::WHITE)
                        ->setFontColor($fontcolor)
                        ->setBorder($border)
                        ->setFontName('Calibri')
                        ->build();
                    }

                    $cellss[] =   WriterEntityFactory::createCell($rowData, $zebraWhiteStyle);

                }
                $rowDataa = WriterEntityFactory::createRow($cellss );
                $writer->addRow($rowDataa);
            }

            $writer->close();
            return Response::download($filePathnew);
        }
    }
    public function deletegame(Request $request, $id ){
        StoreGame::where('game_id', $request->input('gameId'))->where('store_id', $id)->delete();
        return redirect()->to('admin/store/'.$id .'/view');
    } 
    public function createXLSX($filePath, $id, $newsheet, $storeIndentifier){
		set_time_limit(0); 
        $fileName = basename($filePath);
        $filePathnew = public_path()."/store_csvs/". $id."/new-".$fileName;
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePathnew);


        $sheet2 = $writer->getCurrentSheet();
        $cell_arr = [];

        if($storeIndentifier == 2){
            $sheet2->setName('FNAC');
        }else if($storeIndentifier == 1){//amazon
            // $zebraWhiteStyle = (new StyleBuilder()) 
            // ->setFontSize(22)
            // ->setFontBold() 
            // ->setFontName('Calibri')
            // ->build();
            // $zebraWhiteStyle1 = (new StyleBuilder()) 
            // ->setFontSize(11)
            // ->setFontBold() 
            // ->setFontName('Calibri')
            // ->build();
            // $cells = [
            //     WriterEntityFactory::createCell('Amazon.fr', $zebraWhiteStyle),
            //     WriterEntityFactory::createCell('Fichier de référencement - Jeux vidéo ('.date('d/m/Y').')', $zebraWhiteStyle1)
            // ];
            // $rowf = WriterEntityFactory::createRow($cells);
            // $writer->addRow($rowf);
            $sheet2->setName('Item_Sheet');
        }else if($storeIndentifier == 3){//Micromania
            $sheet2->setName('Feuil1');
        }else if($storeIndentifier == 4){//Auchan
            $sheet2->setName('Dynamic Sheet');
        }else if($storeIndentifier == 5){//Leclerc
            $sheet2->setName('Matr694190101ice');
        }else if($storeIndentifier == 6){//Cdiscount
            $sheet2->setName('MatriceInitialisation_VF');
        }else if($storeIndentifier == 7){//Carrefour
            $sheet2->setName('Données générales');
        }else if($storeIndentifier == 8){//Cultura
            $sheet2->setName('SAISIE');
        }else{
            $sheet2->setName('Dynamic Sheet');
        }
        $req_arr = array_filter($newsheet[0], function($k) {
            return $k == 'Requis';
        }, ARRAY_FILTER_USE_BOTH   );
        $req_arr1 = [];
        foreach($req_arr as $index => $key) {
            $req_arr1[] = $index ;
        }
        foreach ($newsheet as $rowindex => $row) {
            $cellss = [];
            $border = (new BorderBuilder())
                    ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->build();
                    $fontcolor = '000000';
            $zebraWhiteStyle = (new StyleBuilder())
                    ->setBackgroundColor(Color::WHITE)
                    ->setFontColor($fontcolor)
                    ->setBorder($border)
                    ->setFontName('Calibri')
                    ->build();
            foreach ($row as $cellindex => $rowData) { 
                if($storeIndentifier == 1){//Amazon
                    
                    if($rowindex == 0 || $rowindex == 1) {
                        if(in_array($cellindex, $req_arr1 )){
                             $zebraWhiteStyle = (new StyleBuilder())
                            ->setBackgroundColor(Color::rgb(255, 153, 0))
                            ->setFontColor($fontcolor)
                            ->setBorder($border)
                            ->setFontName('Calibri')
                            ->build();
                        }else {
                             $zebraWhiteStyle = (new StyleBuilder())
                            ->setBackgroundColor(Color::rgb(0, 192, 0))
                            ->setFontColor($fontcolor)
                            ->setBorder($border)
                            ->setFontName('Calibri')
                            ->build();
                        }
                    } else{
                        $zebraWhiteStyle = (new StyleBuilder())
                        ->setBackgroundColor(Color::WHITE)
                        ->setFontColor($fontcolor)
                        ->setBorder($border)
                        ->setFontName('Calibri')
                        ->build();
                    }

                    $cellss[] =   WriterEntityFactory::createCell($rowData, $zebraWhiteStyle);
 
                }else{
                    $cellss[] =   WriterEntityFactory::createCell($rowData); 
                }
            }
            $rowDataa = WriterEntityFactory::createRow($cellss );
            $writer->addRow($rowDataa); 
        } 
        
		
		$writer->close();
        return $filePathnew;  exit; 
    }

	public function MergeCreateNewFile($oldFilePath='',$folderID='',$store_indentifier,$onlyFileName){
		 //App\Helpers\CsvHelper::instance()->carrefour_csv();
        //echo $store_indentifier;die;
        // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("D:/wamp/www/matrices/public/store_csvs/new-Amazon.xlsx");
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($oldFilePath);
		
		if(!file_exists(public_path().'/store_csvs/'.$folderID.'/download')) {
			mkdir(public_path().'/store_csvs/'.$folderID.'/download', 0777, true);
		}
		$nfilep =  public_path().'/store_csvs/'.$folderID.'/download/'.$onlyFileName;
        if($store_indentifier == 1){
            
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(13);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(45);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(35);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(70);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(30);
            $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 3); 
            $spreadsheet->getActiveSheet()->insertNewRowBefore(6, 1);

            $spreadsheet->getActiveSheet()->mergeCells('B3:D3');
            $spreadsheet->getActiveSheet()->mergeCells('E3:G3');
            $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(2, 3, 'Amazon.fr');
            $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(5, 3, 'Fichier de référencement - Jeux vidéo ('.date('d/m/Y').')');
  
            $spreadsheet->getActiveSheet()->freezePane('A7'); 
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setVisible(false); 
            $spreadsheet->getActiveSheet()->getRowDimension(1)->setVisible(false); 
            $spreadsheet->getActiveSheet()->getRowDimension(2)->setVisible(false);  
 
            // Row 3   
                $row3col1styleArray1 = ['font' => ['size'  => 11,'bold'  => false,'color' => array('rgb' => '000000')],  
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]; 
                $spreadsheet->getActiveSheet()->getStyle('B3:DZ3')->applyFromArray($row3col1styleArray1)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff'); 
                $aligns =  [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true]; 
                $row3col1styleArray = ['font' => ['size'  => 22,'bold'  => true,'color' => array('rgb' => '000000')], 
                    'alignment' => $aligns,    
                ]; 
                $spreadsheet->getActiveSheet()->getStyle('B3')->applyFromArray($row3col1styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff'); 
            // END
            // Row 4 && 5 
                $aligns =  [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true];
                $row4and5Array = [ 
                    'font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => '000000')],
                    'alignment' => $aligns,  
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ];  
                $spreadsheet->getActiveSheet()->getStyle('B4:DZ5')->applyFromArray($row4and5Array); 
            // END
            //Row 6 
                $row6Array = [ 
                    'font' => ['size'  => 11,'bold'  => false,'color' => array('rgb' => '000000')], 
                ];  
                $spreadsheet->getActiveSheet()->getStyle('B6:DZ6')->applyFromArray($row6Array)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E4DFEC'); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(4, 6, "5030917096778");         
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(6, 6, "Call of Duty Modern Warfare 3");  
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(7, 6, "Consoles (rétro et mini)");   
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(8, 6, "Console (PC)");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(9, 6, "Games (Playstation 4) - Casual");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(10, 6, "Jeux de plate-formes");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(11, 6, "Sony Playstation 3");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(12, 6, "Sony Playstation 3");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(13, 6, "Sony Playstation 3");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(14, 6, "Sony Playstation 3");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(15, 6, "20131108");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(16, 6, "Oui");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(17, 6, "Oui");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(18, 6, "20130801");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(19, 6, "Activision");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(20, 6, "3+");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(21, 6, "33.44");    

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(22, 6, "6.22");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(23, 6, "0.01");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(24, 6, "39.99");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(25, 6, "3");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(26, 6, "600");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(27, 6, "26");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(28, 6, "23");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(29, 6, "23");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(30, 6, "25.00");    

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(33, 6, "international_mobile_equipment_identity");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(35, 6, "Type E - 2 broches français");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(36, 6, "Oui");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(38, 6, "China");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(39, 6, "Oui");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(40, 6, "Oui");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(41, 6, "NiMh"); 

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(42, 6, "AA");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(43, 6, "1");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(44, 6, "2");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(45, 6, "1");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(46, 6, "1");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(47, 6, "Batterie assemblée dans le produit");    
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(48, 6, "2"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(49, 6, "0.5"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(50, 6, "Transport"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(51, 6, "Stockage"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(52, 6, "Élimination"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(53, 6, "Autre"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(54, 6, "SGH"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(55, 6, "UN1993"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(56, 6, "www.safetysheetsRus.com/hazardous_substance/msds.pdf"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(57, 6, "180"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(58, 6, "Explosif"); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(59, 6, "Gaz comprimé"); 


                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(60, 6, "Corrosif");  

            // END

        } 
		if($store_indentifier == 7){
			
			
			$spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(12);
			$spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
			$spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
			$spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
			// firet need hide column which is not display in
			$spreadsheet->getActiveSheet()->getColumnDimension('F')->setVisible(false);
			$spreadsheet->getActiveSheet()->getColumnDimension('G')->setVisible(false);
			$spreadsheet->getActiveSheet()->getColumnDimension('K')->setVisible(false);
			$spreadsheet->getActiveSheet()->getColumnDimension('L')->setVisible(false);
			$spreadsheet->getActiveSheet()->getColumnDimension('M')->setVisible(false);
			//$spreadsheet->getActiveSheet()->getColumnDimension('AX')->setVisible(false);
			//freeze column 
			//$spreadsheet->getActiveSheet()->freezePane('A6');
			$spreadsheet->getActiveSheet()->freezePane('I1');
			
			// Merge cell
            $spreadsheet->getActiveSheet()->mergeCells('B1:O1');
			//END
			//all row style
			$rowThreestyleArray = ['font' => ['size'  => 9],'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];
			$rowFirstHeaderstyleArray = ['font' => ['size'  => 14,'bold'  => true,'color' => array('rgb' => 'FFFFFF')],
				'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];	
			$rowFirstheader2styleArray = ['font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => 'FFFFFF')],
				'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];		
			$rowsecond2HeaderstyleArray = ['font' => ['size'  => 9,'bold'  => true,'color' => array('rgb' => 'FFFFFF')],
				'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];	
			$styleArray = ['font' => ['size'  => 9,'color' => array('rgb' => 'FFFFFF')],
				'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];			
			//END
			
			//Col A value 
				//first row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'Comin Culture v2.4.1');
				$spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($rowFirstheader2styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
					->getStartColor()->setARGB('A6A6A6');
				
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 2, '0045496332730');
				$spreadsheet->getActiveSheet()->getStyle('A2')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
					->getStartColor()->setARGB('FF0000');
			 
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 3, '[frn]');
				$spreadsheet->getActiveSheet()->getStyle('A3')->applyFromArray($rowThreestyleArray);
			
			//END
			// B to O first row
				//first row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Données générales');//header 1
				$spreadsheet->getActiveSheet()->getStyle('B1')->applyFromArray($rowFirstHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('808080');
				
			//END
			// B and  C col third row
				//secodn row
				$spreadsheet->getActiveSheet()->getStyle('B2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				$spreadsheet->getActiveSheet()->getStyle('C2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(2, 3, '[carrefour]');//B3
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(3, 3, '[carrefour]');//C3
				$spreadsheet->getActiveSheet()->getStyle('B3:C3')->applyFromArray($rowThreestyleArray);
				
			//END
			// col D
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(4, 2, 'FRMSS6C');//D2
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(4, 3, '[frn]');
				$spreadsheet->getActiveSheet()->getStyle('D3')->applyFromArray($rowThreestyleArray);
			
			//END
			//D and E common style
			$spreadsheet->getActiveSheet()->getStyle('D2:E2')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
			->getStartColor()->setARGB('FF0000');
			//END
			//col E
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(5, 2, 'SUPER MARIO 3D WORLD');//E2
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(5, 3, '[frn]');
				$spreadsheet->getActiveSheet()->getStyle('E3')->applyFromArray($rowThreestyleArray);
			//END
			
			//Col H 
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(8, 2, 'WII U');
				$spreadsheet->getActiveSheet()->getStyle('H2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');		 
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(8, 3, '[frn]'); 
				$spreadsheet->getActiveSheet()->getStyle('H3')->applyFromArray($rowThreestyleArray);
			//END
			//col I second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(9, 2, '32684');
				$spreadsheet->getActiveSheet()->getStyle('I2')->applyFromArray($styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FF0000');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(9, 3, '[carrefour]');
				$spreadsheet->getActiveSheet()->getStyle('I3')->applyFromArray($rowThreestyleArray);
			//END
		   
			//col J 
				//first row
				
				//second row
				$spreadsheet->getActiveSheet()->getStyle('J2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(10, 3, '[calc]');
				$spreadsheet->getActiveSheet()->getStyle('J3')->applyFromArray($rowThreestyleArray);
			//End
			//$spreadsheet->getActiveSheet()->getColumnDimension('F:G')->setVisible(true);
			//$spreadsheet->getActiveSheet()->getColumnDimension('K:M')->setVisible(true);
			  
			//col N 
				//first row
				//second row
				$spreadsheet->getActiveSheet()->getStyle('N2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(14, 3, '[calc]');
				$spreadsheet->getActiveSheet()->getStyle('N3')->applyFromArray($rowThreestyleArray);
			//End
			//col O 
				//first row
				//second row
				 $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(15, 2, '12+');
				 $spreadsheet->getActiveSheet()->getStyle('O2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');	
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(15, 3, '[frn]');
				$spreadsheet->getActiveSheet()->getStyle('O3')->applyFromArray($rowThreestyleArray);
			//End
			// col P blank yello 
			$spreadsheet->getActiveSheet()->insertNewColumnBefore('P', 1);
			$spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(1);
			$spreadsheet->getActiveSheet()->getStyle('P1:P50')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
			// Q col and AC 
				//first row merge
				$spreadsheet->getActiveSheet()->mergeCells('Q1:AC1');
				$spreadsheet->getActiveSheet()->getStyle('Q1:AC1')->applyFromArray($rowFirstHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('C00000');	
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(17, 1, 'Données générales (autres)');
			//END	
			//col Q 
				// First row
				//second row
				 $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(17, 2, 'Nouveauté');
				 $spreadsheet->getActiveSheet()->getStyle('Q2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(17, 3, '[carrefour]');
				$spreadsheet->getActiveSheet()->getStyle('Q3')->applyFromArray($rowThreestyleArray);
			//END
			//col R 
				// First row
				//second row
				 $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(18, 2, 'Permanent');
				 $spreadsheet->getActiveSheet()->getStyle('R2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(18, 3, '[carrefour]');
				$spreadsheet->getActiveSheet()->getStyle('R3')->applyFromArray($rowThreestyleArray);
			//END	
			//col S 
				// First row
				//second row
				 $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(19, 2, '1');
				 $spreadsheet->getActiveSheet()->getStyle('S2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(19, 3, '[carrefour]');
				$spreadsheet->getActiveSheet()->getStyle('S3')->applyFromArray($rowThreestyleArray);
			//END	
			//Row  T to X 	
				//first row
				//second row 
				$spreadsheet->getActiveSheet()->getStyle('T2:X2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(20, 3, '[carrefour]');//T
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(21, 3, '[carrefour]');//U
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(22, 3, '[carrefour]');//V
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(23, 3, '[carrefour]');//W
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(24, 3, '[carrefour]');//X
				$spreadsheet->getActiveSheet()->getStyle('T3:X3')->applyFromArray($rowThreestyleArray);
			//End
			//col Y
				//firs row
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(25, 2, '20000');
				$spreadsheet->getActiveSheet()->getStyle('Y2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row 
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(25, 3, '[frn]');//Y
				$spreadsheet->getActiveSheet()->getStyle('Y3')->applyFromArray($rowThreestyleArray);
			//END
			//col Z
				//firs row
				
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(26, 2, '1000');
				$spreadsheet->getActiveSheet()->getStyle('Z2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row 
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(26, 3, '[carrefour]');//Z
				$spreadsheet->getActiveSheet()->getStyle('Z3')->applyFromArray($rowThreestyleArray);
			//END
			//col AA
				//firs row
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(27, 2, '500');
				$spreadsheet->getActiveSheet()->getStyle('AA2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row 
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(27, 3, '[carrefour]');//AA
				$spreadsheet->getActiveSheet()->getStyle('AA3')->applyFromArray($rowThreestyleArray);
			//END
			//col AB
				//firs row
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(28, 2, '1500');
				$spreadsheet->getActiveSheet()->getStyle('AB2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row 
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(28, 3, '[frn]');//AB
				$spreadsheet->getActiveSheet()->getStyle('AB3')->applyFromArray($rowThreestyleArray);
			//END
			//col AC
				//firs row
				
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(29, 2, 'SPOT TV');
				$spreadsheet->getActiveSheet()->getStyle('AC2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				
				//third row 
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(29, 3, '[frn+carrefour]');//AC
				$spreadsheet->getActiveSheet()->getStyle('AC3')->applyFromArray($rowThreestyleArray);
			//END
			// col AD blank yello 
			$spreadsheet->getActiveSheet()->insertNewColumnBefore('AD', 1);
			$spreadsheet->getActiveSheet()->getColumnDimension('AD')->setWidth(1);
			$spreadsheet->getActiveSheet()->getStyle('AD1:AD50')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
			//END	
			//col AE to AN 
				//first row merge
				$spreadsheet->getActiveSheet()->mergeCells('AE1:AN1');
				$spreadsheet->getActiveSheet()->getStyle('AE1:AN1')->applyFromArray($rowFirstHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
					->getStartColor()->setARGB('1F497D');	
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(31, 1, 'Données spécifiques Culture');
				//second row
				$spreadsheet->getActiveSheet()->getStyle('AE2:AN2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
			//END	
			//col AE
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(31, 2, 'MARIO');//AE
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(31, 3, '[frn]');//AE
				$spreadsheet->getActiveSheet()->getStyle('AE3')->applyFromArray($rowThreestyleArray);
			//END	
			//col AF
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(32, 2, 'Réalisateur');//AF
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(32, 3, '[frn]');//AF
				$spreadsheet->getActiveSheet()->getStyle('AF3')->applyFromArray($rowThreestyleArray);
			//END
			//col AG
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(33, 2, 'Shigeru Miyamoto');//AG
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(33, 3, '[frn]');//AG
				$spreadsheet->getActiveSheet()->getStyle('AG3')->applyFromArray($rowThreestyleArray);
			//END
			//col AH
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(34, 2, 'Producteur');//AH
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(34, 3, '[frn]');//AH
				$spreadsheet->getActiveSheet()->getStyle('AH3')->applyFromArray($rowThreestyleArray);
			//END
			//col AI
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(35, 2, 'Koichi Hayashida');//AI
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(35, 3, '[frn]');//AI
				$spreadsheet->getActiveSheet()->getStyle('AI3')->applyFromArray($rowThreestyleArray);
			//END
			//col AJ
				//second row
				 
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(36, 3, '[frn]');//AJ
				$spreadsheet->getActiveSheet()->getStyle('AJ3')->applyFromArray($rowThreestyleArray);
			//END
			//col AK
				//second row
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(37, 3, '[frn]');//AK
				$spreadsheet->getActiveSheet()->getStyle('AK3')->applyFromArray($rowThreestyleArray);
			//END
			//col AL
				//second row
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(38, 3, '[frn]');//AL
				$spreadsheet->getActiveSheet()->getStyle('AL3')->applyFromArray($rowThreestyleArray);
			//END
			//col AM
				//second row
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(39, 3, '[carrefour]');//AM
				$spreadsheet->getActiveSheet()->getStyle('AM3')->applyFromArray($rowThreestyleArray);
			//END
			//col AN
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(40, 2, '11/29/2013');//AN
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(40, 3, '[frn]');//AN
				$spreadsheet->getActiveSheet()->getStyle('AN3')->applyFromArray($rowThreestyleArray);
			//END
			// col AO blank yello 
			$spreadsheet->getActiveSheet()->insertNewColumnBefore('AO', 1);
			$spreadsheet->getActiveSheet()->getColumnDimension('AO')->setWidth(1);
			$spreadsheet->getActiveSheet()->getStyle('AO1:AO50')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
			//END
			//col AP to BA
				//first row merge
				$spreadsheet->getActiveSheet()->mergeCells('AP1:BA1');
				$spreadsheet->getActiveSheet()->getStyle('AP1:BA1')->applyFromArray($rowFirstHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
					->getStartColor()->setARGB('7030A0');	
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(42, 1, 'Données fournisseurs et tarifaires');
				//third row
				$spreadsheet->getActiveSheet()->getStyle('AP3:BA3')->applyFromArray($rowThreestyleArray);
			//END
			//col AP 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(42, 2, 'NINTENDO')->getStyle('AP2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(42, 3, '[frn]');//AP
			//END
			//col AQ 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(43, 2, '2322547')->getStyle('AQ2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(43, 3, '[frn]');//AQ
			//END
			//col AR 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(44, 2, '46700')->getStyle('AR2')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FF0000');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(44, 3, '[frn]');//AR
			//END
			//col AS 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(45, 2, '9')->getStyle('AS2')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FF0000');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(45, 3, '[frn]');//AS
			//END
			//col AT 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(46, 2, '100')->getStyle('AT2')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FF0000');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(46, 3, '[frn]');//AT
			//END
			//col AU 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(47, 2, 'Taux normal (7)')->getStyle('AU2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(47, 3, '[frn]');//AU
			//END
			//col AV 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(48, 2, '25')->getStyle('AV2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(48, 3, '[frn]');//AV
			//END
			//col AW 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(49, 2, '75')->getStyle('AW2')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FF0000');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(49, 3, '[frn]');//AW
			//END
			$spreadsheet->getActiveSheet()->getColumnDimension('AX')->setVisible(false);
			//col AX 
				//secodn row
				//$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(50, 2, '')->getStyle('AX2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				//->getStartColor()->setARGB('FFFF00');
				//third row
				//$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(50, 3, '[frn]');//AX
			//END
			//col AY 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(51, 2, '90')->getStyle('AY2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(51, 3, '[carrefour]');//AY
			//END
			//col AZ 
				//secodn row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(52, 2, '')->getStyle('AZ2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(52, 3, '[carrefour]');//AZ
			//END
			//col BA 
				//secodn row
				$spreadsheet->getActiveSheet()->getColumnDimension('BA')->setWidth(8);
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(53, 2, '80')->getStyle('BA2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(53, 3, '[carrefour]');//BA
			//END
			// col BB blank yello 
			$spreadsheet->getActiveSheet()->insertNewColumnBefore('BB', 1);
			$spreadsheet->getActiveSheet()->getColumnDimension('BB')->setWidth(1);
			$spreadsheet->getActiveSheet()->getStyle('BB1:BB50')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
			//END
			//col BC to BK
				//first row merge
				$spreadsheet->getActiveSheet()->mergeCells('BC1:BK1');
				$spreadsheet->getActiveSheet()->getStyle('BC1:BK1')->applyFromArray($rowFirstHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
					->getStartColor()->setARGB('E26B0A');	
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(55, 1, 'Données promotionelles');
				//third row
				$spreadsheet->getActiveSheet()->getStyle('BC3:BK3')->applyFromArray($rowThreestyleArray);
			//END
			// col BC
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(55, 2, '83690')->getStyle('BC2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(55, 3, '[calc]');//BC
			//END
			// col BD
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(56, 2, '120')->getStyle('BD2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(56, 3, '[carrefour]');//BD
			//END
			// col BE
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(57, 2, '20')->getStyle('BE2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(57, 3, '[carrefour]');//BE
			//END
			// col BF
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(58, 2, '12/20/2013')->getStyle('BF2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(58, 3, '[frn]');//BF
			//END
			// col BG
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(59, 2, '01/20/2014')->getStyle('BG2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(59, 3, '[frn]');//BG
			//END
			// col BH
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(60, 2, '12.0000')->getStyle('BH2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(60, 3, '[frn]');//BH
			//END
			// col BI
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(61, 2, '')->getStyle('BI2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(61, 3, '[frn]');//BI
			//END
			// col BJ
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(62, 2, '66.0000')->getStyle('BJ2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(62, 3, '[frn]');//BH
			//END
			// col BK
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(63, 2, '')->getStyle('BK2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(63, 3, '[frn]');//BK
			//END
			// col BL blank yello 
			$spreadsheet->getActiveSheet()->insertNewColumnBefore('BL', 1);
			$spreadsheet->getActiveSheet()->getColumnDimension('BL')->setWidth(1);
			$spreadsheet->getActiveSheet()->getStyle('BL1:BL50')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
			//END
			//col BM to BR
				//first row merge
				$spreadsheet->getActiveSheet()->mergeCells('BM1:BX1');
				$spreadsheet->getActiveSheet()->getStyle('BM1:BX1')->applyFromArray($rowFirstHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
					->getStartColor()->setARGB('16365C');	
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(65, 1, 'Taxes');
				//third row
				$spreadsheet->getActiveSheet()->getStyle('BM3:BX3')->applyFromArray($rowThreestyleArray);
				$spreadsheet->getActiveSheet()->getStyle('BS2:BX2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');//hidden no 2 row BG
			//END
			
			// col BM
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(65, 2, '201- Eco-Part. taux norm.E/UC D')->getStyle('BM2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(65, 3, '[frn]');//BM
			//END
			// col BN
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(66, 2, 'Euro par UVC')->getStyle('BN2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(66, 3, '[frn]');//BN
			//END
			// col BO
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(67, 2, '0.45')->getStyle('BO2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(67, 3, '[frn]');//BO
			//END
			// col BP
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(68, 2, '')->getStyle('BP2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(68, 3, '[frn]');//BP
			//END
			// col BQ
				//second row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(69, 2, '')->getStyle('BQ2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(69, 3, '[frn]');//BQ
			//END
			// col BR
				//second row 
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(70, 2, '')->getStyle('BR2')->applyFromArray($rowThreestyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
				//third row
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(70, 3, '[frn]');//BR
			//END
			//Hidden Col BS,BT,BU,BV,BW,BX
				$spreadsheet->getActiveSheet()->getColumnDimension('BS')->setVisible(false);
				$spreadsheet->getActiveSheet()->getColumnDimension('BT')->setVisible(false);
				$spreadsheet->getActiveSheet()->getColumnDimension('BU')->setVisible(false);
				$spreadsheet->getActiveSheet()->getColumnDimension('BV')->setVisible(false);
				$spreadsheet->getActiveSheet()->getColumnDimension('BW')->setVisible(false);
				$spreadsheet->getActiveSheet()->getColumnDimension('BX')->setVisible(false);
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(71, 3, '[frn]');//BS
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(72, 3, '[frn]');//BT
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(73, 3, '[frn]');//BU
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(74, 3, '[frn]');//BV
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(75, 3, '[frn]');//BW
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(76, 3, '[frn]');//BX
			//
			// col BY blank yello 
			$spreadsheet->getActiveSheet()->insertNewColumnBefore('BY', 1);
			$spreadsheet->getActiveSheet()->getColumnDimension('BY')->setWidth(1);
			$spreadsheet->getActiveSheet()->getStyle('BY1:BY50')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
			$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(70, 3, '[frn]');//BR	
			//END
			//col BZ
			$spreadsheet->getActiveSheet()->getStyle('BZ1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('808080');
			$spreadsheet->getActiveSheet()->getStyle('BZ2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF00');
			 
				$spreadsheet->getActiveSheet()->getStyle('BZ3')->applyFromArray($rowThreestyleArray);
			$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(78, 3, '[frn]');//BZ
			$spreadsheet->getActiveSheet()->getStyle('BZ4:BZ150')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('808080');
			//END
			$spreadsheet->getActiveSheet()->getColumnDimension('BZ')->setVisible(false);
			
			//Header setting
			// $spreadsheet->getActiveSheet()->removeRow('4');
			$spreadsheet->getActiveSheet()->getStyle('A4:O4')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('808080');
			$spreadsheet->getActiveSheet()->getStyle('Q4:AC4')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('C00000');
			$spreadsheet->getActiveSheet()->getStyle('AE4:AN4')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('1F497D');		
			$spreadsheet->getActiveSheet()->getStyle('AP4:BA4')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('60497A');
			$spreadsheet->getActiveSheet()->getStyle('BC4:BK4')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('E26B0A');
			$spreadsheet->getActiveSheet()->getStyle('BM4:BZ4')->applyFromArray($rowsecond2HeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('16365C');	
			$spreadsheet->getActiveSheet()->getStyle('D4')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->getStyle('N4')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->getStyle('R4:Y4')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->getStyle('AB4')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->getStyle('AL4:AN4')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->getStyle('AP4')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->getStyle('AV4')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->getStyle('BC4:BI4')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->getStyle('BR4')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->getStyle('A4:BX4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
			//END
			$borderstyleArray = [
				'borders' => [
					'allBorders' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
						'color' => ['argb' => '000000'],
					],
				],
			];
			$spreadsheet->getActiveSheet()->getStyle('A1:BZ550')->applyFromArray($borderstyleArray)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
			
				
        }
        if($store_indentifier == 5){
            $nfilep =  public_path().'/store_csvs/'.$folderID.'/download/'.$onlyFileName; 

            $spreadsheet->getActiveSheet()->insertNewColumnBefore('A', 1);
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(3); 
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(22);
            $spreadsheet->getActiveSheet()->getSheetView()->setZoomScale(70);
            $spreadsheet->getActiveSheet()->getColumnDimension('L')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('N')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('O')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('P')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('R')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('U')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('R')->setVisible(false);

            $spreadsheet->getActiveSheet()->getColumnDimension('AC')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('AD')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('AF')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('AG')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('AH')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('AI')->setVisible(false);
            $spreadsheet->getActiveSheet()->getColumnDimension('AJ')->setVisible(false); 

            $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 8); 

            $spreadsheet->getActiveSheet()->mergeCells('B1:G8');
            $spreadsheet->getActiveSheet()->mergeCells('H1:W8');
            $spreadsheet->getActiveSheet()->mergeCells('X1:Y8'); 
            $spreadsheet->getActiveSheet()->mergeCells('Z1:AK8');
            $spreadsheet->getActiveSheet()->mergeCells('AL1:BY6'); 
            $spreadsheet->getActiveSheet()->mergeCells('AN8:AO8');  
            $spreadsheet->getActiveSheet()->mergeCells('AP8:AQ8');  
            $spreadsheet->getActiveSheet()->mergeCells('AR8:AS8');  
            $spreadsheet->getActiveSheet()->mergeCells('AT8:AU8');  
            $spreadsheet->getActiveSheet()->mergeCells('AV8:AW8');  
            $spreadsheet->getActiveSheet()->mergeCells('AX8:AY8');  
            $spreadsheet->getActiveSheet()->mergeCells('AZ8:BA8');  
            $spreadsheet->getActiveSheet()->mergeCells('BB8:BC8');  
            $spreadsheet->getActiveSheet()->mergeCells('BD8:BE8');  
            $spreadsheet->getActiveSheet()->mergeCells('BF8:BG8');  
            $spreadsheet->getActiveSheet()->mergeCells('BH8:BI8');  
            $spreadsheet->getActiveSheet()->mergeCells('BJ8:BK8');
            $spreadsheet->getActiveSheet()->mergeCells('BL8:BM8');
            $spreadsheet->getActiveSheet()->mergeCells('BN8:BO8');
            $spreadsheet->getActiveSheet()->mergeCells('BP8:BQ8');  
            $spreadsheet->getActiveSheet()->mergeCells('BR8:BS8');
            $spreadsheet->getActiveSheet()->mergeCells('BT8:BU8');
            $spreadsheet->getActiveSheet()->mergeCells('BV8:BW8');
            $spreadsheet->getActiveSheet()->mergeCells('BX8:BY8');  
            // row 7 - row 8                                   
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(40, 7, "BOX 2");              
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(42, 7, "BOX 3");             
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(44, 7, "BOX 4");              
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(46, 7, "BOX 5");             
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(48, 7, "BOX 6");             
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(50, 7, "BOX 7");             
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(52, 7, "BOX 8");             
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(54, 7, "BOX 9");            
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(56, 7, "BOX 10");            
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(58, 7, "BOX 11");             
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(60, 7, "BOX 12");            
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(62, 7, "BOX 13");            
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(64, 7, "BOX 14");           
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(66, 7, "BOX 15");           
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(68, 7, "BOX 16");             
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(70, 7, "BOX 17");             
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(72, 7, "BOX 18");            
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(74, 7, "BOX 19");            
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(76, 7, "BOX 20");         
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(38, 8, "(à remplir par le fournisseur)");               
            //END
            //Row 9  
                $spreadsheet->getActiveSheet()->insertNewRowBefore(10, 3);  

                $spreadsheet->getActiveSheet()->mergeCells('B9:B12');
                $spreadsheet->getActiveSheet()->mergeCells('C9:C12'); 
                $spreadsheet->getActiveSheet()->mergeCells('D9:D12');
                $spreadsheet->getActiveSheet()->mergeCells('E9:E12'); 
                $spreadsheet->getActiveSheet()->mergeCells('F9:F12');
                $spreadsheet->getActiveSheet()->mergeCells('G9:G12'); 
                $spreadsheet->getActiveSheet()->mergeCells('H9:H12');
                $spreadsheet->getActiveSheet()->mergeCells('I9:I12');
                $spreadsheet->getActiveSheet()->mergeCells('J9:J12'); 
                $spreadsheet->getActiveSheet()->mergeCells('K9:K12');
                $spreadsheet->getActiveSheet()->mergeCells('Q9:Q12'); 
                $spreadsheet->getActiveSheet()->mergeCells('S9:S12');
                $spreadsheet->getActiveSheet()->mergeCells('T9:T12'); 
                $spreadsheet->getActiveSheet()->mergeCells('V9:V12');
                $spreadsheet->getActiveSheet()->mergeCells('W9:W12');
                $spreadsheet->getActiveSheet()->mergeCells('X9:X12'); 
                $spreadsheet->getActiveSheet()->mergeCells('Y9:Y12');
                $spreadsheet->getActiveSheet()->mergeCells('Z9:Z12'); 
                $spreadsheet->getActiveSheet()->mergeCells('AA9:AA12');
                $spreadsheet->getActiveSheet()->mergeCells('AB9:AB12'); 
                $spreadsheet->getActiveSheet()->mergeCells('AE9:AE12');
                $spreadsheet->getActiveSheet()->mergeCells('AK9:AK12'); 
            //END 
            $styleArray1 = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ];  
            $spreadsheet->getActiveSheet()->getStyle('B9:BY1000')->applyFromArray($styleArray1);
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ];  
            $spreadsheet->getActiveSheet()->getStyle('B1:BY8')->applyFromArray($styleArray);
            $aligns =  [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true]; 
           
            $rowDefaultCellstyleArray = ['font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => '000000')], 
                'alignment' => $aligns, 
                    
            ];     
            $spreadsheet->getActiveSheet()->getStyle('Z1:BY8')->applyFromArray($rowDefaultCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF00');   
            //Row  1 To 8
                //first, second, third, fourth, fifth, sixth, seventh, eighth row  
                // col 2
                // col B1 to G17 ;  
                    $rowfirstcolHeaderstyleArray = ['font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => '000000')], 
                        'alignment' => $aligns,  
                    ];     
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'CADRE RESERVE A SIPLEC / GALEC');  
                    $spreadsheet->getActiveSheet()->getStyle('B1:G17')->applyFromArray($rowfirstcolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FCD5B4'); 
                //END
                // col 8
                // col H1 to W8 ;  
                    $rowsecondcolHeaderstyleArray = ['font' => ['size'  => 72,'bold'  => true,'color' => array('rgb' => '000000')],
                        'alignment' => $aligns,  
                    ];  
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(8, 1, 'A renseigner par le fournisseur');  
                    $spreadsheet->getActiveSheet()->getStyle('H1:W8')->applyFromArray($rowsecondcolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); 
                //END
                // col 24
                // col X1 to Y20 ;  
                    $rowthirdcolHeaderstyleArray = ['font' => ['size'  => 16,'bold'  => true,'color' => array('rgb' => '000000')], 
                        'alignment' => $aligns,  
                    ];  
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(24, 1, 'RESERVE GALEC / SIPLEC');  
                    $spreadsheet->getActiveSheet()->getStyle('X1:Y20')->applyFromArray($rowthirdcolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FCD5B4'); 
                //END
                // col 26
                // col Z1 to AK8 ;  
                    $rowfourthcolHeaderstyleArray = ['font' => ['size'  => 48,'bold'  => true,'color' => array('rgb' => '000000')], 
                        'alignment' => $aligns,  
                    ];  
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(26, 1, 'A renseigner par le fournisseur');  
                    $spreadsheet->getActiveSheet()->getStyle('Z1:AK8')->applyFromArray($rowfourthcolHeaderstyleArray); 
                //END
            //END  
            //Row  1 To 6
                //first, second, third, fourth, fifth, sixth row  
                // col 38
                // col AL1 to BY6 ; 
                    $rowfifthcolHeaderstyleArray = ['font' => ['size'  => 24,'bold'  => true,'color' => array('rgb' => '000000')], 
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],  
                    ];  
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(38, 1, 'A RENSEIGNER PAR LE FOURNISSEUR (Sauf No Article Gessica)');  
                    $spreadsheet->getActiveSheet()->getStyle('AL1:BY6')->applyFromArray($rowfifthcolHeaderstyleArray); 
                //END
            //END               
            //Row 7 and 8          
                // row 7 - col 38 
                // col AL7 
                    $rowAL7colHeaderstyleArray = ['font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => '000000')], 
                            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,'wrapText' => True]   
                    ];  
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(38, 7, "TYPE D'ASSORTIMENT : MODULE OU BOX 1 ");  
                    $spreadsheet->getActiveSheet()->getStyle('AL7')->applyFromArray($rowAL7colHeaderstyleArray);
                //END 
               

            //Row 9 
                $spreadsheet->getActiveSheet()->getRowDimension('7')->setRowHeight(70); 
                $spreadsheet->getActiveSheet()->getRowDimension('9')->setRowHeight(90); 
                $spreadsheet->getActiveSheet()->getRowDimension('10')->setRowHeight(30); 
                $spreadsheet->getActiveSheet()->getRowDimension('11')->setRowHeight(50);   

                $rowRigthBordernNonecolHeaderstyleArray =  [
                    'font' => ['size'  => 16,'bold'  => true,'color' => array('rgb' => '000000')],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true], 
                ]; 
                
                // col H9 TO K12
                $spreadsheet->getActiveSheet()->getStyle('H9:K21')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); 
                $spreadsheet->getActiveSheet()->getStyle('L9:P15')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); 
                //END
                // col Q9 to Q12
                $spreadsheet->getActiveSheet()->getStyle('P9:P12')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FCD5B4'); 
                $spreadsheet->getActiveSheet()->getStyle('Q9:Q21')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FCD5B4'); 
                //END
                // col S9 TO W12
                $spreadsheet->getActiveSheet()->getStyle('S9:W21')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); 
                //END 
                // col Z9 TO AK12
                $spreadsheet->getActiveSheet()->getStyle('Z9:AK12')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); 
                $spreadsheet->getActiveSheet()->getStyle('Z13:AE21')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); 
                $spreadsheet->getActiveSheet()->getStyle('AK13:Ak21')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('00B0F0'); 
                $spreadsheet->getActiveSheet()->getStyle('AL9:BY10')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); 
                $spreadsheet->getActiveSheet()->getStyle('AL11:BY11')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FCD5B4'); 
                $spreadsheet->getActiveSheet()->getStyle('AL12:BY12')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); 

                $spreadsheet->getActiveSheet()->getStyle('AL12:BY12')->applyFromArray($rowRigthBordernNonecolHeaderstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); 

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(38, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(40, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(42, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(44, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(46, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(48, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(50, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(52, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(54, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(56, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(58, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(60, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(62, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(64, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(66, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(68, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(70, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(72, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(74, 9, 'GENCOD');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(76, 9, 'GENCOD');

                // END
                // Row 10
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(38, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(40, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(42, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(44, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(46, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(48, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(50, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(52, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(54, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(56, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(58, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(60, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(62, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(64, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(66, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(68, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(70, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(72, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(74, 10, 'N° CODE OP');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(76, 10, 'N° CODE OP'); 
                // END
                // Row 11
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(38, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(40, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(42, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(44, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(46, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(48, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(50, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(52, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(54, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(56, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(58, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(60, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(62, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(64, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(66, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(68, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(70, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(72, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(74, 11, 'N° ARTICLE GESSICA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(76, 11, 'N° ARTICLE GESSICA'); 
                // END
                // Row 12
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(38, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(40, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(42, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(44, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(46, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(48, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(50, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(52, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(54, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(56, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(58, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(60, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(62, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(64, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(66, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(68, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(70, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(72, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(74, 12, '0');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(76, 12, '0'); 

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(39, 12, '=SUM(AM13:AM103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(41, 12, '=SUM(AO13:AO103)'); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(43, 12, '=SUM(AQ13:AQ103)'); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(45, 12, '=SUM(AS13:AS103)'); 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(47, 12, '=SUM(AU13:AU103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(49, 12, '=SUM(AW13:AW103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(51, 12, '=SUM(AY13:AY103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(53, 12, '=SUM(BA13:BA103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(55, 12, '=SUM(BC13:BC103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(57, 12, '=SUM(BE13:BE103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(59, 12, '=SUM(BG13:BG103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(61, 12, '=SUM(BI13:BI103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(63, 12, '=SUM(BK13:BK103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(65, 12, '=SUM(BM13:BM103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(67, 12, '=SUM(BO13:BO103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(69, 12, '=SUM(BQ13:BQ103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(71, 12, '=SUM(BS13:BS103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(73, 12, '=SUM(BU13:BU103)');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(75, 12, '=SUM(BW13:BW103)');  
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(77, 12, '=SUM(BY13:BY103)'); 

                $spreadsheet->getActiveSheet()->getStyle('AM12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('AO12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('AQ12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('AS12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('AU12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('AW12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('AY12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BA12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BC12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BE12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BG12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BI12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BK12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BM12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BO12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BQ12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BS12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BU12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BW12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-');
                $spreadsheet->getActiveSheet()->getStyle('BY12')->getNumberFormat()->setFormatCode('_-* #,##0.00 [$€-40C]_-;-* #,##0.00 [$€-40C]_-;_-* "-"?? [$€-40C]_-;_-@_-'); 
                $spreadsheet->getActiveSheet()->getStyle('AM13:AM1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('AO13:AO1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('AQ13:AQ1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('AS13:AS1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('AU13:AU1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('AW13:AW1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('AY13:AY1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BA13:BA1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BC13:BC1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BE13:ABE1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BG13:BG1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BI13:BI1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BK13:BK1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BM13:BM1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BO13:BO1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BQ13:BQ1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BS13:BS1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BU13:BU1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BW13:BW1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                $spreadsheet->getActiveSheet()->getStyle('BY13:BY1000')->getNumberFormat()->setFormatCode('#,##0.00 "€"'); 
                
                // col AL8
                    $rowTextstyleArray1 =  [
                        'font' => ['size'  => 16,'bold'  => true,'color' => array('rgb' => '000000')],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true], 
                    ];  
                    $spreadsheet->getActiveSheet()->getStyle('AL8')->applyFromArray($rowTextstyleArray1); 
                //END
                // col B13:AK10000
                    $rowTextstyleArray2 =  [
                        'font' => ['size'  => 14,'bold'  => false,'color' => array('rgb' => '000000')],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true], 
                    ];  
                    $spreadsheet->getActiveSheet()->getStyle('B13:BY10000')->applyFromArray($rowTextstyleArray2); 
                //END

                // col AL9:BY11
                    $rowTextstyleArray3 =  [
                        'font' => ['size'  => 10,'bold'  => true],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true], 
                    ];  
                    $spreadsheet->getActiveSheet()->getStyle('AL9:BY11')->applyFromArray($rowTextstyleArray3); 
                //END
                // col AL10:BY11
                    $rowTextstyleArray4 =  [
                        'font' => ['color' => array('rgb' => '0070C0')],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true], 
                    ];  
                    $spreadsheet->getActiveSheet()->getStyle('AL10:BY11')->applyFromArray($rowTextstyleArray4); 
                //END
                // col AL12:BY12
                    $rowTextstyleArray4 =  [
                        'font' => ['size'  => 16,'bold'  => false,'color' => array('rgb' => '000000')],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true], 
                    ];  
                    $spreadsheet->getActiveSheet()->getStyle('AL12:BY12')->applyFromArray($rowTextstyleArray4); 

                //END

               // END

            //END 
        } 
        if($store_indentifier == 4){
            $nfilep =  public_path().'/store_csvs/'.$folderID.'/download/'.$onlyFileName; 
			$spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(12);
			
			$spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(10);
			$borderstyleArray = [
				'borders' => [
					'allBorders' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
						'color' => ['argb' => '000000'],
					],
				],
			];
			$spreadsheet->getActiveSheet()->getStyle('A1:AZ550')->applyFromArray($borderstyleArray)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
			$spreadsheet->getActiveSheet()->getStyle('A1:AZ1')->getAlignment()->setWrapText(true);
            //END
        }  
        if($store_indentifier == 3){
            $nfilep =  public_path().'/store_csvs/'.$folderID.'/download/'.$onlyFileName; 
            $spreadsheet->getActiveSheet()->freezePane('A2'); 
            $aligns =  [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true]; 
            $row1col1styleArray = ['font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => '000000')], 
                'alignment' => $aligns,  
            ];  
            $spreadsheet->getActiveSheet()->getStyle('A1:AZ1')->applyFromArray($row1col1styleArray);
        } 
        if($store_indentifier == 2){
            $spreadsheet->getActiveSheet()->freezePane('A1'); 
            $nfilep =  public_path().'/store_csvs/'.$folderID.'/download/'.$onlyFileName; 
			$spreadsheet->getActiveSheet()->freezePane('A2');
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ];  
            $spreadsheet->getActiveSheet()->getStyle('A1:DS1000')->applyFromArray($styleArray);
            // Row 1
                $aligns =  [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,'wrapText' => true]; 
                $rowBlackfontCellstyleArray = ['font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => '000000')], 
                    'alignment' => $aligns                         
                ];    
                $rowWhitefontCellstyleArray = ['font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => 'ffffff')], 
                    'alignment' => $aligns 
                ];    
                $rowYellowfontCellstyleArray = ['font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => 'E1FF00')], 
                    'alignment' => $aligns                         
                ];    
                $spreadsheet->getActiveSheet()->getStyle('A1:B1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C4D79B');  
                $spreadsheet->getActiveSheet()->getStyle('C1:E1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000'); 
                $spreadsheet->getActiveSheet()->getStyle('F1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('EBF1DE');
                $spreadsheet->getActiveSheet()->getStyle('G1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8DB4E2'); 
                $spreadsheet->getActiveSheet()->getStyle('H1:I1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000');  
                $spreadsheet->getActiveSheet()->getStyle('J1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C00000');  
                $spreadsheet->getActiveSheet()->getStyle('K1:O1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8DB4E2');
                $spreadsheet->getActiveSheet()->getStyle('P1:Q1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C4D79B');
                $spreadsheet->getActiveSheet()->getStyle('R1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8DB4E2'); 
                $spreadsheet->getActiveSheet()->getStyle('S1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000');  
                $spreadsheet->getActiveSheet()->getStyle('T1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C00000'); 
                $spreadsheet->getActiveSheet()->getStyle('U1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8DB4E2'); 
                $spreadsheet->getActiveSheet()->getStyle('V1:Y1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000'); 
                $spreadsheet->getActiveSheet()->getStyle('Z1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D8E4BC');
                $spreadsheet->getActiveSheet()->getStyle('AA1:AF1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000'); 
                $spreadsheet->getActiveSheet()->getStyle('AG1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8DB4E2'); 
                $spreadsheet->getActiveSheet()->getStyle('AH1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000'); 
                $spreadsheet->getActiveSheet()->getStyle('AI1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D8E4BC');
                $spreadsheet->getActiveSheet()->getStyle('AJ1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C00000'); 
                $spreadsheet->getActiveSheet()->getStyle('AK1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D8E4BC');
                $spreadsheet->getActiveSheet()->getStyle('AL1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C00000'); 
                $spreadsheet->getActiveSheet()->getStyle('AM1:AN1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D8E4BC');
                $spreadsheet->getActiveSheet()->getStyle('AO1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000'); 
                $spreadsheet->getActiveSheet()->getStyle('AP1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D8E4BC');
                $spreadsheet->getActiveSheet()->getStyle('AQ1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C00000');
                $spreadsheet->getActiveSheet()->getStyle('AR1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000');  
                $spreadsheet->getActiveSheet()->getStyle('AS1:AT1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D8E4BC');
                $spreadsheet->getActiveSheet()->getStyle('AU1:AV1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('EBF1DE');
                $spreadsheet->getActiveSheet()->getStyle('AW1:AY1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000');  
                $spreadsheet->getActiveSheet()->getStyle('AZ1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('EBF1DE');
                $spreadsheet->getActiveSheet()->getStyle('BA1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C4D79B');
                $spreadsheet->getActiveSheet()->getStyle('BB1:BC1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D8E4BC');
                $spreadsheet->getActiveSheet()->getStyle('BD1:BF1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('EBF1DE');
                $spreadsheet->getActiveSheet()->getStyle('BG1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C4D79B');
                $spreadsheet->getActiveSheet()->getStyle('BH1:BP1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000'); 
                $spreadsheet->getActiveSheet()->getStyle('BQ1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8DB4E2');  
                $spreadsheet->getActiveSheet()->getStyle('BR1:BS1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('EBF1DE');
                $spreadsheet->getActiveSheet()->getStyle('BT1:BZ1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8DB4E2');  
                $spreadsheet->getActiveSheet()->getStyle('CA1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('DCE6F1'); 
                $spreadsheet->getActiveSheet()->getStyle('CB1')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000'); 
                $spreadsheet->getActiveSheet()->getStyle('CC1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('DCE6F1'); 
                $spreadsheet->getActiveSheet()->getStyle('CD1:CH1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8DB4E2'); 
                $spreadsheet->getActiveSheet()->getStyle('CI1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('E6B8B7'); 
                $spreadsheet->getActiveSheet()->getStyle('CJ1:CM1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F2DCDB');  
                $spreadsheet->getActiveSheet()->getStyle('CN1')->applyFromArray($rowYellowfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000'); 
                $spreadsheet->getActiveSheet()->getStyle('CO1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C4BD97'); 
                $spreadsheet->getActiveSheet()->getStyle('CP1')->applyFromArray($rowYellowfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('000000'); 
                $spreadsheet->getActiveSheet()->getStyle('CQ1:DB1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C4BD97'); 
                $spreadsheet->getActiveSheet()->getStyle('DC1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C4D79B'); 
                $spreadsheet->getActiveSheet()->getStyle('DD1:DS1')->applyFromArray($rowBlackfontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('E26B0A'); 
                
                $spreadsheet->getActiveSheet()->getStyle('J2:J500')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D9D9D9'); 
                $spreadsheet->getActiveSheet()->getStyle('Q2:Q500')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D9D9D9'); 
                $spreadsheet->getActiveSheet()->getStyle('T2:T500')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D9D9D9'); 
                $spreadsheet->getActiveSheet()->getStyle('AI2:AJ500')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D9D9D9');
                $spreadsheet->getActiveSheet()->getStyle('AL2:AN500')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D9D9D9'); 
                $spreadsheet->getActiveSheet()->getStyle('AQ2:AQ500')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D9D9D9'); 
                $spreadsheet->getActiveSheet()->getStyle('BS2:BS500')->applyFromArray($rowWhitefontCellstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D9D9D9'); 
            // END
        }  
        if($store_indentifier == 8){
            $nfilep =  public_path().'/store_csvs/'.$folderID.'/download/'.$onlyFileName; 
			$headerStyleArray = ['font' => ['size'  => 9,'bold'  => true,'color' => array('rgb' => '000000')],
				'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];	
			$header2StyleArray = ['font' => ['size'  => 23,'bold'  => false,'color' => array('rgb' => '000000')],
				'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]];		
			//header setting
			$spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(9);
			//$spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setHeight(30);
			$spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(10);
			//$spreadsheet->getActiveSheet()->getStyle('B1')->getDefaultColumnDimension()->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(40); 
			$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(30); 
			
			
			$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(7); 
			$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(7); 
			$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(7); 
			
			$spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(9); 
			$spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(5); 
			$spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(12); 
			
			$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(12); 
			$spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(12); 
			
			$spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(9); 
			$spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(9); 
			$spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(9); 
			$spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(9); 
			$spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(9); 
			
			$spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(9); 
			$spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(9); 
			$spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(9);
			$spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(9);	
			$spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(9);
			$spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(9);	
			
			$spreadsheet->getActiveSheet()->getStyle('A1:AF1')->getAlignment()->setWrapText(true);
			$spreadsheet->getActiveSheet()->mergeCells('M2:Q5');
			$spreadsheet->getActiveSheet()->mergeCells('X2:AF5');
			$spreadsheet->getActiveSheet()->getStyle('A1:AF1')->applyFromArray($headerStyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('8DB4E2'); 
			$spreadsheet->getActiveSheet()->getStyle('A2:AF5')->applyFromArray($headerStyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D9D9D9'); 
			$spreadsheet->getActiveSheet()->getStyle('M2:Q5')->applyFromArray($headerStyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('A6A6A6'); 
			
			
			$spreadsheet->getActiveSheet()->getStyle('I6:J550')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('EBF1DE');
			$spreadsheet->getActiveSheet()->getStyle('R6:U550')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('EBF1DE');
			$spreadsheet->getActiveSheet()->getStyle('W6:W550')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('EBF1DE');
				
			$spreadsheet->getActiveSheet()->getStyle('D2:E5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00'); 
			
			$spreadsheet->getActiveSheet()->getStyle('A1:F1')->applyFromArray($headerStyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050'); 
			$spreadsheet->getActiveSheet()->getStyle('H1:J1')->applyFromArray($headerStyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050'); 
			$spreadsheet->getActiveSheet()->getStyle('R1:W1')->applyFromArray($headerStyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050'); 
            $spreadsheet->getActiveSheet()->getStyle('A1:AF1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
			
			$borderstyleArray = [
				'borders' => [
					'allBorders' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
						'color' => ['argb' => '000000'],
					],
				],
			];
			$spreadsheet->getActiveSheet()->getStyle('A1:AF550')->applyFromArray($borderstyleArray)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
			$spreadsheet->getActiveSheet()->getStyle('X2:AF5')->applyFromArray($header2StyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D9D9D9'); 
			$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(24, 2, "Tu n'es pas concernée");
			
			//END
        }  

        if($store_indentifier == 6){  
            $nfilep =  public_path().'/store_csvs/'.$folderID.'/download/'.$onlyFileName;  
            $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1); 
            $spreadsheet->getActiveSheet()->insertNewRowBefore(3, 1); 
            $spreadsheet->getActiveSheet()->getSheetView()->setZoomScale(60);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(10);
            $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(50);  
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(55);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(22);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20); 
            $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(22); 
            $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(30);  

            $spreadsheet->getActiveSheet()->mergeCells('A1:O1');
            $spreadsheet->getActiveSheet()->mergeCells('Q1:R1');  
            $spreadsheet->getActiveSheet()->mergeCells('S1:AF1'); 
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ];  
            $spreadsheet->getActiveSheet()->getStyle('A1:AJ1000')->applyFromArray($styleArray);             
            $aligns =  [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText' => true]; 
           
            $row1col1styleArray = ['font' => ['size'  => 12,'bold'  => true,'color' => array('rgb' => 'ffffff')], 
                'alignment' => $aligns,  
            ];     
            // Row 1 
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'Données obligatoires / Required Data');
                $spreadsheet->getActiveSheet()->getStyle('A1:O1')->applyFromArray($row1col1styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('333F4F'); 


                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(16, 1, 'Données obligatoires si variants / Required Data if variant sizes');
                $spreadsheet->getActiveSheet()->getStyle('p1')->applyFromArray($row1col1styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('C65911'); 

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(17, 1, 'Données optionnelles / Optional datas');
                $spreadsheet->getActiveSheet()->getStyle('Q1:R1')->applyFromArray($row1col1styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4472C6'); 

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(19, 1, 'Données spécifiques / Specifical data');
                $spreadsheet->getActiveSheet()->getStyle('S1:AF1')->applyFromArray($row1col1styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('548235'); 

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(33, 1, 'Donnée obligatoire');
                $spreadsheet->getActiveSheet()->getStyle('AG1')->applyFromArray($row1col1styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('333F4F'); 

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(34, 1, 'Donnée optionnelle');
                $spreadsheet->getActiveSheet()->getStyle('AH1')->applyFromArray($row1col1styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4472C6'); 

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(35, 1, 'Donnée obligatoire');
                $spreadsheet->getActiveSheet()->getStyle('AI1')->applyFromArray($row1col1styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('333F4F'); 

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(36, 1, 'Donnée optionnelle');
                $spreadsheet->getActiveSheet()->getStyle('AJ1')->applyFromArray($row1col1styleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4472C6');  
            //END

            // Row 2    
                // $spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(90);
                $row2colAllstyleArray = ['font' => ['size'  => 11,'bold'  => true,'color' => array('rgb' => 'ffffff')], 
                    'alignment' => $aligns,  
                ];     
                // Col A2 to O2                  
                    $spreadsheet->getActiveSheet()->getStyle('A2:O2')->applyFromArray($row2colAllstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('3E4D60'); 
                // END
                // Col P2 
                    $spreadsheet->getActiveSheet()->getStyle('P2')->applyFromArray($row2colAllstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('DB6413'); 
                // END
                // Col Q2 to R2 
                    $spreadsheet->getActiveSheet()->getStyle('Q2:R2')->applyFromArray($row2colAllstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('5982CB'); 
                // END
                // Col S2 to AF2 
                    $spreadsheet->getActiveSheet()->getStyle('S2:AF2')->applyFromArray($row2colAllstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('62983E'); 
                // END
                // Col AG2 and AI2 
                    $spreadsheet->getActiveSheet()->getStyle('AG2')->applyFromArray($row2colAllstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('3E4D60'); 
                    $spreadsheet->getActiveSheet()->getStyle('AI2')->applyFromArray($row2colAllstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('3E4D60'); 
                // END
                // Col AH2 and AJ2 
                    $spreadsheet->getActiveSheet()->getStyle('AH2')->applyFromArray($row2colAllstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('5982CB'); 
                    $spreadsheet->getActiveSheet()->getStyle('AJ2')->applyFromArray($row2colAllstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('5982CB'); 
                // END
            // END
            // Row 3   
            $spreadsheet->getActiveSheet()->mergeCells('Z3:AC3');   
                $row3colAllstyleArray = ['font' => ['size'  => 9,'bold'  => false,'color' => array('rgb' => '000000')], 
                    'alignment' => $aligns,  
                ];     
                // Col A3                 
                    $spreadsheet->getActiveSheet()->getStyle('A3:BB3')->applyFromArray($row3colAllstyleArray)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F5F5F5'); 
                // END
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 3, '13 chiffres');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(2, 3, 'Référence fabricant');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(3, 3, 'MARQUE ou MODELE Type produit - Référence produit - Caractéristiques principales');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(9, 3, '8 chiffres');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(16, 3, '1 ligne = 1 taille/pointure');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(18, 3, 'DD/MM/AAAA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(21, 3, "Réf. de l'organisme récupérant l'écotaxe");
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(25, 3, '20%');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(26, 3, "Si affichage du prix au kilo, au litre, au m² Exemple d'1 bouteille de vin de 0,75l vendue :");
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(30, 3, 'Pour les Jeux vidéo & Téléphonie');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(31, 3, 'DD/MM/AAAA');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(32, 3, 'OUI/NON');
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(35, 3, 'OUI/NON'); 
            // END 
        }   
		
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx"); 
        $writer->save($nfilep); 
        return $nfilep;
    }
    public function createXLSX_noexcelUpload($id, $newsheet,  $store_name){
        $filePathnew = public_path()."/uploads/".$store_name.".xlsx";
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePathnew);
        $req_arr = array_filter($newsheet[0], function($k) {
            return $k == 'Requis';
        }, ARRAY_FILTER_USE_BOTH   );
        $req_arr1 = [];
        foreach($req_arr as $index => $key) {
            $req_arr1[] = $index ;
        }
        foreach ($newsheet as $rowindex => $row) {
            $cellss = [];
            $zebraWhiteStyle = '';
            foreach ($row as $cellindex => $rowData) {
                $border = (new BorderBuilder())
                ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();
                $fontcolor = '000000';
                if($rowindex == 0 || $rowindex == 1) {
                    if(in_array($cellindex, $req_arr1 )){
                         $zebraWhiteStyle = (new StyleBuilder())
                        ->setBackgroundColor(Color::rgb(255, 153, 0))
                        ->setFontColor($fontcolor)
                        ->setBorder($border)
                        ->setFontName('Calibri')
                        ->build();
                    }else {
                         $zebraWhiteStyle = (new StyleBuilder())
                        ->setBackgroundColor(Color::rgb(0, 192, 0))
                        ->setFontColor($fontcolor)
                        ->setBorder($border)
                        ->setFontName('Calibri')
                        ->build();
                    }
                } else{
                    $zebraWhiteStyle = (new StyleBuilder())
                    ->setBackgroundColor(Color::WHITE)
                    ->setFontColor($fontcolor)
                    ->setBorder($border)
                    ->setFontName('Calibri')
                    ->build();
                }

                $cellss[] =   WriterEntityFactory::createCell($rowData, $zebraWhiteStyle);

            }
            $rowDataa = WriterEntityFactory::createRow($cellss );
            $writer->addRow($rowDataa);
        }

        // foreach ($newsheet as $row) {
        //     $rowFromValues = WriterEntityFactory::createRowFromArray($row );
        //     $writer->addRow($rowFromValues);
        // }
        $writer->close();
        return $filePathnew;
    }
    public function assigned_gamelist(Request $request, $id ){

        $data=[];
        $remove_fields = array('readonly', 'image', 'gallery');
        $store_fildsHeaders = StoreField::with('master_fields');
        $store_fildsHeaders = $store_fildsHeaders->whereHas('master_fields', function($query) use($remove_fields, $id) {
            $query->whereNotIn('master_fields.master_field_type', $remove_fields )->where('store_id', $id  );
        });
        $data['headers'] = $store_fildsHeaders->orderBy('field_order', 'ASC')->get();

        $dataheaders = $data['headers'];
        $ajaxResponce = [];
        $ajaxResponce['columns'] = [];
        $ajaxResponce['count_columns'] = count($dataheaders->toArray());
        $maxcase = [];
        $csv_headers = [];
        $is_required = [];
        $floatFiled=[];
        $UnicFiled=[];
        $m=1;

        if(count($dataheaders->toArray()) > 0){
            foreach($dataheaders as $k=>$header){

                if($header->master_fields->master_field_type == 'float'){
                    $floatFiled[$header->field_id] = 'field_'.$header->field_id;
                }
                if($header->master_fields->master_field_isunic == 'yes'){
                    $UnicFiled[$header->field_id] = 'field_'.$header->field_id;
                }
                $maxcase[] ='MAX(CASE WHEN `field_id` = '.$header->field_id.' THEN `field_value` END) as field_'.$header->field_id;
            }
            $maxcasestr = implode(", ",$maxcase);
            foreach($dataheaders as $k=>$header){
                $ajaxResponce['columns'][$m] = $header ;
                $csv_headers[] = $header->field_personal_name ;
                if( $header->master_fields->master_field_required == 'yes' ){
                    $is_required[] = 'Requis';
                }else{
                    $is_required[] = 'Facultatif';
                }
                $m++;
            }
        }else{
            $data['headers'] = Fields::whereNotIn('master_field_type',  $remove_fields)->orderBy('f_order', 'ASC')->get();
            $dataheaders = $data['headers'];
            foreach($dataheaders as $k=>$header){
                if($header->master_field_type == 'float'){
                    $floatFiled[$header->id] = 'field_'.$header->id;
                }
                if($header->master_field_isunic == 'yes'){
                    $UnicFiled[$header->id] = 'field_'.$header->id;
                }
                $maxcase[] ='MAX(CASE WHEN `field_id` = '.$header->id.' THEN `field_value` END) as field_'.$header->id;
            }
            $maxcasestr = implode(", ",$maxcase);
            foreach($dataheaders as $k=>$header){
                $ajaxResponce['columns'][$m] = $header ;
                $csv_headers[] = $header->master_field_name ;
                if( $header->master_field_required == 'yes' ){
                    $is_required[] = 'Requis';
                }else{
                    $is_required[] = 'Facultatif';
                }
                $m++;
            }

        }
        // echo '<pre>'; print_r( $maxcasestr ); die;
        $assigned_games = StoreGame::select( "game_id")->where('store_id', $id)->get()->pluck('game_id')->toArray();

        $all_tak = GameFields::select(DB::raw($maxcasestr))->whereIn('game_id', $assigned_games )->groupBy('f_order')->get();

        foreach($all_tak as $k=>$field){
            foreach($UnicFiled as $nameid=>$uname){
                $storeval = StoreField::where('store_id', $id)->where('field_id', $nameid)->get()->toArray();
                if(isset($storeval[0]['field_unicPersonal_name'])){
                    $all_tak[$k]->$uname=$storeval[0]['field_unicPersonal_name'];
                }

            }
        }
        $m=1;
        foreach($dataheaders as $k=>$header){
            $ajaxResponce['columns'][$m] = $header ;
            $m++;
        }

        $ajaxResponce['data'] = $all_tak;
        if($request->input('download_csv') == 'csv_download' ) {

            $all_tak = GameFields::select( DB::raw($maxcasestr))->whereIn('game_id', $assigned_games )->groupBy('f_order')->get()->toArray();

            foreach($all_tak as $k=>$field){
                foreach($UnicFiled as $nameid=>$uname){

                    $storeval = StoreField::where('store_id', $id)->where('field_id', $nameid)->get()->toArray();
                    if(isset($storeval[0]['field_unicPersonal_name'])){
                        $all_tak[$k][$uname]=$storeval[0]['field_unicPersonal_name'];
                    }
                }
            }
            $static_csv = Store::select('store_name', 'static_csv', "indentifier")->where('id', $id)->get()->toArray();
            $csvFile = $static_csv[0]['static_csv'];
            $storeIndentifier = $static_csv[0]['indentifier'];
            $store_name = $static_csv[0]['store_name'];

            $fileName = basename($csvFile);
            $filePath = public_path()."/store_csvs/". $id."/".$fileName;
            $filePathnew = public_path()."/store_csvs/". $id."/new-".$fileName;

            $newHeader=[];
            $newHeader[]=$csv_headers;
            if($storeIndentifier == 1 ){
                $isReq_header[]=$is_required;
                $newdata_arr=array_merge($isReq_header,$newHeader);
                $newdata=array_merge($newdata_arr,$all_tak);
            }else{

                $newdata=array_merge($newHeader,$all_tak);
            }
 
            $responce = '';
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            if($csvFile){
                $this->createXLSX($filePath, $id, $newdata, $storeIndentifier);

                if($filePath && $filePathnew){
                    if(!file_exists(public_path().'/store_csvs/'.$id.'/download')) {
                        mkdir(public_path().'/store_csvs/'.$id.'/download', 0777, true);
                    }
                    $objPHPExcel1 = IOFactory::load($filePath); 
                    $objPHPExcel2 = IOFactory::load($filePathnew); 
                    foreach($objPHPExcel1->getSheetNames() as $sheetName) {
                        $sheet = $objPHPExcel1->getSheetByName($sheetName);
						if($sheetName == 'MATRICE'){
                            $objPHPExcel1->setActiveSheetIndexByName('MATRICE');
                            $mathCol = array('AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE');
                            foreach($mathCol as $colName){
                                for($i=3;$i<=235;$i++){
                                    $objPHPExcel1->getActiveSheet()->setCellValue(
                                        $colName.$i,
                                        '=IF(OR(I'.$i.'="",R'.$i.'=""),0,AS'.$i.'*INDIRECT("SR"&I'.$i.'&R'.$i.')*INDIRECT("Niv"&S'.$i.'))'
                                    );
                                }
                            }    
                        }
                        if($sheetName == 'Multi-Colis'){
                            $objPHPExcel1->setActiveSheetIndexByName('Multi-Colis');
                            $row2colAllstyleArray = ['font' => ['size'  => 10,'bold'  => true,'color' => array('rgb' => 'ffffff')]  ];  
                            $objPHPExcel1->getActiveSheet()->getStyle('A2:AW2')->applyFromArray($row2colAllstyleArray) ;
                        }
                        $sheet->setTitle($sheet->getTitle() );
                        $objPHPExcel2->addExternalSheet($sheet);
                    }  
                    $objWriter = IOFactory::createWriter($objPHPExcel2, 'Xlsx'); 
                    if($storeIndentifier == 1){
                        $objWriter->save(public_path()."/store_csvs/". $id."/download/store-".$fileName); 
                        $responce =  public_path()."/store_csvs/". $id."/download/Amazon - Terminator and other.".$ext ;
                    }elseif ($storeIndentifier == 2) {
                        $objWriter->save(public_path()."/store_csvs/". $id."/download/store-".$fileName); 
                        $responce =  public_path()."/store_csvs/". $id."/download/fnac-store-example.".$ext ;
                    }elseif ($storeIndentifier == 3) {
                        $objWriter->save(public_path()."/store_csvs/". $id."/download/store-".$fileName); 
                        $responce =  public_path()."/store_csvs/". $id."/download/Micromania -Shadow definitive edition.".$ext ;
                    }elseif ($storeIndentifier == 4) {
                        $objWriter->save(public_path()."/store_csvs/". $id."/download/store-".$fileName); 
                        $responce =  public_path()."/store_csvs/". $id."/download/Matrice Auchan.".$ext ;
                    }elseif ($storeIndentifier == 5) {
                        $objWriter->save(public_path()."/store_csvs/". $id."/download/store-".$fileName); 
                        $responce =  public_path()."/store_csvs/". $id."/download/leclerc-store-example.".$ext ;
                    }elseif ($storeIndentifier == 6) {
                        $objWriter->save(public_path()."/store_csvs/". $id."/download/store-".$fileName); 
                        $responce =  public_path()."/store_csvs/". $id."/download/cdiscount-csv-example.".$ext ;
                    }elseif ($storeIndentifier == 7) {
                        $objWriter->save(public_path()."/store_csvs/". $id."/download/store-".$fileName); 
                        $responce =  public_path()."/store_csvs/". $id."/download/carrefour-csv-example.".$ext ;
                    }elseif ($storeIndentifier == 8) {
                        $objWriter->save(public_path()."/store_csvs/". $id."/download/store-".$fileName); 
                        $responce =  public_path()."/store_csvs/". $id."/download/Matrice Cultura.".$ext ;
                    }
                    if($storeIndentifier ==1 || $storeIndentifier == 7 || $storeIndentifier == 5 || $storeIndentifier == 4 || $storeIndentifier == 6 || $storeIndentifier == 3 || $storeIndentifier == 2 || $storeIndentifier == 8){
						$storeFilePath = public_path()."/store_csvs/". $id."/download/store-".$fileName;
						$responce= $this->MergeCreateNewFile($storeFilePath,$id,$storeIndentifier,$fileName);
					}
                    // $responce = $objWriter->save(public_path()."/store_csvs/". $id."/dynamicwith_".strtolower(str_replace(' ','_', $store_name)).'.'.$ext); 
                } 
            }else{
                $responce = $this->createXLSX_noexcelUpload($id, $newdata, $store_name);
            } 
            if ($storeIndentifier == 4 && count($all_tak) == 0) { 
                $responce =  public_path()."/store_csvs/". $id."/".$fileName ;
                return Response::download($responce);
            }else{
                return Response::download($responce);
            }
            
        }
        return response()->json( $ajaxResponce ); exit;
    }
}
