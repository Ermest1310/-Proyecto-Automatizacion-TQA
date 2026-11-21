<?php
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

final class QaPlanTest extends \Tests\TestCase
{
 public function createApplication(): \Illuminate\Foundation\Application {
  $source=getenv('QA_APP_SOURCE');$scratch=getenv('QA_STORAGE_DIR');
  if(!$source||!$scratch||is_file($source.'/bootstrap/cache/config.php'))throw new RuntimeException('BLOCKED: aislamiento no disponible');
  foreach(['framework/cache/data','framework/sessions','framework/views','logs','public'] as $dir)if(!is_dir($scratch.'/'.$dir))mkdir($scratch.'/'.$dir,0777,true);
  $app=require $source.'/bootstrap/app.php';$app->useStoragePath($scratch);$app->usePublicPath($scratch.'/public');$app->make(Kernel::class)->bootstrap();
  $app['config']->set('database.default','sqlite');$app['config']->set('database.connections.sqlite.database',':memory:');
  $app['config']->set('session.driver','array');$app['config']->set('cache.default','array');$app['config']->set('mail.default','array');$app['config']->set('hashing.bcrypt.rounds',4);
  $app['config']->set('filesystems.disks.private_videos.root',$scratch.'/private-videos');$app['config']->set('filesystems.disks.public.root',$scratch.'/public-files');$app['config']->set('logging.channels.audit.path',$scratch.'/logs/audit.log');return $app;
 }
 protected function setUp():void {parent::setUp();DB::purge();$this->assertSame('sqlite',DB::connection()->getDriverName());$this->assertSame(':memory:',DB::connection()->getDatabaseName());DB::statement('PRAGMA foreign_keys=ON');$this->assertSame(1,(int)DB::selectOne('PRAGMA foreign_keys')->foreign_keys);Http::preventStrayRequests();\Carbon\Carbon::setTestNow('2030-06-15 12:00:00');Artisan::call('migrate',['--force'=>true]);$this->assertSame(1,(int)DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
  foreach([1=>'Administrador',2=>'Gimnasta',3=>'Entrenador',4=>'Médico',5=>'Juez',6=>'Fisioterapeuta'] as $id=>$role){DB::table('roles')->insert(['ID_Rol'=>$id,'NombreRol'=>$role,'activo'=>true]);\App\Models\Usuario::create(['ID_Usuario'=>$id,'ID_Rol'=>$id,'NombreUsuario'=>'qa_plan_'.$id,'Nombre'=>'Atleta','Apellido'=>'Control','Email'=>'plan'.$id.'@example.test','Contrasena'=>Hash::make(bin2hex(random_bytes(16))),'CedulaIdentidad'=>(string)(21000000+$id),'Nacionalidad'=>'Bolivia','activo'=>true,'Genero'=>$id===2?'M':null,'Categoria'=>'Senior','Nivel'=>'Intermedio','FechaNacimiento'=>'2010-05-05','Peso'=>60,'Altura'=>1.65]);}
  $this->proof('isolation',['driver'=>'sqlite','foreign_keys'=>1,'database'=>':memory:']);
 }
 protected function tearDown():void {\Carbon\Carbon::setTestNow();parent::tearDown();}
 private function actor(int $id):self{return $this->withSession(['usuario_id'=>$id,'usuario_rol'=>\App\Models\Usuario::findOrFail($id)->rol->NombreRol]);}
 private function proof(string $id,array $data):void {file_put_contents(getenv('QA_STORAGE_DIR').'/'.$id.'.json',json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));}
 private function registration(array $extra=[]):array{return array_replace(['Nombre'=>'Pedro','Apellido'=>'Prueba','Email'=>'plan.registration@example.test','Nacionalidad'=>'Bolivia','CedulaIdentidad'=>'32000000','FechaNacimiento'=>'2015-06-15','Genero'=>'M','Categoria'=>'Junior','Peso'=>60,'Altura'=>1.65,'Nivel'=>'Principiante'],$extra);}
 private function apparatus():\App\Models\Aparato{return \App\Models\Aparato::create(['Nombre'=>'Suelo','activo'=>true,'EstadoMantenimiento'=>'Operativo']);}
 private function sessionData(array $extra=[]):array{return array_replace(['ID_Gimnasta'=>2,'ID_Entrenador'=>3,'ID_Aparato'=>$this->apparatus()->ID_Aparato,'Fecha'=>'2030-06-16','Hora_Inicio'=>'10:00','Hora_Fin'=>'11:00'],$extra);}
 private function sessionFixture(array $extra=[]):\App\Models\SesionEntrenamiento{return \App\Models\SesionEntrenamiento::create(array_replace(['ID_Gimnasta'=>2,'ID_Entrenador'=>3,'ID_Aparato'=>null,'Fecha'=>'2030-06-14','Hora_Inicio'=>'10:00','Hora_Fin'=>'11:00','Duracion_Minutos'=>60,'Tipo_Asignacion'=>'Manual','Estado'=>'Programada'], $extra));}
 private function measurementData(array $extra=[]):array{return array_replace(['athlete_id'=>2,'source'=>'manual','manual_key'=>'codo_izq','width'=>1000,'height'=>1000,'timestamp_seconds'=>1,'timestamp_source'=>'video_current_time','landmarks'=>[['x'=>0.1,'y'=>0.2],['x'=>0.2,'y'=>0.3],['x'=>0.3,'y'=>0.4]]],$extra);}
 public function test_plan_tc_01():void {$this->get('/')->assertRedirect(route('login'));$this->proof('TC-01',['status'=>302,'allowed'=>false]);}
 public function test_plan_tc_06():void {$data=$this->registration();$before=DB::table('usuarios')->count();$this->post('/register',$data)->assertRedirect(route('login'));$this->assertDatabaseHas('postulaciones',['Email'=>$data['Email'],'estado'=>'pendiente','Nombre'=>$data['Nombre'],'Apellido'=>$data['Apellido']]);$this->assertSame($before,DB::table('usuarios')->count());$this->proof('TC-06',['status'=>302,'rows'=>1,'persisted'=>true]);}
 public function test_plan_tc_07():void {$data=$this->registration(['Nombre'=>'María','Apellido'=>'González']);$this->post('/register',$data)->assertRedirect(route('login'));$this->assertDatabaseHas('postulaciones',['Nombre'=>'María','Apellido'=>'González','Email'=>$data['Email']]);$this->proof('TC-07',['status'=>302,'persisted'=>true]);}
 public function test_plan_tc_08():void {$this->from('/register')->post('/register',$this->registration(['FechaNacimiento'=>'2021-06-15']))->assertRedirect('/register')->assertSessionHasErrors('FechaNacimiento');$this->assertDatabaseCount('postulaciones',0);$this->proof('TC-08',['status'=>302,'rows'=>0,'rejected'=>true]);}
 public function test_plan_tc_09():void {$data=$this->registration();$this->post('/register',$data)->assertRedirect(route('login'));$this->postJson('/register',array_replace($data,['Email'=>'plan.duplicate@example.test']))->assertUnprocessable()->assertJsonValidationErrors('CedulaIdentidad');$this->assertDatabaseCount('postulaciones',1);$this->proof('TC-09',['status'=>422,'rows'=>1,'rejected'=>true]);}
 public function test_plan_tc_15():void {$this->actor(1)->get('/admin/audit')->assertOk();$r=$this->actor(2)->getJson('/admin/audit');$r->assertForbidden()->assertJson(['error'=>'Sin permisos']);$this->assertStringNotContainsString('audit_events',$r->getContent());$this->proof('TC-15',['status'=>403,'denied'=>true]);}
 public function test_plan_tc_18():void {$r=$this->actor(1)->get('/admin/reportes/exportar-gimnasta/2');$r->assertOk()->assertHeader('Content-Type','application/pdf');$pdf=$r->getContent();$this->assertStringStartsWith('%PDF-',$pdf);$this->assertStringContainsString('%%EOF',$pdf);file_put_contents(getenv('QA_STORAGE_DIR').'/athlete.pdf',$pdf);$this->assertStringContainsString('Atleta_Control',$r->headers->get('Content-Disposition'));preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s',$pdf,$streams);$content='';foreach($streams[1] as $stream){$decoded=@gzuncompress($stream);$content.=$decoded===false?$stream:$decoded;}$this->assertStringContainsString('plan2@example.test',$content);$this->assertStringNotContainsString('plan1@example.test',$content);$this->actor(2)->getJson('/admin/reportes/exportar-gimnasta/2')->assertForbidden();$this->proof('TC-18',['status'=>200,'valid'=>true]);}
 public function test_plan_tc_20():void {$r=$this->actor(1)->get('/areamedica/psicologia');$r->assertOk()->assertViewIs('areamedica.especialidad')->assertViewHas('specialty','Psicología')->assertSee('Psicología');$this->proof('TC-20',['status'=>200,'valid'=>true]);}
 public function test_plan_tc_22():void {$hit=false;Http::fake(function()use(&$hit){$hit=true;return Http::response([],200);});$this->get('/login')->assertOk();$csrf=session()->token();$this->assertNotEmpty($csrf);$r=$this->withHeader('X-CSRF-TOKEN',$csrf)->postJson('/api/psicologia/chat',['message'=>'Control anónimo']);$r->assertUnauthorized();$this->assertFalse($hit);$this->assertStringNotContainsString('Exception',$r->getContent());$this->assertStringNotContainsString('sk-',$r->getContent());$this->proof('TC-22',['status'=>401,'denied'=>true]);}
 public function test_plan_tc_25():void {$data=$this->sessionData(['Fecha'=>'2020-01-01']);$this->actor(1)->postJson('/sesiones',$data)->assertUnprocessable()->assertJsonValidationErrors('Fecha');$this->assertDatabaseCount('sesiones_entrenamiento',0);$this->proof('TC-25',['status'=>422,'rows'=>0]);}
 public function test_plan_tc_26():void {$data=$this->sessionData();$this->actor(1)->post('/sesiones',$data)->assertRedirect(route('sesiones.index'));$this->assertDatabaseHas('sesiones_entrenamiento',['ID_Gimnasta'=>2,'ID_Entrenador'=>3,'Estado'=>'Programada','Duracion_Minutos'=>60]);$this->assertDatabaseCount('sesiones_entrenamiento',1);$this->proof('TC-26',['status'=>302,'rows'=>1,'persisted'=>true]);}
 public function test_plan_tc_27():void {$data=$this->sessionData();$this->actor(1)->post('/sesiones',$data)->assertRedirect(route('sesiones.index'));$this->postJson('/sesiones',array_replace($data,['Hora_Inicio'=>'10:30','Hora_Fin'=>'11:30']))->assertUnprocessable()->assertJsonValidationErrors('Hora_Fin');$this->assertDatabaseCount('sesiones_entrenamiento',1);$this->proof('TC-27',['status'=>422,'rows'=>1,'rejected'=>true]);}
 public function test_plan_tc_28():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['Hora_Inicio'=>'05:59','Hora_Fin'=>'06:45']))->assertUnprocessable()->assertJsonValidationErrors('Hora_Inicio');$this->assertDatabaseCount('sesiones_entrenamiento',0);$this->proof('TC-28',['status'=>422,'rows'=>0]);}
 public function test_plan_tc_29():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['Hora_Fin'=>'10:20']))->assertUnprocessable()->assertJsonValidationErrors('Hora_Fin');$this->assertDatabaseCount('sesiones_entrenamiento',0);$this->proof('TC-29',['status'=>422,'rows'=>0]);}
 public function test_plan_tc_30():void {$this->actor(1)->post('/sesiones',$this->sessionData(['Hora_Inicio'=>'08:00','Hora_Fin'=>'10:55']))->assertRedirect(route('sesiones.index'));$this->assertDatabaseCount('sesiones_entrenamiento',1);$this->assertDatabaseHas('sesiones_entrenamiento',['Duracion_Minutos'=>175]);$this->proof('TC-30',['status'=>302,'rows'=>1]);}
 public function test_plan_tc_31():void {$disk=Storage::disk('private_videos');$before=$disk->allFiles();$r=$this->actor(1)->post('/analisis/upload',['video'=>UploadedFile::fake()->createWithContent('qa-inert.php','<?php /* no executable statements */ ?>')],['Accept'=>'application/json']);$r->assertUnprocessable()->assertJsonValidationErrors('video');$this->assertDatabaseCount('videos',0);$this->assertSame($before,$disk->allFiles());$video=new UploadedFile(__DIR__.'/../fixtures/qa-valid.webm','qa-valid.webm','video/webm',null,true);$control=$this->post('/analisis/upload',['video'=>$video],['Accept'=>'application/json']);$control->assertOk()->assertJson(['success'=>true]);$this->assertDatabaseCount('videos',1);$this->proof('TC-31',['status'=>422,'valid'=>true,'rows'=>1]);}
 public function test_plan_tc_37():void {foreach([29,30,31,49,50,51] as $i=>$length){$email=str_repeat('a',$length-strlen('@example.test')).'@example.test';$data=$this->registration(['Email'=>$email,'CedulaIdentidad'=>(string)(35000000+$i)]);$r=$this->withServerVariables(['REMOTE_ADDR'=>'192.0.2.'.($i+40)])->postJson('/register',$data);if($length<=50){$r->assertRedirect(route('login'));$this->assertDatabaseHas('postulaciones',['Email'=>$email]);}else{$r->assertUnprocessable()->assertJsonValidationErrors('Email');$this->assertDatabaseMissing('postulaciones',['Email'=>$email]);}}$this->assertDatabaseCount('postulaciones',5);$this->proof('TC-37',['valid'=>true,'rows'=>5]);}

 public function test_plan_tc_35():void {
  $apparatus=$this->apparatus();$values=[0.1,0.2,0.3,0.4,1.0];$edges=[[1,2],[3],[4],[4],[]];$elements=[];
  foreach($values as $i=>$value){$e=\App\Models\ElementoRutina::create(['ID_Aparato'=>$apparatus->ID_Aparato,'Nombre'=>'QA elemento '.($i+1),'ValorDificultad'=>$value,'Grupo'=>'A','MinEnlace'=>$i===0?0:1,'IDsConexion'=>[],'activo'=>true]);$elements[]=$e;\App\Models\CalificacionElemento::create(['ID_Gimnasta'=>2,'ID_Elemento'=>$e->ID_Elemento,'TasaExito'=>1,'IntentosExitosos'=>10,'IntentosFallidos'=>0]);}
  foreach($edges as $i=>$next)$elements[$i]->update(['IDsConexion'=>array_map(fn($n)=>$elements[$n]->ID_Elemento,$next)]);
  // Independent exhaustive enumeration of all bounded paths in this five-node fixture.
  $candidates=[];$walk=function($path)use(&$walk,&$candidates,$edges,$values){$candidates[]=array_sum(array_map(fn($i)=>$values[$i],$path));if(count($path)>=4)return;foreach($edges[end($path)] as $next)if(!in_array($next,$path,true))$walk([...$path,$next]);};$walk([0]);$oracle=max($candidates);
  $r=$this->actor(1)->postJson('/rutinas/planificar-optima',['gimnasta_id'=>2,'aparato_id'=>$apparatus->ID_Aparato,'max_elementos'=>4]);$r->assertOk()->assertJsonPath('success',true)->assertJsonPath('gimnasta.id',2);
  $ids=array_map('intval',array_column($r->json('rutina_optima.secuencia'),'elemento_id'));$this->assertLessThanOrEqual(4,count($ids));$this->assertCount(count(array_unique($ids)),$ids);$this->assertEqualsWithDelta($oracle,$r->json('rutina_optima.puntaje_esperado'),0.001);
  $this->assertSame($elements[0]->ID_Elemento,$ids[0]);foreach(array_slice($ids,1) as $j=>$id){$previous=array_search($ids[$j],array_map(fn($e)=>$e->ID_Elemento,$elements));$this->assertContains($id,array_map(fn($n)=>$elements[$n]->ID_Elemento,$edges[$previous]));}
  $this->proof('TC-35',['status'=>200,'valid'=>true,'assertions'=>count($candidates)]);
 }
 public function test_plan_tc_36():void {
  $lesion=\App\Models\Lesion::create(['ID_Gimnasta'=>2,'ID_Medico'=>4,'Tipo'=>'Muscular','ParteCuerpo'=>'Espalda','Gravedad'=>'Leve','FechaLesion'=>'2030-06-14','FechaDiagnostico'=>'2030-06-14','Estado'=>'Lesionado','activo'=>true]);
  $phases=[];foreach([2,3,10,1] as $i=>$days)$phases[]=\App\Models\FaseRehabilitacion::create(['Nombre'=>'QA fase '.($i+1),'Descripcion'=>'Fixture controlado','Orden'=>$i+1,'Duracion_Minima_Dias'=>$days,'Duracion_Maxima_Dias'=>$days,'Riesgo_Recaída'=>0,'Tipos_Lesion_Validos'=>['Muscular'],'Gravedades_Validas'=>['Leve'],'Fases_Siguientes'=>[],'Es_Inicial'=>$i===0,'Es_Final'=>$i===3,'activo'=>true]);
  $phases[0]->update(['Fases_Siguientes'=>[$phases[1]->ID_Fase,$phases[2]->ID_Fase]]);$phases[1]->update(['Fases_Siguientes'=>[$phases[3]->ID_Fase]]);$phases[2]->update(['Fases_Siguientes'=>[$phases[3]->ID_Fase]]);
  // Complete alternatives: 2+3+1=6 days versus 2+10+1=13, no risk/history penalty.
  $oracle=min(2+3+1,2+10+1);$input=['lesion_id'=>$lesion->ID_Lesion,'fecha_inicio'=>'2030-06-16','guardar'=>false];$r=$this->actor(4)->postJson('/rehabilitacion/crear-plan',$input);$r->assertOk()->assertJsonPath('success',true)->assertJsonPath('plan.duracion_total_dias',$oracle);$this->assertSame(array_map('strval',[$phases[0]->ID_Fase,$phases[1]->ID_Fase,$phases[3]->ID_Fase]),array_column($r->json('plan.secuencia'),'fase_id'));$this->assertDatabaseCount('planes_rehabilitacion',0);
  $save=$this->postJson('/rehabilitacion/crear-plan',array_replace($input,['guardar'=>true]));$save->assertRedirect();$this->assertDatabaseCount('planes_rehabilitacion',1);$this->assertDatabaseHas('planes_rehabilitacion',['ID_Lesion'=>$lesion->ID_Lesion,'Duracion_Estimada_Dias'=>$oracle,'ID_Medico'=>4]);$this->assertSame(3,\App\Models\PlanFaseSeguimiento::count());$this->proof('TC-36',['status'=>200,'valid'=>true,'persisted'=>true]);
 }

 public function test_expanded_01():void {
  foreach([[1,'/admin/inicio'],[3,'/entrenador/dashboard'],[4,'/medico/dashboard'],[6,'/medico/dashboard'],[2,'/gimnasta/dashboard'],[5,'/juez/dashboard']] as [$user,$path])$this->actor($user)->get($path)->assertOk();
  $this->proof('EXT-01',['authorizedDashboards'=>6]);
 }
 public function test_expanded_02():void {
  $this->get('/password/forgot')->assertOk();$this->actor(2)->get('/perfil')->assertOk();$before=DB::table('usuarios')->where('ID_Usuario',2)->value('Email');
  $this->actor(2)->putJson('/perfil',['Email'=>'invalido','CedulaIdentidad'=>'','Nacionalidad'=>''])->assertUnprocessable()->assertJsonValidationErrors(['Email','CedulaIdentidad','Nacionalidad']);
  $this->assertSame($before,DB::table('usuarios')->where('ID_Usuario',2)->value('Email'));$this->proof('EXT-02',['recoveryPage'=>true,'profile'=>true,'invalidUpdateRejected'=>true]);
 }
 public function test_expanded_03():void {
  foreach(['/entrenador/dashboard','/entrenador/gimnastas','/entrenador/sesiones','/entrenador/crear-gimnasta','/entrenador/calendario'] as $path)$this->actor(3)->get($path)->assertOk();
  $this->proof('EXT-03',['trainerViews'=>5]);
 }
 public function test_expanded_04():void {
  foreach(['/gimnasta/dashboard','/gimnasta/progreso','/gimnasta/entrenador','/gimnasta/sesiones','/gimnasta/sesiones/crear','/gimnasta/historial','/gimnasta/calendario'] as $path)$this->actor(2)->get($path)->assertOk();
  $this->proof('EXT-04',['athleteViews'=>7]);
 }
 public function test_expanded_05():void {
  $this->actor(2)->get('/gimnasta/solicitudes-nivel')->assertOk();$this->actor(3)->get('/entrenador/solicitudes-nivel')->assertOk();$this->actor(1)->get('/admin/solicitudes-nivel')->assertOk();
  $this->proof('EXT-05',['roles'=>['Gimnasta','Entrenador','Administrador']]);
 }
 public function test_expanded_06():void {
  $this->actor(1)->get('/sesiones?estado=Programada&fecha_desde=2030-06-16&fecha_hasta=2030-06-20')->assertOk();$this->actor(1)->get('/sesiones/historial')->assertOk();$this->actor(1)->get('/sesiones/crear')->assertOk();
  $this->proof('EXT-06',['list'=>true,'filter'=>true,'createForm'=>true]);
 }
 public function test_expanded_07():void {
  foreach(['/roles','/usuarios-unificado','/usuarios-unificado-roles','/gimnastas','/entrenadores','/medicos','/fisioterapeutas'] as $path)$this->actor(1)->get($path)->assertOk();
  $this->proof('EXT-07',['directoryViews'=>7]);
 }
 public function test_expanded_08():void {
  foreach(['/aparatos','/competencias','/entrenamientos'] as $path)$this->actor(1)->get($path)->assertOk();$this->actor(1)->getJson('/entrenamientos-data')->assertOk();
  $this->proof('EXT-08',['catalogViews'=>4]);
 }
 public function test_expanded_09():void {
  $data=$this->registration(['Email'=>'expanded.application@example.test','CedulaIdentidad'=>'33110001']);$this->post('/register',$data)->assertRedirect(route('login'));
  $id=DB::table('postulaciones')->where('Email',$data['Email'])->value('ID_Postulacion');$this->assertNotNull($id);$this->actor(1)->get('/postulaciones')->assertOk();
  $this->getJson('/postulaciones/'.$id.'/detalle')->assertOk()->assertJsonPath('Email',$data['Email']);$this->post('/postulaciones/'.$id.'/rechazar',['comentarios'=>'Revisión QA aislada'])->assertRedirect();
  $this->assertDatabaseHas('postulaciones',['ID_Postulacion'=>$id,'estado'=>'rechazada','comentarios'=>'Revisión QA aislada']);$this->proof('EXT-09',['applicationCreated'=>true,'detailVisible'=>true,'rejected'=>true]);
 }
 public function test_expanded_10():void {
  foreach(['/areamedica','/areamedica/fisioterapia','/areamedica/nutricion','/areamedica/psicologia','/areamedica/psicologia/chatbot','/areamedica/medicina','/areamedica/rehabilitacion'] as $path)$this->actor(4)->get($path)->assertOk();
  $this->proof('EXT-10',['medicalViews'=>7]);
 }
 public function test_expanded_11():void {
  $this->actor(4)->get('/lesiones')->assertOk();$this->actor(4)->get('/lesiones/create')->assertOk();$this->actor(4)->getJson('/lesiones-gimnastas')->assertOk();
  $this->proof('EXT-11',['injuryList'=>true,'createForm'=>true,'athleteLookup'=>true]);
 }
 public function test_expanded_12():void {
  $this->actor(4)->get('/rehabilitacion')->assertOk();$this->actor(4)->get('/rehabilitacion/planes')->assertOk();$this->actor(2)->getJson('/rehabilitacion')->assertForbidden();
  $this->proof('EXT-12',['rehabilitationViews'=>2,'unauthorizedRoleDenied'=>true]);
 }
 public function test_expanded_13():void {
  foreach(['/analisis/aptitud','/analisis/nutricional','/analisis/respiratoria','/analisis/psicologica','/analisis/fisiologica'] as $path)$this->actor(4)->get($path)->assertOk();
  $this->proof('EXT-13',['assessmentForms'=>5]);
 }
 public function test_expanded_14():void {
  foreach(['/analisis','/analisis/video','/analisis/fig/catalogo','/analisis/fig/propuestas','/analisis/gimnastas','/analisis/mediciones','/analisis/angulo','/analisis/estadisticas','/analisis/movimiento-dashboard'] as $path)$this->actor(1)->get($path)->assertOk();
  $this->proof('EXT-14',['analysisViews'=>9]);
 }
 public function test_expanded_15():void {
  foreach(['/juez/dashboard','/juez/crear','/juez/pendientes'] as $path)$this->actor(5)->get($path)->assertOk();
  $this->proof('EXT-15',['judgeViews'=>3]);
 }
 public function test_expanded_16():void {
  $this->actor(1)->get('/sistema-experto/reglas')->assertOk();$this->actor(2)->getJson('/sistema-experto/reglas')->assertForbidden();
  $this->proof('EXT-16',['rulesPage'=>true,'unauthorizedRoleDenied'=>true]);
 }
 public function test_expanded_17():void {
  $this->actor(4)->get('/areamedica/psicologia/sepi')->assertOk();
  $this->actor(4)->postJson('/areamedica/psicologia/sepi',['ID_Gimnasta'=>2,'ansiedad'=>11,'estres'=>4,'resiliencia'=>6,'concentracion'=>6])->assertUnprocessable()->assertJsonValidationErrors('ansiedad');
  $this->proof('EXT-17',['sepiPage'=>true,'outOfRangeRejected'=>true]);
 }
 public function test_expanded_18():void {
  foreach(['/mineria','/mineria/clustering','/mineria/patrones','/mineria/correlaciones','/mineria/prediccion'] as $path)$this->actor(1)->get($path)->assertOk();
  $this->proof('EXT-18',['miningViews'=>5]);
 }
 public function test_expanded_19():void {
  $this->actor(1)->get('/rutinas/planificar')->assertOk();$this->proof('EXT-19',['routinePlanner'=>true]);
 }
 public function test_expanded_20():void {
  foreach(['/admin/reportes','/admin/reportes/completo','/admin/reportes/backup'] as $path)$this->actor(1)->get($path)->assertOk();
  $this->proof('EXT-20',['reportViews'=>3]);
 }
 public function test_expanded_21():void {
  foreach(['/admin/reportes','/roles','/usuarios-unificado','/postulaciones','/medico/dashboard','/juez/dashboard'] as $path)$this->actor(2)->getJson($path)->assertForbidden();
  $this->proof('EXT-21',['forbiddenRoutes'=>6,'role'=>'Gimnasta']);
 }
 public function test_expanded_22():void {
  $this->actor(2)->postJson('/api/ia/registrar-feedback',[])->assertForbidden();$this->getJson('/api/ia/estadisticas-modelo')->assertForbidden();$this->postJson('/api/sepi/diagnosticar',[])->assertForbidden();
  $this->proof('EXT-22',['permissionProtectedApis'=>3]);
 }
 public function test_expanded_23():void {
  $this->actor(2)->getJson('/juez/evaluar/1/video')->assertForbidden();$this->proof('EXT-23',['privateVideoDenied'=>true]);
 }

 public function test_deep_001():void {
  $data=$this->registration(['Email'=>'deep.approval@example.test','CedulaIdentidad'=>'33110021']);$this->post('/register',$data)->assertRedirect(route('login'));
  $id=DB::table('postulaciones')->where('Email',$data['Email'])->value('ID_Postulacion');$this->actor(1)->post('/postulaciones/'.$id.'/aprobar')->assertRedirect();
  $this->assertDatabaseHas('postulaciones',['ID_Postulacion'=>$id,'estado'=>'aprobada']);$this->assertDatabaseHas('usuarios',['Email'=>$data['Email'],'ID_Rol'=>2]);$this->proof('DEEP-001',['approved'=>true,'accountCreated'=>true,'mailer'=>'array']);
 }
 public function test_deep_002():void {
  $data=$this->registration(['Email'=>'deep.duplicate@example.test','CedulaIdentidad'=>'33110022']);$this->post('/register',$data)->assertRedirect(route('login'));
  $id=DB::table('postulaciones')->where('Email',$data['Email'])->value('ID_Postulacion');$this->actor(1)->post('/postulaciones/'.$id.'/aprobar')->assertRedirect();$this->postJson('/postulaciones/'.$id.'/aprobar')->assertRedirect();
  $this->assertSame(1,DB::table('usuarios')->where('Email',$data['Email'])->count());$this->proof('DEEP-002',['duplicateApprovalRejected'=>true,'accounts'=>1]);
 }
 public function test_deep_003():void {
  $before=DB::table('aparatos')->count();$this->actor(1)->postJson('/aparatos',['Nombre'=>'Pista QA'])->assertUnprocessable()->assertJsonValidationErrors('Nombre');
  $this->assertSame($before,DB::table('aparatos')->count());$this->proof('DEEP-003',['invalidCatalogValueRejected'=>true,'rowsUnchanged'=>true]);
 }
 public function test_deep_004():void {
  $apparatus=$this->apparatus();$this->actor(1)->postJson('/entrenamientos',['ID_Gimnasta'=>2,'ID_Aparato'=>$apparatus->ID_Aparato,'Fecha'=>'2030-06-14','Duracion'=>14])->assertUnprocessable()->assertJsonValidationErrors('Duracion');
  $this->assertDatabaseCount('entrenamientos',0);$this->proof('DEEP-004',['minimumDurationRejected'=>true,'rows'=>0]);
 }
 public function test_deep_005():void {
  $apparatus=$this->apparatus();$this->actor(1)->postJson('/entrenamientos',['ID_Gimnasta'=>2,'ID_Aparato'=>$apparatus->ID_Aparato,'Fecha'=>'2030-06-16','Duracion'=>60])->assertUnprocessable()->assertJsonValidationErrors('Fecha');
  $this->assertDatabaseCount('entrenamientos',0);$this->proof('DEEP-005',['futureTrainingRejected'=>true,'rows'=>0]);
 }
 public function test_deep_006():void {
  $this->actor(2)->post('/solicitudes-nivel',['comentarios_gimnasta'=>'Solicitud QA'])->assertRedirect();$this->post('/solicitudes-nivel',['comentarios_gimnasta'=>'Duplicada'])->assertRedirect();
  $this->assertDatabaseCount('solicitudes_nivel',1);$this->assertDatabaseHas('solicitudes_nivel',['ID_Gimnasta'=>2,'estado'=>'pendiente']);$this->proof('DEEP-006',['duplicatePendingRequestPrevented'=>true,'rows'=>1]);
 }
 public function test_deep_007():void {
  $this->actor(2)->post('/solicitudes-nivel')->assertRedirect();$id=DB::table('solicitudes_nivel')->value('ID_Solicitud');$this->actor(1)->post('/admin/solicitudes-nivel/'.$id.'/aprobar')->assertRedirect();
  $this->assertDatabaseHas('solicitudes_nivel',['ID_Solicitud'=>$id,'estado'=>'aprobada']);$this->assertDatabaseHas('usuarios',['ID_Usuario'=>2,'Nivel'=>'Avanzado']);$this->proof('DEEP-007',['approved'=>true,'levelUpdated'=>true]);
 }
 public function test_deep_008():void {
  $this->actor(2)->post('/solicitudes-nivel')->assertRedirect();$id=DB::table('solicitudes_nivel')->value('ID_Solicitud');$this->actor(1)->post('/admin/solicitudes-nivel/'.$id.'/aprobar')->assertRedirect();
  $this->postJson('/admin/solicitudes-nivel/'.$id.'/aprobar')->assertStatus(409);$this->assertSame('Avanzado',DB::table('usuarios')->where('ID_Usuario',2)->value('Nivel'));$this->proof('DEEP-008',['repeatedDecisionRejected'=>true,'levelChangedOnce'=>true]);
 }
 public function test_deep_009():void {
  $this->actor(4)->postJson('/lesiones',['ID_Gimnasta'=>2,'ID_Medico'=>4,'Tipo'=>'Muscular','ParteCuerpo'=>'Espalda','Gravedad'=>'Leve','FechaLesion'=>'2030-06-16','Estado'=>'Lesionado'])->assertUnprocessable()->assertJsonValidationErrors('FechaLesion');
  $this->assertDatabaseCount('lesiones',0);$this->proof('DEEP-009',['futureInjuryDateRejected'=>true,'rows'=>0]);
 }
 public function test_deep_010():void {
  $this->actor(4)->postJson('/lesiones',['ID_Gimnasta'=>2,'ID_Medico'=>3,'Tipo'=>'Muscular','ParteCuerpo'=>'Espalda','Gravedad'=>'Leve','FechaLesion'=>'2030-06-14','Estado'=>'Lesionado'])->assertUnprocessable()->assertJsonValidationErrors('ID_Medico');
  $this->assertDatabaseCount('lesiones',0);$this->proof('DEEP-010',['nonMedicalDiagnosticianRejected'=>true,'rows'=>0]);
 }
 public function test_deep_011():void {
  $this->actor(1)->postJson('/competencias',['NombreCompetencia'=>'QA inválida','Fecha'=>'2030-06-16','Ubicacion'=>'QA','inscribir'=>[3]])->assertUnprocessable()->assertJsonValidationErrors('inscribir');
  $this->assertDatabaseCount('competencias',0);$this->assertDatabaseCount('competencia_gimnastas',0);$this->proof('DEEP-011',['nonAthleteEnrollmentRejected'=>true,'noPartialWrite'=>true]);
 }
 public function test_deep_012():void {
  $this->actor(1)->postJson('/competencias',['NombreCompetencia'=>'Competencia QA','Fecha'=>'2030-06-16','Ubicacion'=>'Gimnasio QA','inscribir'=>[2]])->assertOk()->assertJson(['success'=>true]);
  $this->assertDatabaseHas('competencias',['NombreCompetencia'=>'Competencia QA']);$this->assertDatabaseHas('competencia_gimnastas',['ID_Gimnasta'=>2]);$this->proof('DEEP-012',['competitionCreated'=>true,'athleteEnrolled'=>true]);
 }
 public function test_deep_013():void {
  $this->actor(2)->postJson('/aparatos',['Nombre'=>'Suelo','EstadoMantenimiento'=>'Operativo'])->assertForbidden();$this->assertDatabaseCount('aparatos',0);$this->proof('DEEP-013',['unauthorizedCatalogMutationDenied'=>true]);
 }
 public function test_deep_014():void {
  $apparatus=$this->apparatus();\App\Models\Lesion::create(['ID_Gimnasta'=>2,'ID_Medico'=>4,'Tipo'=>'Muscular','ParteCuerpo'=>'Espalda','Gravedad'=>'Leve','FechaLesion'=>'2030-06-14','FechaDiagnostico'=>'2030-06-14','Estado'=>'Lesionado','activo'=>true]);
  $this->actor(1)->postJson('/entrenamientos',['ID_Gimnasta'=>2,'ID_Aparato'=>$apparatus->ID_Aparato,'Fecha'=>'2030-06-14','Duracion'=>60])->assertUnprocessable()->assertJsonValidationErrors('ID_Gimnasta');
  $this->assertDatabaseCount('entrenamientos',0);$this->proof('DEEP-014',['injuredAthleteTrainingBlocked'=>true,'rows'=>0]);
 }
 public function test_deep_015():void {$session=$this->sessionFixture();$this->actor(1)->patchJson('/sesiones/'.$session->ID_Sesion.'/realizacion',[])->assertUnprocessable()->assertJsonValidationErrors('Asistio_Gimnasta');$this->assertDatabaseHas('sesiones_entrenamiento',['ID_Sesion'=>$session->ID_Sesion,'Estado'=>'Programada']);$this->proof('DEEP-015',['attendanceRequired'=>true]);}
 public function test_deep_016():void {$session=$this->sessionFixture();$this->actor(1)->patchJson('/sesiones/'.$session->ID_Sesion.'/realizacion',['Asistio_Gimnasta'=>1,'Calificacion_Sesion'=>6])->assertUnprocessable()->assertJsonValidationErrors('Calificacion_Sesion');$this->assertDatabaseHas('sesiones_entrenamiento',['ID_Sesion'=>$session->ID_Sesion,'Estado'=>'Programada']);$this->proof('DEEP-016',['ratingOutOfRangeRejected'=>true]);}
 public function test_deep_017():void {$session=$this->sessionFixture();$this->actor(1)->patchJson('/sesiones/'.$session->ID_Sesion.'/realizacion',['Asistio_Gimnasta'=>1,'Calificacion_Sesion'=>4,'Comentarios_Finales'=>'Sesión completada correctamente.'])->assertOk()->assertJson(['success'=>true]);$this->assertDatabaseHas('sesiones_entrenamiento',['ID_Sesion'=>$session->ID_Sesion,'Estado'=>'Completada','Realizada_Por'=>1,'Asistio_Gimnasta'=>true,'Calificacion_Sesion'=>4]);$this->proof('DEEP-017',['completionPersisted'=>true]);}
 public function test_deep_018():void {$session=$this->sessionFixture();$payload=['Asistio_Gimnasta'=>1];$this->actor(1)->patchJson('/sesiones/'.$session->ID_Sesion.'/realizacion',$payload)->assertOk();$this->patchJson('/sesiones/'.$session->ID_Sesion.'/realizacion',$payload)->assertStatus(409);$this->assertSame(1,DB::table('sesiones_entrenamiento')->where('ID_Sesion',$session->ID_Sesion)->whereNotNull('Realizada_En')->count());$this->proof('DEEP-018',['duplicateCompletionRejected'=>true]);}
 public function test_deep_019():void {$session=$this->sessionFixture(['Fecha'=>'2030-06-16']);$this->actor(1)->patchJson('/sesiones/'.$session->ID_Sesion.'/realizacion',['Asistio_Gimnasta'=>1])->assertUnprocessable();$this->assertDatabaseHas('sesiones_entrenamiento',['ID_Sesion'=>$session->ID_Sesion,'Estado'=>'Programada']);$this->proof('DEEP-019',['futureSessionCannotComplete'=>true]);}
 public function test_deep_020():void {$session=$this->sessionFixture(['Fecha'=>'2030-06-16']);$this->actor(1)->deleteJson('/sesiones/'.$session->ID_Sesion)->assertOk()->assertJson(['success'=>true]);$this->assertDatabaseHas('sesiones_entrenamiento',['ID_Sesion'=>$session->ID_Sesion,'Estado'=>'Cancelada']);$this->proof('DEEP-020',['scheduledSessionCancelled'=>true]);}
 public function test_deep_021():void {$session=$this->sessionFixture(['Estado'=>'Completada','Realizada_En'=>'2030-06-14 11:00:00','Asistio_Gimnasta'=>true]);$this->actor(1)->deleteJson('/sesiones/'.$session->ID_Sesion)->assertBadRequest();$this->assertDatabaseHas('sesiones_entrenamiento',['ID_Sesion'=>$session->ID_Sesion,'Estado'=>'Completada']);$this->proof('DEEP-021',['completedSessionProtected'=>true]);}
 public function test_deep_022():void {$session=$this->sessionFixture();$this->actor(2)->patchJson('/sesiones/'.$session->ID_Sesion.'/realizacion',['Asistio_Gimnasta'=>1])->assertForbidden();$this->assertDatabaseHas('sesiones_entrenamiento',['ID_Sesion'=>$session->ID_Sesion,'Estado'=>'Programada']);$this->proof('DEEP-022',['athleteCannotRecordCompletion'=>true]);}
 public function test_deep_023():void {$session=$this->sessionFixture(['Fecha'=>'2030-06-16']);\App\Models\Usuario::create(['ID_Usuario'=>7,'ID_Rol'=>2,'NombreUsuario'=>'qa_plan_7','Nombre'=>'Atleta','Apellido'=>'Segundo','Email'=>'plan7@example.test','Contrasena'=>Hash::make('qa'),'CedulaIdentidad'=>'21000007','Nacionalidad'=>'Bolivia','activo'=>true,'Genero'=>'M','Categoria'=>'Senior','Nivel'=>'Intermedio','FechaNacimiento'=>'2010-05-05','Peso'=>60,'Altura'=>1.65]);$apparatus=$this->apparatus();$data=['ID_Gimnasta'=>7,'ID_Entrenador'=>3,'ID_Aparato'=>$apparatus->ID_Aparato,'Fecha'=>'2030-06-16','Hora_Inicio'=>'10:00','Hora_Fin'=>'11:00'];$this->actor(1)->postJson('/sesiones',$data)->assertUnprocessable()->assertJsonValidationErrors('Hora_Fin');$this->assertDatabaseCount('sesiones_entrenamiento',1);$this->proof('DEEP-023',['trainerOverlapRejected'=>true]);}
 public function test_deep_024():void {$apparatus=$this->apparatus();$apparatus->update(['EstadoMantenimiento'=>'Mantenimiento']);$this->actor(1)->postJson('/sesiones',['ID_Gimnasta'=>2,'ID_Entrenador'=>3,'ID_Aparato'=>$apparatus->ID_Aparato,'Fecha'=>'2030-06-16','Hora_Inicio'=>'10:00','Hora_Fin'=>'11:00'])->assertUnprocessable()->assertJsonValidationErrors('ID_Aparato');$this->assertDatabaseCount('sesiones_entrenamiento',0);$this->proof('DEEP-024',['maintenanceApparatusRejected'=>true]);}
 public function test_deep_025():void {$this->actor(4)->postJson('/analisis/aptitud',['ID_Gimnasta'=>2,'FechaCertificacion'=>'2030-06-16','AptoParaEntrenar'=>true])->assertUnprocessable()->assertJsonValidationErrors('FechaCertificacion');$this->assertDatabaseCount('aptitud_medica',0);$this->proof('DEEP-025',['futureMedicalCertificateRejected'=>true]);}
 public function test_deep_026():void {$this->actor(4)->postJson('/analisis/nutricional',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','Peso'=>60,'Altura'=>1.65,'IMC'=>22])->assertUnprocessable()->assertJsonValidationErrors('IMC');$this->assertDatabaseCount('evaluaciones_nutricionales',0);$this->proof('DEEP-026',['derivedBmiCannotBeSupplied'=>true]);}
 public function test_deep_027():void {$this->actor(4)->postJson('/analisis/psicologica',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','NivelAnsiedad'=>11])->assertUnprocessable()->assertJsonValidationErrors('NivelAnsiedad');$this->assertDatabaseCount('evaluaciones_psicologicas',0);$this->proof('DEEP-027',['psychologyScaleBounded'=>true]);}
 public function test_deep_028():void {$this->actor(4)->postJson('/analisis/fisiologica',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','SaturacionOxigeno'=>100.1])->assertUnprocessable()->assertJsonValidationErrors('SaturacionOxigeno');$this->assertDatabaseCount('evaluaciones_fisiologicas',0);$this->proof('DEEP-028',['oxygenSaturationBounded'=>true]);}
 public function test_deep_029():void {$this->actor(4)->postJson('/analisis/respiratoria',['ID_Gimnasta'=>2,'CapacidadPulmonar'=>4.2])->assertUnprocessable()->assertJsonValidationErrors('FechaEvaluacion');$this->assertDatabaseCount('evaluaciones_respiratorias',0);$this->proof('DEEP-029',['respiratoryAssessmentDateRequired'=>true]);}
 public function test_deep_030():void {$this->actor(2)->postJson('/analisis/aptitud',['ID_Gimnasta'=>2,'FechaCertificacion'=>'2030-06-14','AptoParaEntrenar'=>true])->assertForbidden();$this->assertDatabaseCount('aptitud_medica',0);$this->proof('DEEP-030',['medicalAssessmentRequiresPermission'=>true]);}
 public function test_deep_031():void {$this->actor(4)->postJson('/api/sepi/diagnosticar',['ID_Gimnasta'=>2,'ansiedad'=>11,'estres'=>5,'resiliencia'=>5,'concentracion'=>5])->assertUnprocessable()->assertJsonValidationErrors('ansiedad');$this->assertDatabaseCount('sepi_evaluaciones',0);$this->proof('DEEP-031',['sepiRangeRejected'=>true]);}
 public function test_deep_032():void {$this->actor(4)->postJson('/rehabilitacion/crear-plan',[])->assertUnprocessable()->assertJsonValidationErrors(['lesion_id','fecha_inicio']);$this->assertDatabaseCount('planes_rehabilitacion',0);$this->proof('DEEP-032',['rehabilitationInputsRequired'=>true]);}
 public function test_deep_033():void {$lesion=\App\Models\Lesion::create(['ID_Gimnasta'=>2,'ID_Medico'=>4,'Tipo'=>'Muscular','ParteCuerpo'=>'Espalda','Gravedad'=>'Leve','FechaLesion'=>'2030-06-14','FechaDiagnostico'=>'2030-06-14','Estado'=>'Lesionado','activo'=>true]);$this->actor(4)->postJson('/rehabilitacion/crear-plan',['lesion_id'=>$lesion->ID_Lesion,'fecha_inicio'=>'2030-06-14'])->assertUnprocessable()->assertJsonValidationErrors('fecha_inicio');$this->assertDatabaseCount('planes_rehabilitacion',0);$this->proof('DEEP-033',['pastRehabilitationStartRejected'=>true]);}
 public function test_deep_034():void {$lesion=\App\Models\Lesion::create(['ID_Gimnasta'=>2,'ID_Medico'=>6,'Tipo'=>'Muscular','ParteCuerpo'=>'Espalda','Gravedad'=>'Leve','FechaLesion'=>'2030-06-14','FechaDiagnostico'=>'2030-06-14','Estado'=>'Lesionado','activo'=>true]);$this->actor(4)->postJson('/rehabilitacion/crear-plan',['lesion_id'=>$lesion->ID_Lesion,'fecha_inicio'=>'2030-06-16'])->assertForbidden();$this->assertDatabaseCount('planes_rehabilitacion',0);$this->proof('DEEP-034',['nonOwnerMedicalUserDenied'=>true]);}
 public function test_deep_035():void {$data=['athlete_id'=>2,'source'=>'manual','manual_key'=>'codo_izq','width'=>1000,'height'=>1000,'timestamp_seconds'=>604801,'timestamp_source'=>'video_current_time','landmarks'=>[['x'=>0.1,'y'=>0.2],['x'=>0.2,'y'=>0.3],['x'=>0.3,'y'=>0.4]]];$this->actor(1)->postJson('/analisis/mediciones',$data)->assertUnprocessable()->assertJsonValidationErrors('timestamp_seconds');$this->assertDatabaseCount('video_measurements',0);$this->proof('DEEP-035',['captureTimestampBounded'=>true]);}
 public function test_deep_036():void {$data=['athlete_id'=>2,'source'=>'manual','manual_key'=>'codo_izq','width'=>1000,'height'=>1000,'timestamp_seconds'=>1,'timestamp_source'=>'video_current_time','landmarks'=>[['x'=>0.1,'y'=>0.2],['x'=>0.2,'y'=>0.3]]];$this->actor(1)->postJson('/analisis/mediciones',$data)->assertUnprocessable();$this->assertDatabaseCount('video_measurements',0);$this->proof('DEEP-036',['manualLandmarkCountChecked'=>true]);}
 public function test_deep_037():void {$data=['athlete_id'=>2,'source'=>'manual','manual_key'=>'codo_izq','width'=>1000,'height'=>1000,'timestamp_seconds'=>1,'timestamp_source'=>'video_current_time','landmarks'=>[['x'=>0.1,'y'=>0.2,'visibility'=>1.1],['x'=>0.2,'y'=>0.3],['x'=>0.3,'y'=>0.4]]];$this->actor(1)->postJson('/analisis/mediciones',$data)->assertUnprocessable()->assertJsonValidationErrors('landmarks.0.visibility');$this->assertDatabaseCount('video_measurements',0);$this->proof('DEEP-037',['landmarkVisibilityBounded'=>true]);}
 public function test_deep_038():void {$data=['source'=>'manual','manual_key'=>'codo_izq','width'=>1000,'height'=>1000,'timestamp_seconds'=>1,'timestamp_source'=>'video_current_time','landmarks'=>[['x'=>0.1,'y'=>0.2],['x'=>0.2,'y'=>0.3],['x'=>0.3,'y'=>0.4]]];$this->actor(1)->postJson('/analisis/mediciones',$data)->assertUnprocessable();$this->assertDatabaseCount('video_measurements',0);$this->proof('DEEP-038',['captureRequiresAthlete'=>true]);}
 public function test_deep_039():void {$this->actor(1)->postJson('/analisis/fig/propuestas',['measurement_id'=>1,'apparatus_id'=>1,'task_id'=>'qa','element'=>'Elemento QA','phase'=>'mantenimiento','event_key'=>'invalid key','axis_side'=>'izq','camera_reference_confirmed'=>1,'rule_applicability_confirmed'=>1,'applicability_note'=>'Nota de aplicabilidad QA'])->assertUnprocessable()->assertJsonValidationErrors('event_key');$this->assertDatabaseCount('fig_deduction_proposals',0);$this->proof('DEEP-039',['figEventKeySanitized'=>true]);}
 public function test_deep_040():void {$this->actor(1)->postJson('/analisis/fig/propuestas',['measurement_id'=>1,'apparatus_id'=>1,'task_id'=>'qa','element'=>'Elemento QA','phase'=>'mantenimiento','event_key'=>'qa_event_1','axis_side'=>'izq','rule_applicability_confirmed'=>1,'applicability_note'=>'Nota de aplicabilidad QA'])->assertUnprocessable()->assertJsonValidationErrors('camera_reference_confirmed');$this->assertDatabaseCount('fig_deduction_proposals',0);$this->proof('DEEP-040',['figCameraConfirmationRequired'=>true]);}
 public function test_deep_041():void {$this->actor(1)->postJson('/sistema-experto/probar-motor',['dominio'=>'dominio_inexistente','hechos'=>[]])->assertUnprocessable()->assertJsonValidationErrors('dominio');$this->proof('DEEP-041',['unknownRuleDomainRejected'=>true]);}
 public function test_deep_042():void {$this->actor(1)->postJson('/sistema-experto/probar-motor',['dominio'=>'gimnasia','hechos'=>array_fill(0,26,'x')])->assertUnprocessable()->assertJsonValidationErrors('hechos');$this->proof('DEEP-042',['factCountBounded'=>true]);}
 public function test_deep_043():void {$this->actor(1)->postJson('/sistema-experto/probar-motor',['dominio'=>'gimnasia','hechos'=>['hecho-invalido'=>'<b>QA</b>']])->assertStatus(422)->assertJsonPath('error','Nombre de hecho invalido: hecho-invalido');$this->proof('DEEP-043',['factKeyConstrained'=>true]);}
 public function test_deep_044():void {$this->actor(1)->postJson('/usuarios-unificado',['Nombre'=>'A','Email'=>'correo-invalido'])->assertUnprocessable()->assertJsonValidationErrors(['Email','CedulaIdentidad','ID_Rol','Nombre','Apellido']);$this->assertDatabaseCount('usuarios',6);$this->proof('DEEP-044',['userRequiredFieldsEnforced'=>true]);}
 public function test_deep_045():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['Fecha'=>'2030-09-16']))->assertUnprocessable()->assertJsonValidationErrors('Fecha');$this->assertDatabaseCount('sesiones_entrenamiento',0);$this->proof('DEEP-045',['schedulingHorizonBounded'=>true]);}
 public function test_deep_046():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['Hora_Inicio'=>'05:59','Hora_Fin'=>'06:45']))->assertUnprocessable()->assertJsonValidationErrors('Hora_Inicio');$this->proof('DEEP-046',['openingHourBounded'=>true]);}
 public function test_deep_047():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['Hora_Inicio'=>'22:30','Hora_Fin'=>'23:30']))->assertUnprocessable()->assertJsonValidationErrors('Hora_Inicio');$this->proof('DEEP-047',['closingHourBounded'=>true]);}
 public function test_deep_048():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['Hora_Inicio'=>'11:00','Hora_Fin'=>'10:30']))->assertUnprocessable()->assertJsonValidationErrors('Hora_Fin');$this->proof('DEEP-048',['endMustFollowStart'=>true]);}
 public function test_deep_049():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['Hora_Fin'=>'10:20']))->assertUnprocessable()->assertJsonValidationErrors('Hora_Fin');$this->proof('DEEP-049',['minimumSessionDuration'=>true]);}
 public function test_deep_050():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['Hora_Fin'=>'14:00']))->assertUnprocessable()->assertJsonValidationErrors('Hora_Fin');$this->proof('DEEP-050',['maximumSessionDuration'=>true]);}
 public function test_deep_051():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['ID_Entrenador'=>4]))->assertUnprocessable()->assertJsonValidationErrors('ID_Entrenador');$this->assertDatabaseCount('sesiones_entrenamiento',0);$this->proof('DEEP-051',['medicalRoleCannotBeAssignedAsCoach'=>true]);}
 public function test_deep_052():void {$this->actor(1)->postJson('/sesiones',$this->sessionData(['ID_Gimnasta'=>3]))->assertUnprocessable()->assertJsonValidationErrors('ID_Gimnasta');$this->assertDatabaseCount('sesiones_entrenamiento',0);$this->proof('DEEP-052',['nonAthleteCannotReceiveSession'=>true]);}
 public function test_deep_053():void {$a=$this->apparatus();$a->update(['activo'=>false]);$this->actor(1)->postJson('/sesiones',$this->sessionData(['ID_Aparato'=>$a->ID_Aparato]))->assertUnprocessable()->assertJsonValidationErrors('ID_Aparato');$this->assertDatabaseCount('sesiones_entrenamiento',0);$this->proof('DEEP-053',['inactiveApparatusRejected'=>true]);}
 public function test_deep_054():void {$a=$this->apparatus();$a->update(['ProximoMantenimiento'=>'2030-06-16']);$this->actor(1)->postJson('/sesiones',$this->sessionData(['ID_Aparato'=>$a->ID_Aparato]))->assertUnprocessable()->assertJsonValidationErrors('ID_Aparato');$this->assertDatabaseCount('sesiones_entrenamiento',0);$this->proof('DEEP-054',['maintenanceDateConflictRejected'=>true]);}
 public function test_deep_055():void {$this->actor(4)->postJson('/analisis/nutricional',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','Peso'=>150.1,'Altura'=>1.65])->assertUnprocessable()->assertJsonValidationErrors('Peso');$this->proof('DEEP-055',['weightUpperBoundEnforced'=>true]);}
 public function test_deep_056():void {$this->actor(4)->postJson('/analisis/nutricional',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','Peso'=>60,'Altura'=>2.21])->assertUnprocessable()->assertJsonValidationErrors('Altura');$this->proof('DEEP-056',['heightUpperBoundEnforced'=>true]);}
 public function test_deep_057():void {$this->actor(4)->postJson('/analisis/nutricional',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','PorcentajeGrasa'=>100.1])->assertUnprocessable()->assertJsonValidationErrors('PorcentajeGrasa');$this->proof('DEEP-057',['bodyFatPercentageBounded'=>true]);}
 public function test_deep_058():void {$this->actor(4)->postJson('/analisis/respiratoria',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','SaturacionOxigeno'=>100.1])->assertUnprocessable()->assertJsonValidationErrors('SaturacionOxigeno');$this->proof('DEEP-058',['respiratoryOxygenBounded'=>true]);}
 public function test_deep_059():void {$this->actor(4)->postJson('/analisis/psicologica',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','NivelEstres'=>-1])->assertUnprocessable()->assertJsonValidationErrors('NivelEstres');$this->proof('DEEP-059',['stressScaleLowerBounded'=>true]);}
 public function test_deep_060():void {$this->actor(4)->postJson('/analisis/psicologica',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','NivelMotivacion'=>11])->assertUnprocessable()->assertJsonValidationErrors('NivelMotivacion');$this->proof('DEEP-060',['motivationScaleUpperBounded'=>true]);}
 public function test_deep_061():void {$this->actor(4)->postJson('/analisis/fisiologica',['ID_Gimnasta'=>2,'FechaEvaluacion'=>'2030-06-14','FCReposo'=>1001])->assertUnprocessable()->assertJsonValidationErrors('FCReposo');$this->proof('DEEP-061',['restingHeartRateBounded'=>true]);}
 public function test_deep_062():void {$this->actor(4)->postJson('/analisis/aptitud',['ID_Gimnasta'=>2,'FechaCertificacion'=>'2030-06-14'])->assertUnprocessable()->assertJsonValidationErrors('AptoParaEntrenar');$this->proof('DEEP-062',['fitnessDecisionRequired'=>true]);}
 public function test_deep_063():void {$this->actor(4)->postJson('/analisis/nutricional',['ID_Gimnasta'=>3,'FechaEvaluacion'=>'2030-06-14'])->assertUnprocessable()->assertJsonValidationErrors('ID_Gimnasta');$this->proof('DEEP-063',['assessmentTargetMustBeAthlete'=>true]);}
 public function test_deep_064():void {$this->actor(5)->postJson('/analisis/aptitud',['ID_Gimnasta'=>2,'FechaCertificacion'=>'2030-06-14','AptoParaEntrenar'=>true])->assertForbidden();$this->assertDatabaseCount('aptitud_medica',0);$this->proof('DEEP-064',['judgeCannotWriteMedicalAssessment'=>true]);}
 public function test_deep_065():void {$this->actor(4)->postJson('/api/sepi/diagnosticar',[])->assertUnprocessable()->assertJsonValidationErrors(['ID_Gimnasta','ansiedad','estres','resiliencia','concentracion']);$this->proof('DEEP-065',['sepiRequiredMetricsEnforced'=>true]);}
 public function test_deep_066():void {$this->actor(4)->postJson('/api/sepi/diagnosticar',['ID_Gimnasta'=>999,'ansiedad'=>5,'estres'=>5,'resiliencia'=>5,'concentracion'=>5])->assertUnprocessable()->assertJsonValidationErrors('ID_Gimnasta');$this->proof('DEEP-066',['sepiRequiresExistingAthlete'=>true]);}
 public function test_deep_067():void {$this->actor(4)->postJson('/api/sepi/diagnosticar',['ID_Gimnasta'=>2,'ansiedad'=>5,'estres'=>5,'resiliencia'=>5,'concentracion'=>5,'cohesion_equipo'=>11])->assertUnprocessable()->assertJsonValidationErrors('cohesion_equipo');$this->proof('DEEP-067',['teamCohesionBounded'=>true]);}
 public function test_deep_068():void {$this->actor(4)->postJson('/api/sepi/diagnosticar',['ID_Gimnasta'=>2,'ansiedad'=>-1,'estres'=>5,'resiliencia'=>5,'concentracion'=>5])->assertUnprocessable()->assertJsonValidationErrors('ansiedad');$this->proof('DEEP-068',['sepiLowerBoundEnforced'=>true]);}
 public function test_deep_069():void {$this->actor(2)->postJson('/api/sepi/diagnosticar',['ID_Gimnasta'=>2,'ansiedad'=>5,'estres'=>5,'resiliencia'=>5,'concentracion'=>5])->assertForbidden();$this->proof('DEEP-069',['athleteCannotInvokeClinicalSepiApi'=>true]);}
 public function test_deep_070():void {$this->actor(4)->postJson('/api/sepi/diagnosticar',['ID_Gimnasta'=>3,'ansiedad'=>5,'estres'=>5,'resiliencia'=>5,'concentracion'=>5])->assertForbidden();$this->proof('DEEP-070',['sepiCannotTargetNonAthlete'=>true]);}
 public function test_deep_071():void {$this->actor(4)->postJson('/rehabilitacion/crear-plan',['lesion_id'=>999,'fecha_inicio'=>'2030-06-16'])->assertUnprocessable()->assertJsonValidationErrors('lesion_id');$this->assertDatabaseCount('planes_rehabilitacion',0);$this->proof('DEEP-071',['unknownInjuryRejected'=>true]);}
 public function test_deep_072():void {$this->actor(4)->postJson('/rehabilitacion/crear-plan',['fecha_inicio'=>'2030-06-16'])->assertUnprocessable()->assertJsonValidationErrors('lesion_id');$this->proof('DEEP-072',['rehabilitationNeedsInjury'=>true]);}
 public function test_deep_073():void {$this->actor(2)->postJson('/rehabilitacion/crear-plan',['lesion_id'=>1,'fecha_inicio'=>'2030-06-16'])->assertForbidden();$this->assertDatabaseCount('planes_rehabilitacion',0);$this->proof('DEEP-073',['athleteCannotCreateRehabilitationPlan'=>true]);}
 public function test_deep_074():void {$this->actor(4)->putJson('/rehabilitacion/fase/999',['estado'=>'Invalido'])->assertUnprocessable()->assertJsonValidationErrors('estado');$this->proof('DEEP-074',['rehabilitationPhaseStateValidated'=>true]);}
 public function test_deep_075():void {$this->actor(4)->putJson('/rehabilitacion/fase/999',['estado'=>'Completada'])->assertNotFound();$this->proof('DEEP-075',['missingFollowupNotFound'=>true]);}
 public function test_deep_076():void {$this->actor(4)->deleteJson('/rehabilitacion/plan/999/cancelar')->assertNotFound();$this->proof('DEEP-076',['unknownPlanCannotBeCancelled'=>true]);}
 public function test_deep_077():void {$this->actor(1)->postJson('/analisis/mediciones',$this->measurementData(['timestamp_seconds'=>-0.1]))->assertUnprocessable()->assertJsonValidationErrors('timestamp_seconds');$this->proof('DEEP-077',['negativeVideoTimestampRejected'=>true]);}
 public function test_deep_078():void {$this->actor(1)->postJson('/analisis/mediciones',$this->measurementData(['source'=>'unknown']))->assertUnprocessable()->assertJsonValidationErrors('source');$this->proof('DEEP-078',['measurementSourceAllowlisted'=>true]);}
 public function test_deep_079():void {$this->actor(1)->postJson('/analisis/mediciones',$this->measurementData(['width'=>0]))->assertUnprocessable()->assertJsonValidationErrors('width');$this->proof('DEEP-079',['frameWidthPositive'=>true]);}
 public function test_deep_080():void {$this->actor(1)->postJson('/analisis/mediciones',$this->measurementData(['height'=>16385]))->assertUnprocessable()->assertJsonValidationErrors('height');$this->proof('DEEP-080',['frameHeightBounded'=>true]);}
 public function test_deep_081():void {$points=array_fill(0,34,['x'=>0.2,'y'=>0.3]);$this->actor(1)->postJson('/analisis/mediciones',$this->measurementData(['source'=>'pose','manual_key'=>null,'landmarks'=>$points]))->assertUnprocessable()->assertJsonValidationErrors('landmarks');$this->proof('DEEP-081',['poseLandmarkCountCapped'=>true]);}
 public function test_deep_082():void {$points=[['x'=>10.1,'y'=>0.2],['x'=>0.2,'y'=>0.3],['x'=>0.3,'y'=>0.4]];$this->actor(1)->postJson('/analisis/mediciones',$this->measurementData(['landmarks'=>$points]))->assertUnprocessable()->assertJsonValidationErrors('landmarks.0.x');$this->proof('DEEP-082',['landmarkCoordinatesBounded'=>true]);}
 public function test_deep_083():void {$points=[['x'=>0.1,'y'=>-10.1],['x'=>0.2,'y'=>0.3],['x'=>0.3,'y'=>0.4]];$this->actor(1)->postJson('/analisis/mediciones',$this->measurementData(['landmarks'=>$points]))->assertUnprocessable()->assertJsonValidationErrors('landmarks.0.y');$this->proof('DEEP-083',['landmarkVerticalCoordinatesBounded'=>true]);}
 public function test_deep_084():void {$this->actor(1)->postJson('/analisis/mediciones',$this->measurementData(['manual_key'=>'dedo_inventado']))->assertUnprocessable()->assertJsonValidationErrors('manual_key');$this->proof('DEEP-084',['manualAngleKeyAllowlisted'=>true]);}
 public function test_deep_085():void {$this->actor(1)->postJson('/analisis/mediciones/999/correcciones',[])->assertUnprocessable()->assertJsonValidationErrors(['angles','reason']);$this->proof('DEEP-085',['correctionRequiresAnglesAndReason'=>true]);}
 public function test_deep_086():void {$this->actor(1)->postJson('/analisis/mediciones/999/correcciones',['angles'=>['codo_izq'=>181],'reason'=>'Corrección de medición inválida'])->assertUnprocessable()->assertJsonValidationErrors('angles.codo_izq');$this->proof('DEEP-086',['correctedAngleBounded'=>true]);}
 public function test_deep_087():void {$this->actor(1)->postJson('/analisis/fig/propuestas',['measurement_id'=>1,'apparatus_id'=>1,'task_id'=>'qa','element'=>'Elemento QA','phase'=>'fase_invalida','event_key'=>'qa_event_1','axis_side'=>'izq','camera_reference_confirmed'=>1,'rule_applicability_confirmed'=>1,'applicability_note'=>'Nota de aplicabilidad QA'])->assertUnprocessable()->assertJsonValidationErrors('phase');$this->proof('DEEP-087',['figPhaseAllowlisted'=>true]);}
 public function test_deep_088():void {$this->actor(1)->postJson('/analisis/fig/propuestas',['measurement_id'=>1,'apparatus_id'=>1,'task_id'=>'qa','element'=>'Elemento QA','phase'=>'mantenimiento','event_key'=>'qa_event_1','axis_side'=>'centro','camera_reference_confirmed'=>1,'rule_applicability_confirmed'=>1,'applicability_note'=>'Nota de aplicabilidad QA'])->assertUnprocessable()->assertJsonValidationErrors('axis_side');$this->proof('DEEP-088',['figAxisSideAllowlisted'=>true]);}
 public function test_deep_089():void {$this->actor(1)->postJson('/analisis/fig/propuestas',['measurement_id'=>1,'apparatus_id'=>1,'task_id'=>'qa','element'=>'Elemento QA','phase'=>'mantenimiento','event_key'=>'qa_event_1','axis_side'=>'izq','camera_reference_confirmed'=>0,'rule_applicability_confirmed'=>1,'applicability_note'=>'Nota de aplicabilidad QA'])->assertUnprocessable()->assertJsonValidationErrors('camera_reference_confirmed');$this->proof('DEEP-089',['cameraMustBeConfirmed'=>true]);}
 public function test_deep_090():void {$this->actor(5)->postJson('/analisis/fig/propuestas/999/decision',['decision'=>'unknown','reason'=>'Motivo suficiente para revisión'])->assertUnprocessable()->assertJsonValidationErrors('decision');$this->proof('DEEP-090',['figDecisionAllowlisted'=>true]);}
 public function test_deep_091():void {$this->actor(5)->postJson('/analisis/fig/propuestas/999/decision',['decision'=>'corrected','reason'=>'Motivo suficiente para revisión'])->assertUnprocessable()->assertJsonValidationErrors('final_deduction');$this->proof('DEEP-091',['correctedDecisionRequiresDeduction'=>true]);}
 public function test_deep_092():void {$this->actor(5)->postJson('/analisis/fig/propuestas/999/decision',['decision'=>'accepted','reason'=>'corto'])->assertUnprocessable()->assertJsonValidationErrors('reason');$this->proof('DEEP-092',['figDecisionRequiresReason'=>true]);}
 public function test_deep_093():void {$this->actor(1)->postJson('/sistema-experto/reglas',[])->assertUnprocessable()->assertJsonValidationErrors(['Dominio','Codigo','Prioridad','CF','Condiciones','Conclusiones']);$this->assertDatabaseCount('reglas_produccion',0);$this->proof('DEEP-093',['ruleRequiredFieldsEnforced'=>true]);}
 public function test_deep_094():void {$this->actor(1)->postJson('/sistema-experto/reglas',['Dominio'=>'otro','Codigo'=>'QA-R-1','Prioridad'=>1,'CF'=>0.5,'Condiciones'=>[['hecho'=>'x','operador'=>'eq','valor'=>1]],'Conclusiones'=>[['hecho'=>'y','valor'=>1]]])->assertUnprocessable()->assertJsonValidationErrors('Dominio');$this->proof('DEEP-094',['ruleDomainAllowlisted'=>true]);}
 public function test_deep_095():void {$this->actor(1)->postJson('/sistema-experto/reglas',['Dominio'=>'gimnasia','Codigo'=>'QA-R-1','Prioridad'=>0,'CF'=>0.5,'Condiciones'=>[['hecho'=>'x','operador'=>'eq','valor'=>1]],'Conclusiones'=>[['hecho'=>'y','valor'=>1]]])->assertUnprocessable()->assertJsonValidationErrors('Prioridad');$this->proof('DEEP-095',['rulePriorityBounded'=>true]);}
 public function test_deep_096():void {$this->actor(1)->postJson('/sistema-experto/reglas',['Dominio'=>'gimnasia','Codigo'=>'QA-R-1','Prioridad'=>1,'CF'=>0.01,'Condiciones'=>[['hecho'=>'x','operador'=>'eq','valor'=>1]],'Conclusiones'=>[['hecho'=>'y','valor'=>1]]])->assertUnprocessable()->assertJsonValidationErrors('CF');$this->proof('DEEP-096',['ruleConfidenceBounded'=>true]);}
 public function test_deep_097():void {$this->actor(1)->postJson('/sistema-experto/reglas',['Dominio'=>'gimnasia','Codigo'=>'QA-R-1','Prioridad'=>1,'CF'=>0.5,'Conclusiones'=>[['hecho'=>'y','valor'=>1]]])->assertUnprocessable()->assertJsonValidationErrors('Condiciones');$this->proof('DEEP-097',['ruleNeedsCondition'=>true]);}
 public function test_deep_098():void {$this->actor(1)->postJson('/sistema-experto/reglas',['Dominio'=>'gimnasia','Codigo'=>'QA-R-1','Prioridad'=>1,'CF'=>0.5,'Condiciones'=>[['hecho'=>'x','operador'=>'eq','valor'=>1]]])->assertUnprocessable()->assertJsonValidationErrors('Conclusiones');$this->proof('DEEP-098',['ruleNeedsConclusion'=>true]);}
 public function test_deep_099():void {$this->actor(1)->postJson('/roles',['NombreRol'=>'admin'])->assertUnprocessable()->assertJsonValidationErrors('NombreRol');$this->assertDatabaseCount('roles',6);$this->proof('DEEP-099',['reservedRoleNameRejected'=>true]);}
 public function test_deep_100():void {$this->actor(1)->postJson('/roles',['NombreRol'=>'Gimnasta'])->assertUnprocessable()->assertJsonValidationErrors('NombreRol');$this->assertDatabaseCount('roles',6);$this->proof('DEEP-100',['duplicateRoleNameRejected'=>true]);}
 public function test_deep_101():void {$this->actor(1)->postJson('/roles',['NombreRol'=>'QA_Admin'])->assertUnprocessable()->assertJsonValidationErrors('NombreRol');$this->assertDatabaseCount('roles',6);$this->proof('DEEP-101',['roleNameCharacterSetEnforced'=>true]);}
 public function test_deep_102():void {$this->actor(1)->postJson('/usuarios-unificado',['Email'=>'nuevo@example.test','CedulaIdentidad'=>'33000001','Nacionalidad'=>'Bolivia','ID_Rol'=>2,'Nombre'=>'QA','Apellido'=>'Atleta','FechaNacimiento'=>'2010-05-05','Peso'=>60,'Altura'=>1.65,'Genero'=>'F','Categoria'=>'Senior','Nivel'=>'Intermedio'])->assertUnprocessable()->assertJsonValidationErrors('Genero');$this->assertDatabaseCount('usuarios',6);$this->proof('DEEP-102',['magGenderPolicyEnforced'=>true]);}
 public function test_deep_103():void {$this->actor(1)->patchJson('/ml/muestras/999/revision',[])->assertUnprocessable()->assertJsonValidationErrors(['review_status','review_note','element','label_deduction','label_error','rule_context']);$this->proof('DEEP-103',['mlReviewRequiredFieldsEnforced'=>true]);}
 public function test_deep_104():void {$this->actor(1)->patchJson('/ml/predicciones/999/revision',['judge_deduction'=>0.2])->assertUnprocessable()->assertJsonValidationErrors('judge_deduction');$this->proof('DEEP-104',['mlCorrectionAllowlisted'=>true]);}

 public function test_plan_tc_34():void {
  $a=$this->apparatus();$competition=\App\Models\Competencia::create(['NombreCompetencia'=>'QA control juez','Fecha'=>'2030-06-16','Ubicacion'=>'Prueba aislada','activo'=>true]);DB::table('competencia_gimnastas')->insert(['ID_Competencia'=>$competition->ID_Competencia,'ID_Gimnasta'=>2]);
  $video=new UploadedFile(__DIR__.'/../fixtures/qa-valid.webm','qa-valid.webm','video/webm',null,true);
  $created=$this->actor(5)->post('/juez/store',['ID_Gimnasta'=>2,'ID_Competencia'=>$competition->ID_Competencia,'ID_Aparato'=>$a->ID_Aparato,'video'=>$video],['Accept'=>'application/json']);$created->assertOk();$id=$created->json('evaluacion_id');$page=$this->get('/juez/evaluar/'.$id);$page->assertOk()->assertViewIs('juez.evaluar')->assertSee('id="togglePoseBtn"',false)->assertSee('id="scoreForm"',false)->assertSee('mediapipe/pose.js',false);
  $scored=$this->postJson('/juez/evaluar/'.$id.'/score',['nota_dificultad'=>5,'nota_ejecucion'=>9.5,'total_descuentos'=>999,'estado'=>'confirmado']);$scored->assertOk()->assertJsonPath('nota_final',14.5)->assertJsonPath('total_descuentos',0);$this->assertDatabaseHas('evaluaciones_juez',['ID_EvaluacionJuez'=>$id,'nota_final'=>14.5,'estado'=>'confirmado']);$this->postJson('/juez/evaluar/'.$id.'/score',['nota_dificultad'=>5,'nota_ejecucion'=>9,'estado'=>'confirmado'])->assertStatus(409);
  $this->proof('TC-34-partial',['status'=>200,'valid'=>true,'persisted'=>true,'blocked'=>true]);throw new RuntimeException('BLOCKED: TC-34 sin vídeo autorizado y referencia de inferencia MediaPipe. Controles de vista, calificación y cierre verificados; evaluación completa no validada.');
 }
}
