<?php
// External QA tests: load real application classes; never migrate or connect MySQL.
$source = getenv('QA_APP_SOURCE') ?: 'E:/laragon/www/Proyecto2';
require_once $source.'/tests/Feature/SecurityAccessTest.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

final class QaRegressionTest extends \Tests\Feature\SecurityAccessTest
{
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $source = getenv('QA_APP_SOURCE');
        $scratch = getenv('QA_STORAGE_DIR');
        if (!$scratch || !$source) throw new RuntimeException('BLOCKED: QA paths required');
        if (is_file($source.'/bootstrap/cache/config.php')) throw new RuntimeException('BLOCKED: Laravel config cache requires isolation review');
        foreach (['framework/cache/data', 'framework/sessions', 'framework/views', 'logs', 'app', 'public'] as $dir) {
            if (!is_dir($scratch.'/'.$dir)) mkdir($scratch.'/'.$dir, 0777, true);
        }
        $app = require $source.'/bootstrap/app.php';
        $app->useStoragePath($scratch);
        $app->usePublicPath($scratch.'/public');
        $app->make(Kernel::class)->bootstrap();
        // These overrides happen before the parent setup creates any schemas.
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('filesystems.disks.private_videos.root', $scratch.'/private-videos');
        $app['config']->set('filesystems.disks.public.root', $scratch.'/public-files');
        $app['config']->set('logging.channels.audit.path', $scratch.'/logs/audit.log');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->evidence('backend-isolation', ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_keys' => 1]);
        Schema::table('usuarios', fn (Blueprint $t) => $t->date('FechaNacimiento')->nullable());
        DB::table('usuarios')->update(['FechaNacimiento' => '2010-05-05']);
    }

    private function asQa(int $id): self
    {
        return $this->withSession(['usuario_id' => $id, 'usuario_rol' => \App\Models\Usuario::findOrFail($id)->rol->NombreRol]);
    }

