<?php

use App\Http\Controllers\Backend\DashboardController;

// All route names are prefixed with 'admin.'.

// Fields Routes
Route::redirect('/', '/admin/dashboard', 301);
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('masterfields','FieldsController'); 

Route::get('masterfields/create','FieldsController@create');
Route::post('masterfields/create','FieldsController@create');
Route::post('masterfields/{id}/delete','FieldsController@destroy'); 
Route::post('masterfields/edit','FieldsController@edit');
Route::delete('masterfields/{id}/delete', 'FieldsController@destroy');
Route::post('masterfields/deleteall','FieldsController@deleteAll')->name('deleteAll'); 
// Route::delete('masterfields', 'FieldsController@deleteAll'); 

// Stores Routes 
Route::resource('store','StoreController');  
Route::get('store/{id}/view','StoreController@show')->name('store.show') ;  
Route::post('store/store','StoreController@store');
Route::get('store/{id}/delete','StoreController@destroy');  
Route::delete('store/{id}/delete','StoreController@destroy') ;    
Route::any('store/{id}/edit/','StoreController@update')->name('update');
Route::post('store/{id}/view/new', 'StoreController@update_manage_fields' )->name('update_manage_fields');   
Route::post('store/{id}/view/addField','StoreController@single_add_field')->name('single_add_field');
Route::post('store/{id}/view/removeField','StoreController@single_remove_field')->name('single_remove_field');
Route::get('store/{id}/view/fieldslist/{storetype}','StoreController@get_fieldslisting')->name('get_fieldslisting');
Route::post('store/{id}/view/fieldslist/storeajax','StoreController@get_fieldslisting')->name('get_fieldslisting'); 
Route::get('store/{id}/view/{storetype}','StoreController@gamelist')->name('gamelist'); 
Route::post('store/{id}/view/gamelistss','StoreController@gamelist')->name('gamelist');  
Route::post('store/{id}/view/csv_storegame','StoreController@gamelist')->name('csv.gamelist');  
Route::get('store/{id}/games','StoreController@assigned_gamelist')->name('assigned_gamelist'); 
Route::post('store/{id}/games','StoreController@assigned_gamelist')->name('assigned_gamelist');  
Route::post('store/{id}/games/downloacsv','StoreController@assigned_gamelist')->name('assigned_gamelist');   
Route::post('store/{id}/view/deletegame','StoreController@deletegame')->name('deletegame');
Route::post('store/deleteall','StoreController@deleteAll')->name('deleteAll');
Route::post('store/{id}/view/deletestorefieldsall','StoreController@deleteStoreFieldsAll')->name('deleteStoreFieldsAll');

// Game Routes
Route::resource('game','GameController');
Route::get('game/index/gameslist/{type}','GameController@get_gameslisting')->name('get_gameslisting');
Route::post('game/index/gameslist/ajax','GameController@get_gameslisting')->name('get_gameslisting');
Route::post('game/{id}','GameController@update');  
Route::get('/game/{id}/view','GameController@show')->name('game.show');
Route::put('game/{id}/edit','GameController@update')->name('update');   
Route::post('game/index/gameslist/assign_storegame','GameController@get_gameslisting')->name('assign.get_gameslisting') ; 
Route::post('game/index/gameslist/csv_storegame','GameController@get_gameslisting')->name('csv.get_gameslisting') ; 
Route::get('game/{game_id}/preview_storegame/store/{store_id}/{type}','GameController@preview_gameData')->name('gamepre.preview_gameData') ;
Route::post('game/{game_id}/preview_storegame/store/{store_id}/previewcsv','GameController@preview_gameData')->name('gamepre.preview_gameData'); 
Route::post('game/{game_id}/preview_storegame/store/{store_id}/emailstore','GameController@preview_gameData')->name('email.preview_gameData') ; 

// StoreMonitor Routes
Route::resource('storemonitor','StoreMonitorController');  
// Route::get('storemonitor','StoreMonitorController@index');  
Route::get('storemonitor/game_list/{typegame}','StoreMonitorController@game_list')->name('storemonitor.game_list');
Route::post('storemonitor/game_list/ajaxgame','StoreMonitorController@game_list')->name('storemonitor.game_list');

Route::get('storemonitor/storelist/{gameid}','StoreMonitorController@storelist')->name('storemonitor.storelist');
Route::post('storemonitor/sendemailrequesttoaddgame','StoreMonitorController@sendemailrequesttoaddgame')->name('storemonitor.sendemailrequesttoaddgame');

