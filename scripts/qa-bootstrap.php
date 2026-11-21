<?php
function qa_value(string $key):string{$v=getenv($key);if($v===false||$v==='')throw new RuntimeException('QA_CONFIG: falta '.$key);return $v;}
function qa_bootstrap():array{
 $source=qa_value('QA_APP_SOURCE');$storage=qa_value('QA_LIVE_STORAGE');$database=qa_value('QA_DB_DATABASE');
 if($database!=='gimnasia_unificada_test')throw new RuntimeException('QA_CONFIG: base no permitida');
 if(is_file($source.'/bootstrap/cache/config.php'))throw new RuntimeException('QA_CONFIG: Laravel tiene configuración cacheada; revisar aislamiento');
 foreach(['framework/cache/data','framework/sessions','framework/views','logs','app/private-videos','public'] as $dir)if(!is_dir($storage.'/'.$dir))mkdir($storage.'/'.$dir,0777,true);
 $env=['DB_CONNECTION'=>'mysql','DB_DATABASE'=>$database,'DB_HOST'=>qa_value('QA_DB_HOST'),'DB_PORT'=>qa_value('QA_DB_PORT'),'DB_USERNAME'=>qa_value('QA_DB_USER'),'DB_PASSWORD'=>getenv('QA_DB_PASSWORD')?:'','APP_URL'=>qa_value('APP_URL'),'APP_ENV'=>'local','APP_DEBUG'=>'false'];
 foreach($env as $k=>$v){putenv($k.'='.$v);$_ENV[$k]=$v;$_SERVER[$k]=$v;}
 require_once $source.'/vendor/autoload.php';$app=require $source.'/bootstrap/app.php';$app->useStoragePath($storage);$kernel=$app->make(\Illuminate\Contracts\Http\Kernel::class);$kernel->bootstrap();
 config(['database.default'=>'mysql','database.connections.mysql.host'=>$env['DB_HOST'],'database.connections.mysql.port'=>$env['DB_PORT'],'database.connections.mysql.database'=>$database,'database.connections.mysql.username'=>$env['DB_USERNAME'],'database.connections.mysql.password'=>$env['DB_PASSWORD'],'app.url'=>$env['APP_URL'],'app.debug'=>false,'session.driver'=>'file','cache.default'=>'file','logging.channels.audit.path'=>$storage.'/logs/audit.log','filesystems.disks.private_videos.root'=>$storage.'/app/private-videos']);
 \Illuminate\Support\Facades\DB::purge('mysql');$db=\Illuminate\Support\Facades\DB::connection('mysql');if($db->selectOne('SELECT DATABASE() AS db')->db!==$database)throw new RuntimeException('QA_CONFIG: aislamiento no confirmado');return [$app,$kernel,$db];
}