    private function evidence(string $name, array $data): void
    {
        file_put_contents(getenv('QA_STORAGE_DIR').'/'.$name.'.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function test_qa_bug_004_download_control_and_traversal(): void
    {
        $directory = storage_path('app/backups');
        if (!is_dir($directory)) mkdir($directory, 0777, true);
        file_put_contents($directory.'/qa_control.sql', '-- QA controlled backup, no user data');
        file_put_contents(storage_path('app/.env'), 'QA_OUTSIDE_BACKUP_MARKER');
        $control = $this->asQa(1)->get('/admin/reportes/descargar-backup/qa_control.sql');
        $control->assertOk()->assertDownload('qa_control.sql');
        $this->assertSame('-- QA controlled backup, no user data', file_get_contents($control->baseResponse->getFile()->getPathname()));
        $traversal = $this->asQa(1)->get('/admin/reportes/descargar-backup/..%5C.env');
        $traversal->assertNotFound();
        $this->assertStringNotContainsString('QA_OUTSIDE_BACKUP_MARKER', $traversal->getContent());
        $this->assertFalse($traversal->headers->has('Content-Disposition'));
        $this->evidence('BUG-004', ['control_status' => $control->getStatusCode(), 'traversal_status' => $traversal->getStatusCode(), 'marker_leaked' => false]);
    }

    public function test_qa_bug_006_controlled_provider_failures(): void
    {
        $http = \Illuminate\Support\Facades\Http::class;
        config(['services.openai.key' => 'QA_DUMMY_NOT_A_REAL_KEY', 'services.openai.chat_model' => 'qa-controlled']);
        $http::preventStrayRequests();
        $failureMode = 'provider';
        $providerHit = false;
        $transportHit = false;
        $http::fake(function () use (&$failureMode, &$providerHit, &$transportHit, $http) {
            if ($failureMode === 'transport') {
                $transportHit = true;
                throw new \Illuminate\Http\Client\ConnectionException('QA_SECRET cURL error 60 internal path');
            }
            $providerHit = true;
            return $http::response(['error' => ['message' => 'QA_SECRET Exception stack provider detail', 'code' => 'qa_failure']], 500);
        });
        $provider = $this->asQa(1)->postJson('/api/psicologia/chat', ['message' => 'QA controlled failure']);
        $provider->assertStatus(502)->assertExactJson(['response' => 'El servicio de conversación no está disponible en este momento. Intenta nuevamente.']);
        $this->assertTrue($providerHit);
        $failureMode = 'transport';
        $transport = $this->asQa(1)->postJson('/api/psicologia/chat', ['message' => 'QA transport failure']);
        $transport->assertStatus(502)->assertExactJson(['response' => 'El servicio de conversación no está disponible en este momento. Intenta nuevamente.']);
        $this->assertTrue($transportHit);
        config(['services.openai.key' => null]);
        $missing = $this->asQa(1)->postJson('/api/psicologia/chat', ['message' => 'QA missing configuration']);
        $missing->assertStatus(503)->assertExactJson(['response' => 'El servicio de conversación no está configurado. Contacta al administrador.']);
        foreach ([$provider, $transport, $missing] as $response) {
            $this->assertStringNotContainsString('QA_SECRET', $response->getContent());
        }
        $this->evidence('BUG-006', ['provider_error' => 502, 'transport_error' => 502, 'missing_configuration' => 503, 'secrets_leaked' => false, 'network' => 'Http fake; stray requests forbidden']);
    }

    public function test_qa_bug_002_ampersand_registration(): void
    {
        $migration = require getenv('QA_APP_SOURCE').'/database/migrations/2025_09_23_215422_create_postulaciones_table.php';
        $migration->up();
        Schema::table('postulaciones', function (Blueprint $t) { $t->string('Genero')->nullable(); $t->string('Categoria')->nullable(); });
        $data = ['Nombre' => 'Pedro', 'Apellido' => 'Sotomayor Vargas', 'Email' => 'qa.control@example.test',
            'Nacionalidad' => 'Bolivia', 'CedulaIdentidad' => '76543210', 'FechaNacimiento' => '2010-05-05',
            'Genero' => 'M', 'Categoria' => 'Junior', 'Peso' => 60, 'Altura' => 1.62, 'Nivel' => 'Principiante'];
        $control = $this->postJson('/register', $data);
        $this->evidence('BUG-002-control', ['status' => $control->status(), 'body' => json_decode($control->getContent(), true), 'rows' => DB::table('postulaciones')->count()]);
        $control->assertRedirect(route('login'));
        $this->assertDatabaseHas('postulaciones', ['Email' => $data['Email'], 'Apellido' => $data['Apellido']]);
        $data['Apellido'] = 'Sotomayor & Vargas';
        $data['Email'] = 'qa.ampersand@example.test';
        $data['CedulaIdentidad'] = '76543211';
        $response = $this->postJson('/register', $data);
        $this->evidence('BUG-002-result', ['status' => $response->status(), 'body' => json_decode($response->getContent(), true),
            'persisted' => DB::table('postulaciones')->where('Email', $data['Email'])->first()]);
        // The original plan (table 3.2 TC-10; representative TC-03) explicitly requires a legitimate ampersand to survive.
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('postulaciones', ['Email' => $data['Email'], 'Apellido' => 'Sotomayor & Vargas']);
        $rows = DB::table('postulaciones')->count();
        $rejected = [];
        foreach (['<script>alert(1)</script>', 'Vargas; SELECT', str_repeat('A', 21), 'A', ''] as $i => $invalid) {
            $data['Apellido'] = $invalid;
            $data['Email'] = 'qa.invalid'.$i.'@example.test';
            $data['CedulaIdentidad'] = '8765432'.$i;
            $response = $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.($i + 10)])
                ->postJson('/register', $data);
            $response->assertUnprocessable()->assertJsonValidationErrors('Apellido');
            $this->assertSame($rows, DB::table('postulaciones')->count());
            $rejected[] = ['input' => $invalid, 'status' => $response->status(), 'persisted' => false];
        }
        $this->evidence('BUG-002-negative-controls', $rejected);

    }

    public function test_qa_bug_005_athlete_cannot_approve_other_athlete(): void
    {
        $migration = require getenv('QA_APP_SOURCE').'/database/migrations/2025_11_20_000001_create_solicitudes_nivel_table.php';
        $migration->up();
        $id = DB::table('solicitudes_nivel')->insertGetId(['ID_Gimnasta' => 7, 'nivel_actual' => 'Principiante',
            'nivel_solicitado' => 'Intermedio', 'tipo' => 'evaluacion', 'estado' => 'pendiente',
            'created_at' => now(), 'updated_at' => now()]);
        $before = (array) DB::table('solicitudes_nivel')->where('ID_Solicitud', $id)->first();
        $levels = DB::table('usuarios')->whereIn('ID_Usuario', [2,7])->orderBy('ID_Usuario')->pluck('Nivel', 'ID_Usuario')->all();
        $denied = $this->asQa(2)->postJson('/admin/solicitudes-nivel/'.$id.'/aprobar', ['comentarios_admin' => 'QA must be denied']);
        $after = (array) DB::table('solicitudes_nivel')->where('ID_Solicitud', $id)->first();
        $this->evidence('BUG-005', ['status' => $denied->status(), 'before' => $before, 'after' => $after,
            'levels_before' => $levels, 'levels_after' => DB::table('usuarios')->whereIn('ID_Usuario', [2,7])->orderBy('ID_Usuario')->pluck('Nivel', 'ID_Usuario')->all()]);
        $denied->assertForbidden()->assertJson(['error' => 'Sin permisos']);
        $this->assertSame($before, $after);
        $this->assertSame($levels, DB::table('usuarios')->whereIn('ID_Usuario', [2,7])->orderBy('ID_Usuario')->pluck('Nivel', 'ID_Usuario')->all());
        // A positive control proves this is an existing, actionable request and route.
        $approved = $this->asQa(1)->postJson('/admin/solicitudes-nivel/'.$id.'/aprobar');
        $approved->assertRedirect();
        $this->assertDatabaseHas('solicitudes_nivel', ['ID_Solicitud' => $id, 'estado' => 'aprobada', 'aprobado_por' => 1]);
        $this->assertDatabaseHas('usuarios', ['ID_Usuario' => 7, 'Nivel' => 'Intermedio']);
        $this->evidence('BUG-005-positive-control', ['status' => $approved->status(), 'approved_by' => 1, 'victim_level' => 'Intermedio']);
    }

