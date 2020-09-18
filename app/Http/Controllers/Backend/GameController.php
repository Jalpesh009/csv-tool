<?php

namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fields;
use App\Models\Store;
use App\Models\Game;
use App\Models\GameFields;
use App\Models\StoreGame;
use App\Models\StoreField;
use DB;
use Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Requests\Backend\AddGameRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

use Illuminate\Support\Facades\Response;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;

use Box\Spout\Common\Type;
use Illuminate\Support\Facades\View;

/**
 * Class GameController.
 */
class GameController extends Controller
{
    public function __construct()
    {
        View::share('menu_item', 'game');
        $this->middleware('permission:game-list');
        $this->middleware('permission:game-create', ['only' => ['create','store']]);
        $this->middleware('permission:game-edit', ['only' => ['show','update']]);
        $this->middleware('permission:game-delete', ['only' => ['destroy']]);
    }
    public function index(Request $request )
    {

        $headers1 = Fields::where('master_field_show', 'yes')->where('master_field_slug', 'game_logo')->get();
        $headers2 = Fields::where('master_field_show', 'yes')->where('master_field_slug', 'game_name')->get();
        $notin = ['game_name', 'game_logo'];
        $headers3 = Fields::where('master_field_show', 'yes')->whereNotIn('master_field_slug', $notin)->orderBy('f_order', 'ASC')->get();  
       
        $headers11 = $headers1->merge($headers2);
        $headers = $headers11->merge($headers3); 
        // $headers = Fields::where('master_field_show', 'yes')->orderBy('f_order', 'ASC')->get(); 
        $headers_count = Fields::where('master_field_show', 'yes')->orderBy('f_order', 'ASC')->get()->toArray();
        $remove_fields = array('readonly', 'image', 'gallery');
         
        // echo '<pre>';
        // print_r($headers); die;
        $fields = Fields::orderBy('id', 'ASC')->get();
        $pagetitle = 'Manage Game';
        $gameFields = GameFields::groupBy('game_id')->get();
        $count_gameFields = count($gameFields);
        $stores = Store::orderBy('id','DESC')->get();
        $count_stores = count($stores); 
        return view('backend.game.index',compact('fields', 'headers','headers_count', 'pagetitle', 'count_gameFields', 'stores', 'count_stores'));
    }
    public function create()
    {
        $fields = Fields::all();
        return view('backend.game.create', compact('fields'));
    }
    public function store(AddGameRequest $request, Validator $validator){
        $fields =  $request->all() ; 
        $game = new Game();
        $game->save();
        if( !empty($game) ){
            if( $request->input('multi_images') !== null ){
                foreach ($request->input('multi_images')  as $key => $value) {
                    if($request->hasfile('filename')) { 
                        foreach($request->file('filename') as $image)
                        {
                            $name=$image->getClientOriginalName();
                            $image->move(public_path().'/game_images/'.$game->id.'/', $name);
                            $data[] = url('/public/game_images').'/'.$game->id.'/'.$name;
                        }
                        $fields['input']['game'][$value] = json_encode($data );
                    }else{
                        $fields['input']['game'][$value] = Null;
                    }
                }
            }
            if( $request->input('imagesids') !== null){
                foreach ($request->input('imagesids')  as $key => $value) {
                    if( $request->hasFile('photos_'.$value) ) {
                        $game_img = $request->file('photos_'.$value);
                        $extension = $game_img->getClientOriginalExtension();
                        $pathImg = Storage::disk('public')->put($game_img->getClientOriginalName(), File::get($game_img));
                        $imagefullpath = url('/public/uploads/' . $game_img->getClientOriginalName());
                        $fields['input']['game'][$value] = '<img src="' .$imagefullpath .'" class="game_logo_img">';
                    }
                    else{
                        $field_isGameLogo = Fields::where('master_field_slug', 'game_logo')->where('id', $value)->get()->toArray();                         
                        if(count($field_isGameLogo) > 0){ 
                            $fields['input']['game'][$value] = '<img src="' .url('/public/uploads/default-game.jpg'). '" class="game_logo_img">';
                        } else{
                            $fields['input']['game'][$value] = NULL;
                        } 
                        
                    }
                }

            }
            // die;
            $maxOrderValue = GameFields::max('f_order'); 
            $fields_count = count(array_filter($fields['input']['game']));
            $total_fields = count($fields['input']['game'] ); 
            foreach ($fields['input'] as $key => $index) {
                foreach ($index  as $key1 => $value) {
                    $game_fields = new GameFields();
                    $game_fields->game_id = $game->id;
                    $game_fields->f_order = ($maxOrderValue + 1);
                    $game_fields->field_id = $key1;
                    $fieldss  = Fields::select('id')->where('master_field_type', 'readonly')->get()->toArray(); 
                    if( count($fieldss) > 0 &&  $fieldss[0]['id'] == $key1 ){
                        $game_fields->field_value = $fields_count .'/'.  $total_fields ;
                    }else{
                        $game_fields->field_value = $value;
                    } 
                    $game_fields->save();
                }
            }
            return redirect()->route('admin.game.index')->with('message', 'Game added successfully!');
        } else{
            return redirect()->route('admin.game.index')->with('errors', $validator->errors());
        }
    }

