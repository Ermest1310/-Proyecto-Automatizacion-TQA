<?php
require __DIR__.'/qa-bootstrap.php';[$app,$kernel,$db]=qa_bootstrap();$input=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);
$rows=$db->table('postulaciones')->whereIn('Email',array_column($input,'email'))->get(['Email','Nombre','Apellido','estado']);echo json_encode(['database'=>$db->getDatabaseName(),'rows'=>$rows],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