    public function test_qa_bug_008_php_rejected_with_valid_business_fields(): void
    {
        Schema::create('aparatos', function (Blueprint $t) { $t->id('ID_Aparato'); $t->string('Nombre'); $t->boolean('activo')->default(true); });
        Schema::create('competencias', function (Blueprint $t) { $t->id('ID_Competencia'); $t->boolean('activo')->default(true); });
        Schema::create('competencia_gimnastas', function (Blueprint $t) { $t->integer('ID_Competencia'); $t->integer('ID_Gimnasta'); });
        Schema::table('evaluaciones_juez', function (Blueprint $t) {
            $t->integer('ID_Gimnasta')->nullable(); $t->integer('ID_Competencia')->nullable(); $t->integer('ID_Aparato')->nullable();
            $t->string('estado')->nullable(); $t->dateTime('fecha_evaluacion')->nullable(); $t->timestamps();
        });
        DB::table('aparatos')->insert(['ID_Aparato' => 1, 'Nombre' => 'Suelo', 'activo' => true]);
        DB::table('competencias')->insert(['ID_Competencia' => 1, 'activo' => true]);
        DB::table('competencia_gimnastas')->insert(['ID_Competencia' => 1, 'ID_Gimnasta' => 2]);
        $disk = Storage::disk('private_videos');
        $publicRoot = Storage::build(['driver' => 'local', 'root' => public_path()]);
        $publicDisk = Storage::disk('public');
        $beforeRows = DB::table('evaluaciones_juez')->count();
        $beforeFiles = $disk->allFiles();
        $beforePublic = $publicRoot->allFiles();
        $beforePublicDisk = $publicDisk->allFiles();
        $valid = ['ID_Gimnasta' => 2, 'ID_Competencia' => 1, 'ID_Aparato' => 1];
        $php = UploadedFile::fake()->createWithContent('qa_inert.php', '<?php /* QA marker only; no executable statements */ ?>');
        $response = $this->asQa(5)->post('/juez/store', $valid + ['video' => $php], ['Accept' => 'application/json']);
        $this->evidence('BUG-008-rejection', ['status' => $response->status(), 'body' => $response->json(),
            'rows_before' => $beforeRows, 'rows_after' => DB::table('evaluaciones_juez')->count(),
            'files_before' => $beforeFiles, 'files_after' => $disk->allFiles(),
            'public_before' => $beforePublic, 'public_after' => $publicRoot->allFiles(),
            'public_disk_before' => $beforePublicDisk, 'public_disk_after' => $publicDisk->allFiles()]);
        $response->assertUnprocessable()->assertJsonValidationErrors('video');
        $this->assertSame(['video'], array_keys($response->json('errors')));
        $this->assertSame($beforeRows, DB::table('evaluaciones_juez')->count());
        $this->assertSame($beforeFiles, $disk->allFiles());
        $this->assertSame($beforePublic, $publicRoot->allFiles());
        $this->assertSame($beforePublicDisk, $publicDisk->allFiles());
        // Genuine, inert WebM generated from a 16x16 black JPEG; no mocked MIME.
        $video = new UploadedFile(__DIR__.'/../fixtures/qa-valid.webm', 'qa-valid.webm', 'video/webm', null, true);
        $this->assertSame('video/webm', $video->getMimeType());
        $accepted = $this->asQa(5)->post('/juez/store', $valid + ['video' => $video], ['Accept' => 'application/json']);
        $accepted->assertOk()->assertJson(['success' => true]);
        $record = DB::table('evaluaciones_juez')->where('ID_EvaluacionJuez', $accepted->json('evaluacion_id'))->first();
        $this->assertNotNull($record);
        $this->assertTrue($disk->exists($record->video_ruta));
        $this->assertSame(file_get_contents(__DIR__.'/../fixtures/qa-valid.webm'), $disk->get($record->video_ruta));
        $this->assertCount(count($beforeFiles) + 1, $disk->allFiles());
        $this->assertFalse(str_contains($record->video_ruta, 'uploads/'));
        $this->assertSame($beforePublic, $publicRoot->allFiles());
        $this->assertSame($beforePublicDisk, $publicDisk->allFiles());
        $this->evidence('BUG-008-positive-control', ['status' => $accepted->status(), 'row_created' => true,
            'private_path' => $record->video_ruta, 'real_mime' => 'video/webm', 'bytes' => $video->getSize()]);
    }
}

