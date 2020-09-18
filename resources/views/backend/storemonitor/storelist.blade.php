<div  class="table-responsive" >
	<table class="table">
	<thead>
		<tr>
			<th>Logo</th>
			<th>Retailer Name</th>
			<th>Availability</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		
			<?php foreach($getallGameStore as $store){?>
			<tr>
				<td><img width="100" height="100" src="<?php echo $store->logo ?>"/></td>
				<td><?php echo $store->store_name ?></td>
				<?php 
					$getAvailabilityAndUrl = App\Models\StoreGame::select('availability','availability_url')->where('game_id',$gameId)->where('store_id',$store->id)->first();
					// echo $getAvailabilityAndUrl->availability;
				?>
				<td>
				<?php if($getAvailabilityAndUrl->availability == 0){?>
					<span class="text-danger">Not Available</span>
				<?php }else{?>
					<span class="text-success">Available</span>
				<?php }?>
				</td>
				<td>
				<?php if($getAvailabilityAndUrl->availability == 0){?>
					<button onclick="sendEmailRequestToaddGame('<?php echo $store->contact_email?>',<?php echo $gameId?>);" class="cbtn cprimary btn-icon"><i class="mdi mdi-email"></i></button>
				<?php }else{?>
					
					<button onClick="window.open('<?php echo $getAvailabilityAndUrl->availability_url;?>');" class="cbtn cprimary btn-icon"><i class="mdi mdi-eye"></i></button>
				<?php }?>
				
				</td>
			</tr>
			<?php }?>
		
	</tbody>
</table>
</div>
