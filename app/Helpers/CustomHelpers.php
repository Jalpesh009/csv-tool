<?php 
function setActive(string $path, string $class_name = "is-active")
{	
	echo '<pre>';
	print_r($path);
	// die;
    return Request::path() === $path ? $class_name : "";
}

?>