    public function show(Request $request, $id){
        $game_fields = GameFields::where('game_id', $id)->with('master_fields')->orderBy('field_id', 'ASC')->get()->toArray();
        $pagetitle = 'Manage Game';
        $game_id = $id;
        $game_iddd = $request->input('game_id') ;
        if($game_iddd){
            Game::find($game_iddd)->delete();
            GameFields::where('game_id', $game_iddd)->delete();
            StoreGame::where('game_id', $game_iddd)->delete();
            $totaldata = GameFields::select('game_id')->groupBy('game_id')->get()->toArray() ;
            $i=1;
            foreach($totaldata as $k=>$gameField){
                GameFields::where('game_id', $gameField['game_id'])->update(['f_order' => $i]);

                $i++;
            }
            return redirect()->route('admin.game.index');
        }
        return view('backend.game.show', compact( 'game_fields', 'id', 'pagetitle','game_id'));

    }
    public function edit( $id){
        $pagetitle = 'Manage Game';
        $game_fields = GameFields::where('game_id', $id)->with('master_fields')->orderBy('field_id', 'ASC')->get()->toArray();
        // $game_fields = Fields::all()->toArray();
        return view('backend.game.edit', compact( 'game_fields', 'id', 'pagetitle'));
    }
    public function update(Request $request, $id){
        $fields = $request->all();
        
        $field_isGameLogo = Fields::select('id')->where('master_field_slug', 'game_logo')->get()->first()->toArray();    
        $field_logoid = $field_isGameLogo['id'];
        $field_gamelogofieldid = GameFields::where('game_id', $id)->where('field_id',$field_logoid)->get()->first()->toArray(); 
        // echo '<pre>'; print_r($fields['input']['game']); die;   
        if( !empty($fields) ){ 
            foreach ($request->input('imagesids')  as $key => $value) { 
                if( $request->hasFile('photos_'.$value) ) {  
                    $time = time();
                    $game_img = $request->file('photos_'.$value);
                    $extension = $game_img->getClientOriginalExtension();
                    $pathImg = Storage::disk('public')->put($time.$game_img->getClientOriginalName(), File::get($game_img));
                    $imagefullpath = url('/public/uploads/' .$time.$game_img->getClientOriginalName());
                    $fields['input']['game'][$value] = '<img src="' .$imagefullpath .'" class="game_logo_img">'; 
                } else if( $request->input('edit_image_'.$value)  != NULL ){ 
                    $fields['input']['game'][$value] = '<img src="' .url('/public/uploads/' .$request->input('edit_image_'.$value)). '" class="game_logo_img">';
                }  
            }
                      
            $multiimagepaths =array();
            $time = time();  
            $mId = $request->input('multi_images');
            $multipleImages = [];
            if($request->input('multi_images') ){
                foreach ($request->input('multi_images')  as $key => $value) { 
                    if($request->hasfile('filename')) { 
                        foreach($request->file('filename') as $image) { 
                            $name=$time.'-'.$image->getClientOriginalName();
                            $image->move(public_path().'/game_images/'.$request->input('gameId').'/', $name);
                            $multipleImages[] = url('/public/game_images').'/'.$request->input('gameId').'/'.$name ; 
                        } 
                        if( $request->input('multi_images_imgs') !== Null){ 
                            $arr = $request->input('multi_images_imgs'); 
                            $arr_images = array_merge($arr, $multipleImages); 
                            $fields['input']['game'][$value] = json_encode( $arr_images) ;
                        }else{ 
                           $fields['input']['game'][$value] = json_encode( $multipleImages) ; 
                        } 
                    } else if( $request->input('multi_images_imgs')  != null){ 
                        $ar_imgs = $request->input('multi_images_imgs');
                        foreach($ar_imgs as $imagename) { 
                            $multipleImages[] =  $imagename ;
                        } 
                        $fields['input']['game'][$value] = json_encode( $multipleImages) ;
                    }else{
                          $fields['input']['game'][$value] = NULL;  
                    }
                }
            }

            foreach ($fields['input'] as $key => $index) {

                foreach ($index  as $key1 => $value) {
                    $game_fields  = GameFields::where('id', $key1)->first();
                    if(  isset($fields['multi_images'] ) ){
                        // if (in_array($key1, $fields['multi_images'])){
                          $game_fields->field_value =   $value ;
                        // }
                    }else{
                      $game_fields->field_value = $value;
                    }
                    $game_fields->save();
                }
            }
        }
        return redirect()->to('admin/game/'.$id.'/edit')->with('success','Game updated successfully');
    }

