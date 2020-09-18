<?php

Breadcrumbs::for('admin.dashboard', function ($trail) {
    $trail->push(__('strings.backend.dashboard.title'), route('admin.dashboard'));
});


Breadcrumbs::for('admin.masterfields.index', function ($trail) { 
    $trail->push('Manage Matrix', route('admin.masterfields.index'));
});

Breadcrumbs::for('admin.store.index', function ($trail) { 
    $trail->push('Manage Retailer Matrix', route('admin.store.index'));
});
Breadcrumbs::for('admin.store.edit', function ($trail, $store) {
    $trail->parent('admin.store.index');
    $trail->push('Edit Retailer Information', route('admin.store.edit', $store));
});
Breadcrumbs::for('admin.store.show', function ($trail, $store) {
    $trail->parent('admin.store.index');
    $trail->push('View Retailer Matrix', route('admin.store.show', $store));
});


Breadcrumbs::for('admin.game.index', function ($trail) { 
    $trail->push('Manage Game', route('admin.game.index'));
});

Breadcrumbs::for('admin.game.edit', function ($trail, $game) { 
	$trail->parent('admin.game.index');
    $trail->push('Edit Game Information', route('admin.game.edit', $game));
});

Breadcrumbs::for('admin.game.show', function ($trail, $id) { 
	$trail->parent('admin.game.index', $id);
    $trail->push('View Game', route('admin.game.show', $id));
});


Breadcrumbs::for('admin.storemonitor.index', function ($trail ) {  
    $trail->push('Store Monitoring', route('admin.storemonitor.index' ));
});

 
require __DIR__.'/auth.php';
require __DIR__.'/log-viewer.php';
