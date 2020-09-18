<?php

namespace App\Console\Commands;

use App\Models\Fields;
use App\Models\Store;
use App\Models\GameFields;
use App\Models\StoreGame;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
class Daily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check availibility and url';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		//get ean field id 
		$eanFieldId = Fields::where('master_field_slug','game_ean_number')->first();
		$eanFieldId = $eanFieldId->id;
		
		//get all store
		$allStoreIds = Store::Select('id','indentifier')->with('store_games')->whereIn('indentifier',[1,2,3,4,5,6,7,8])->get()->toArray();	
		foreach($allStoreIds as $store_games){
			$indentifier = $store_games['indentifier'];
			if(!empty($store_games['store_games'])){
				foreach($store_games['store_games'] as $sgame){
					if($sgame['availability_url'] == ''){
						//get ean value
						$store_id = $sgame['store_id'];
						$game_id = $sgame['game_id'];
						$ean = GameFields::Select('field_value')->where('game_id',$game_id)->where('field_id',$eanFieldId)->first()->toArray();
						$eanNumber = $ean['field_value'];
						
						$client = new \GuzzleHttp\Client();
						try {
							$response = $client->request('POST', 'http://51.15.228.122:5000/get-data', [
								'form_params' => ['id' => $indentifier,'ean' => $eanNumber,]
							]);
							if($response->getStatusCode() ==200){
								$responseData = json_decode($response->getBody()->getContents());
								$storegame = StoreGame::where('store_id',$store_id)->where('game_id',$game_id)->first();
								$storegame->availability_url = $responseData->url;
								$storegame->availability = $responseData->product_availability;
								if($storegame->save()){
									echo "=================== \n" ;
									echo "Update data ".$indentifier." and EAN ".$eanNumber." store \n";
									echo "<pre>Result  ";  print_r($responseData);
									echo "=================== \n" ;	
								}else{
									echo "Issue with update data ".$indentifier." and EAN ".$eanNumber." store \n";	
								}
							}
							//echo $response->getStatusCode();
							//echo "<pre>aa";  print_r();die;
						} catch (\GuzzleHttp\Exception\ClientException $e) {
							/*echo "bb==".\GuzzleHttp\Psr7\str($e->getRequest());
							if ($e->hasResponse()) {
								echo "cc". \GuzzleHttp\Psr7\str($e->getResponse());
							}*/
							//echo "aaa=".\GuzzleHttp\Psr7\str($e->getRequest());
							//echo "bb==".\GuzzleHttp\Psr7\str($e->getResponse());
						}
					}
				}
			}
		}
		echo "Succes";die;					
        
    }
}
