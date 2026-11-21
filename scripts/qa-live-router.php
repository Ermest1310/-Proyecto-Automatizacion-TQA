<?php
require __DIR__.'/qa-bootstrap.php';
try{
 $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
 if($path==='/__qa/health'&&!hash_equals(qa_value('QA_API_TOKEN'),$_SERVER['HTTP_X_QA_TOKEN']??'')){http_response_code(401);exit('Unauthorized');}
 $public=realpath(qa_value('QA_APP_SOURCE').'/public');$candidate=realpath($public.rawurldecode($path));
 $assetExtensions=['css','js','png','jpg','jpeg','gif','svg','webp','ico','woff','woff2','ttf','map'];
 if($path!=='/'&&$candidate&&str_starts_with($candidate,$public.DIRECTORY_SEPARATOR)&&is_file($candidate)&&in_array(strtolower(pathinfo($candidate,PATHINFO_EXTENSION)),$assetExtensions,true))return false;
 [$app,$kernel,$db]=qa_bootstrap();header('X-QA-Database: '.$db->getDatabaseName());if($path==='/__qa/health'){header('Content-Type: application/json');echo json_encode(['qa'=>true,'database'=>$db->getDatabaseName(),'driver'=>'mysql']);return;}
 $response=$kernel->handle($request=\Illuminate\Http\Request::capture());$response->send();$kernel->terminate($request,$response);
}catch(Throwable $e){http_response_code(503);header('Content-Type: application/json');echo json_encode(['error'=>'QA environment unavailable']);error_log('QA startup failed: '.get_class($e));}
