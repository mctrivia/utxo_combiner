<?php
//load all global config files
$files=glob(__DIR__.'/config/_*.php');
foreach ($files as $file) {
		require_once($file);
}

//load name spaced classes
spl_autoload_register(function($name){
	
	//load config file if exists
	$path=__DIR__.'/config/'.str_replace('\\','_',$name).'.php';
	if (file_exists($path)) require_once($path);
	
	//split class name into library name and parts
	list($library,$classPath)=explode('\\',$name,2);
	$classPath=str_replace('\\','/',$classPath);
	
	//Load Interface
	$path=__DIR__.'/'.$library.'/src/interface/'.$classPath.'.php';
	if (file_exists($path)) require_once($path);
	
	//Load Class
	$path=__DIR__.'/'.$library.'/src/'.$classPath.'.php';
	if (file_exists($path)) require_once($path);
	
	//Load Interface
	$path=__DIR__.'/'.$library.'/src/util/'.$classPath.'.php';
	if (file_exists($path)) require_once($path);
});

//load vendor packages if any
if (file_exists(__DIR__.'/vendor/autoload.php')) require_once(__DIR__.'/vendor/autoload.php');