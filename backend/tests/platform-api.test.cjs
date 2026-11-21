const test=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),os=require('node:os');
const {start}=require('../server.cjs');
test('API de evidencias mantiene autenticación, privacidad, integridad y rutas acotadas',async()=>{
 const root=fs.mkdtempSync(path.join(os.tmpdir(),'qa-platform-api-')),id='qa-2026-11-17T00-00-00-000Z-112233445566',dir=path.join(root,'reports/runs',id);fs.mkdirSync(path.join(dir,'evidence'),{recursive:true});
 const run={id,status:'passed',createdAt:new Date().toISOString(),finishedAt:new Date().toISOString(),selection:{recipient:'private@example.test',cases:[{id:'QA-UNIT',name:'Caso',objective:'Objetivo',steps:['Ejecutar'],expectedResult:'Aceptado',source:{file:'tests/unit.spec.ts'},mode:'phpunit-sqlite'}],projects:['chromium']},results:[{caseId:'QA-UNIT',project:'chromium',status:'PASS',durationMs:1}]};
 fs.writeFileSync(path.join(dir,'run.json'),JSON.stringify(run));fs.writeFileSync(path.join(dir,'evidence/proof.json'),JSON.stringify({status:200,persisted:{Nombre:'DO-NOT-PUBLISH',Email:'private@example.test'}}));
 const manager={root,catalog:{},list:()=>[run],dir:value=>{assert.equal(value,id);return dir;},read:value=>{assert.equal(value,id);return run;}},token='unit-only-platform-auth-token-32-characters',headers={Authorization:'Bearer '+token};
 const {server}=start({manager,port:0,token,lock:false});await new Promise(r=>server.once('listening',r));const base='http://127.0.0.1:'+server.address().port;
 try{
  assert.equal((await fetch(base+'/api/platform/history')).status,401);assert.equal((await fetch(base+'/api/platform/history',{headers:{...headers,Origin:'http://outside.invalid'}})).status,403);
  const route='/api/platform/runs/'+id;let response=await fetch(base+route+'/archive',{headers});assert.equal(response.status,200);const body=await response.text();assert(!body.includes('DO-NOT-PUBLISH'));assert(!body.includes('private@example.test'));assert.equal(JSON.parse(body).rows[0].status,'PASS');
  assert.equal((await fetch(base+route+'/evidence?caseId=unknown&project=chromium',{headers})).status,404);assert.equal((await fetch(base+'/api/platform/compare?base=invalid&target=invalid',{headers})).status,404);assert.equal((await fetch(base+'/reports/runs/'+id+'/evidence/proof.json',{headers})).status,404);
  const integrity=await (await fetch(base+route+'/integrity',{headers})).json();assert.equal(integrity.valid,true);
  fs.appendFileSync(path.join(dir,'evidence/proof.json'),' ');assert.equal((await fetch(base+route+'/archive',{headers})).status,409);const history=await (await fetch(base+'/api/platform/history',{headers})).json();assert.equal(history.runs[0].archiveAvailable,false);assert(!history.runs[0].totals);
 }finally{await new Promise(r=>server.close(r));}
});