    public function createXLSX($newsheet, $store_id, $storename){
        $filePathnew = public_path()."/uploads/".$store_id."-".$storename.".xlsx";
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
        
        $writer->close();
        return $filePathnew;
    }

    public function get_gameslisting( Request $request, $type ) {

        if($type == 'ajax'){
            $data=[];
            $data['headers'] = Fields::where('master_field_show', 'yes')->orderBy('f_order', 'ASC')->get();
            if($request->input('action') == 'edit'){

                $newReorderData = $request->input('data');
                $excludeIds = [];
                foreach($newReorderData as $rowOrder=>$newOrder){

                    $taskIds = GameFields::select('id')->where('f_order',str_replace("row_","",$rowOrder))->get()->pluck('id')->toArray();
                    $taskIds1 = array_values(array_diff($taskIds,$excludeIds));

                    GameFields::whereIn('id', $taskIds1)->update(['f_order'=>$newOrder['f_order']]);
                    $excludeIds = array_unique(array_merge($excludeIds,$taskIds));
                }
            }
            $maxcase = [];
            $dataheaders = $data['headers'];

            $floatFiled=[];
            $imgFiled=[];
            $nameFiled=[];
            $fieldsFiled=[];
            $kk = 2;  
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
                    // echo  '<pre> sdsd' ; print_r($maxcase); die;
                }
                if($header->master_field_type == 'readonly' ){
                    $fieldsFiled[$header->id] = 'field_'.$header->id;
                } 
                if($header->master_field_slug != 'game_name' && $header->master_field_slug != 'game_logo'){
                    $maxcase[$kk] ='MAX(CASE WHEN `field_id` = '.$header->id.' THEN `field_value` END) as field_'.$header->id;
                    $kk++;
                }
                
            } 
              
            ksort($maxcase);
            $maxcasestr = implode(", ",$maxcase);
            // $all_tak = DB::select(DB::raw("SELECT f_order, id,  $maxcasestr FROM game_fields GROUP BY `f_order`"));
            $all_tak = GameFields::select("f_order", "game_id", DB::raw($maxcasestr))->groupBy('f_order')->get(); 
            
