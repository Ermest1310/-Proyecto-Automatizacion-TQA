const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),cp=require('node:child_process');
const {root,appUrl,database,required}=require('../config/runtime.cjs');
function startApp({stdio='inherit',extraEnv={}}={}){database();required('QA_API_TOKEN');const source=required('QA_APP_SOURCE'),url=new URL(appUrl());
const storage=path.join(root,'reports/app-'+Date.now()+'-'+crypto.randomBytes(4).toString('hex'));fs.mkdirSync(storage,{recursive:true});fs.mkdirSync(path.join(root,'.runtime'),{recursive:true});
const env={...process.env,...extraEnv,QA_LIVE_STORAGE:storage,APP_URL:appUrl()};delete env.PHP_CLI_SERVER_WORKERS;
const child=cp.spawn(process.env.QA_PHP||'php',['-S',url.hostname+':'+url.port,'-t',path.join(source,'public'),path.join(root,'scripts/qa-live-router.php')],{env,windowsHide:true,stdio,shell:false});
fs.writeFileSync(path.join(root,'.runtime/app.json'),JSON.stringify({pid:child.pid,storage,url:appUrl(),startedAt:new Date().toISOString()}));console.log('Instancia QA: '+appUrl()+'; comprobar health autenticado.');
child.on('error',e=>{console.error('No se inició PHP: '+e.code);process.exitCode=2;});child.on('exit',code=>{process.exitCode=code??0;});return child;}
if(require.main===module){const child=startApp();for(const sig of ['SIGINT','SIGTERM'])process.on(sig,()=>child.kill());}
module.exports={startApp};
