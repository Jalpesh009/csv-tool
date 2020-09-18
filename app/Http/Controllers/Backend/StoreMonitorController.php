<?php

namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fields;
use App\Models\Store;
use App\Models\Game;
use App\Models\StoreGame;
use App\Models\GameFields;
use DB;
use Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;   
use Illuminate\Support\Facades\View;
/**
 * Class GameController.
 */
class StoreMonitorController extends Controller
{
    public function __construct()
    {
        View::share('menu_item', 'storemonitoring');
		 $this->middleware('permission:monitoring-list',['only' => ['index']]);
        //$this->middleware('permission:monitoring-create', ['only' => ['create','store']]);
        //$this->middleware('permission:monitoring-edit', ['only' => ['edit','update']]);
        //$this->middleware('permission:monitoring-delete', ['only' => ['destroy']]);
        //$this->middleware('permission:monitoring-show',['only' => ['show']]);
        
    }
    public function index(Request $request  )
    {   
        $headers = Fields::where('master_field_slug', 'game_logo')->orWhere('master_field_slug', 'game_name')->orWhere('master_field_slug', 'game_ean_number')->orderBy('f_order', 'ASC')->get(); 

        $headers1 = Fields::where('master_field_slug', 'game_logo')->get();
        $headers2 = Fields::where('master_field_slug', 'game_name')->get();
        $headers3 = Fields::where('master_field_slug', 'game_ean_number')->get();
        // $notin = ['game_name', 'game_logo'];
        // $headers4 = Fields::where('master_field_show', 'yes')->whereNotIn('master_field_slug', $notin)->orderBy('f_order', 'ASC')->get();  
       
        $headers11 = $headers1->merge($headers2);
        $headers = $headers11->merge($headers3); 

        return view('backend.storemonitor.index', compact(  'headers') );
    }
	public function sendemailrequesttoaddgame(Request $request){
		$to_email = $request->input('storeEmail');
		$to_name = '';
		//$to_email = 'amit.albiorix@gmail.com';
		$data=array();
		Mail::send('backend.emails.sendaddgamerequest', $data, function($message) use ($to_name, $to_email) {
			$message->to($to_email, $to_name);
			$message->subject('Add game to store');
			
		});
		 if (Mail::failures()) {
			return Response::json(array('status' => 'fail')); 
		}else{
			return Response::json(array('status' => 'ok')); 
		}
		
	}
	
	public function storelist($gameid){
		$data=[];
		$data['gameId']=$gameid;
		$getStoreIds = StoreGame::Select('store_id')->where('game_id',$gameid)->get()->pluck('store_id')->toArray();
		$data['getallGameStore'] = Store::WhereIn('id',$getStoreIds)->get();
		//print_r($getallGameStore);
		return view('backend.storemonitor.storelist',$data)->render();
		 //return view('backend.storemonitor.index', $data );
	}
     public function game_list(Request $request, $typegame )
    {   
        $data=[];  
        $data['headers'] = Fields::where('master_field_slug', 'game_logo')->orWhere('master_field_slug', 'game_name')->orWhere('master_field_slug', 'game_ean_number')->orderBy('f_order', 'ASC')->get(); 

        if($typegame == 'ajaxgame'){
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
            $dataheaders = Fields::where('master_field_slug', 'game_logo')->orWhere('master_field_slug', 'game_name')->orWhere('master_field_slug', 'game_ean_number')->orderBy('f_order', 'ASC')->get(); 

            $floatFiled=[];
            $imgFiled=[];
            $nameFiled=[];
            $i = 2;
            foreach($dataheaders as $k=>$header){
                 
                if($header->master_field_type == 'float'){
                    $floatFiled[$header->id] = 'field_'.$header->id;
                }
                
                if($header->master_field_slug == 'game_logo' && $header->master_field_type == 'image'){
                    $imgFiled[$header->id] = 'field_'.$header->id; 
                    $maxcase[0] ='MAX(CASE WHEN `field_id` = '.$header->id.' THEN `field_value` END) as field_'.$header->id;
                }
                if($header->master_field_slug == 'game_name' ){
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
            // $all_tak = DB::select(DB::raw("SELECT f_order, id,  $maxcasestr FROM game_fields GROUP BY `f_order`"));
            $all_tak = GameFields::select("f_order", "game_id", DB::raw($maxcasestr))->groupBy('f_order')->get();  
           
            foreach($all_tak as $k=>$field){

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
            return response()->json( $ajaxResponce);exit; 
        }
   
        return view('backend.storemonitor.index', $data );
    }
   
}