            $j = 0;
            foreach($all_tak as $k=>$field){ 
             
                $selectedFields = StoreGame::select("store_id")->where('game_id',$field->game_id)->get()->toArray();
                
                foreach($imgFiled as $imgid=>$iname){ 
                    $all_tak[$k]->$iname='<a href="'. url('/') .'/admin/game/'.$field->game_id.'/view" >'.  $field->$iname .'</a>'; 
                }
                foreach($nameFiled as $nameid=>$gname){
                    $all_tak[$k]->$gname='<a href="'. url('/') .'/admin/game/'.$field->game_id.'/view" class="gameName">'.  $field->$gname .'</a>';
                }
                foreach($floatFiled as $fid=>$fname){
                    if (is_numeric($field->$fname)){
                        $price = '€ '. number_format($field->$fname, 2) ;
                    }else{
                        $price = $field->$fname;
                    }
                    $all_tak[$k]->$fname="<span class='text-success font-weight-bold'>".  $price .'</span>';
                }
                foreach($fieldsFiled as $nameid=>$filedname){
                    $all_tak[$k]->$filedname=$field->$filedname;
                } 
                $all_tak[$k]->DT_RowId="row_".$field->f_order;
                $all_tak[$k]->assigned_stores = $selectedFields;
            }  
           // die;
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
        }elseif($type == 'assign_storegame'){
            StoreGame::where('game_id', $request->input('game_id'))->delete();
            $ids = $request->input('store_ids_arr');
            $ids_stores = explode(",", $ids);
            if($ids_stores != null ){
                foreach ($ids_stores as $value) {
                    if($value != null && !empty($value)){
                        $storegameOld = StoreGame::where('store_id', $value)->where('game_id', $request->input('game_id'))->get()->toArray();
                        $count_storegame = count($storegameOld);
                        if( $count_storegame == 0){
                            $storegame = new StoreGame();
                            $storegame->store_id = $value;
                            $storegame->game_id = $request->input('game_id');
                            $storegame->save();
                        }
                    }
                }
                return redirect()->route('admin.game.index')->with('success', 'Retailer assigned successfully to game.');
            }
        }elseif($type == 'csv_storegame' &&  $request->input('downlod_gamecsv') == 'Download CSV'){

            $data=[];
            $remove_fields = array('readonly', 'image', 'gallery');
            $store_fildsHeaders = StoreField::with('master_fields');
            $store_fildsHeaders = $store_fildsHeaders->whereHas('master_fields', function($query) use($remove_fields, $request) {
                $query->whereNotIn('master_fields.master_field_type', $remove_fields )->where('store_id', $request->input('csv_store_id')  );
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
                    $storeval = StoreField::where('store_id', $request->input('csv_store_id'))->where('field_id', $nameid)->get()->toArray(); 
                    if(isset($storeval[0]['field_unicPersonal_name'])){
                        $all_tak[$k][$uname]=$storeval[0]['field_unicPersonal_name'];
                    }
                }
            }
            $newHeader=[];
            $newHeader[]=$csv_headers; 
            $isReq_header[]=$is_required;
            $store = Store::select("store_name")->where('id', $request->input('csv_store_id'))->first();
            $newdata_arr=array_merge($isReq_header,$newHeader);
            $newdata=array_merge($newdata_arr,$all_tak);  
            // $newdata=array_merge($newHeader,$all_tak); 
            $responce = $this->createXLSX($newdata, $request->input('csv_store_id'), $store->store_name);
            return Response::download($responce);

        }
        return view('backend.game.index', $data );
    }

    public function preview_gameData(Request $request, $game_id, $store_id, $type ) {
        $data=[];
        $remove_fields = array('readonly', 'image', 'gallery');
        if($type == 'previewcsv'){

            $store_fildsHeaders = StoreField::with('master_fields');
            $store_fildsHeaders = $store_fildsHeaders->whereHas('master_fields', function($query) use($remove_fields, $store_id) {
                $query->whereNotIn('master_fields.master_field_type', $remove_fields )->where('store_id', $store_id  );
            });
            $data['headers'] = $store_fildsHeaders->orderBy('field_order', 'ASC')->get();
          
            $maxcase = [];
            $dataheaders = $data['headers'];
         
            $ajaxResponce = [];
            $floatFiled=[];
            $ajaxResponce['count_columns'] = count($dataheaders->toArray());
            $UnicFiled=[];
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
            }

            $maxcasestr = implode(", ",$maxcase);
            $all_tak = GameFields::select( DB::raw($maxcasestr))->where('game_id', $game_id )->groupBy('f_order')->get();
            foreach($all_tak as $k=>$field){
                foreach($floatFiled as $fid=>$fname){
                    if (is_numeric($field->$fname)){
                        $price = '€ '. number_format($field->$fname, 2) ;
                    }else{
                        $price = $field->$fname;
                    }
                }
                foreach($UnicFiled as $nameid=>$uname){ 
                    $storeval = StoreField::where('store_id', $store_id)->where('field_id', $nameid)->get()->toArray(); 
                    if(isset($storeval[0]['field_unicPersonal_name'])){
                        $all_tak[$k]->$uname=$storeval[0]['field_unicPersonal_name'];
                    }
                }
            }

            $ajaxResponce['columns'] = [];
            $m=1;
            foreach($dataheaders as $k=>$header){
                $ajaxResponce['columns'][$m] = $header ;
                $m++;
            } 
            $ajaxResponce['data'] = $all_tak;
            return response()->json( $ajaxResponce );exit;
        }else if($type == 'emailstore'){
            $data=[];
            $remove_fields = array('readonly', 'image', 'gallery');
            $store_fildsHeaders = StoreField::with('master_fields');
            $store_fildsHeaders = $store_fildsHeaders->whereHas('master_fields', function($query) use($remove_fields, $store_id) {
                $query->whereNotIn('master_fields.master_field_type', $remove_fields )->where('store_id', $store_id  );
            });
            $data['headers'] = $store_fildsHeaders->orderBy('field_order', 'ASC')->get();
            $maxcase = [];
            $dataheaders = $data['headers'];

            $ajaxResponce = [];
            $ajaxResponce['columns'] = [];
            $m=1;
            $csv_headers = [];
            $UnicFiled=[];
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
                    $m++;
                }
            }

            $all_tak = GameFields::select( DB::raw($maxcasestr))->where('game_id', $game_id )->groupBy('f_order')->get()->toArray();
            foreach($all_tak as $k=>$field){ 
                foreach($UnicFiled as $nameid=>$uname){ 
                    $storeval = StoreField::where('store_id', $store_id)->where('field_id', $nameid)->get()->toArray(); 
                    if(isset($storeval[0]['field_unicPersonal_name'])){
                        $all_tak[$k][$uname]=$storeval[0]['field_unicPersonal_name'];
                    }
                }
            }

            $newHeader=[];
            $newHeader[]=$csv_headers;
            $store = Store::select("store_name")->where('id', $store_id)->first();
            $newdata=array_merge($newHeader,$all_tak);
            $responce = $this->createXLSX($newdata, $store_id, $store->store_name);

            $to_email = $request->input('email');
            $to_name = '';
            $data=array();


                $filePath = public_path()."/uploads/".$store_id."-".$store->store_name.".xlsx";
                // echo  $filePath ; die;

            Mail::send('backend.emails.sendaddgamerequest', $data, function($message) use ($to_name, $to_email, $filePath) {
                $message->to($to_email, $to_name);
                $message->subject('Add Store to Game'); 
                $message->attach( $filePath );



            });
             if (Mail::failures()) {
                return Response::json(array('status' => 'fail'));
            }else{
                return Response::json(array('status' => 'ok'));
            }
        }

        return view('backend.game.index', $data );

    }

}
