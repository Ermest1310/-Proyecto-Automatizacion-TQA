let required;
try{({required}=require('../config/runtime.cjs'));}catch(e){console.error('QA_CONFIG: configuración privada no accesible ('+(e.code||'invalid')+')');process.exit(2);}
(async()=>{const args=process.argv.slice(2);const command=args.shift();let body={};if(command==='case')body.caseIds=args.shift()?.split(',');else if(command==='category')body.category=args.shift();else throw new Error('Uso: npm run qa:run -- case QA-SEC-002,QA-SEC-005 [chromium] | category security [chromium]');if(args[0])body.projects=args[0].split(',');
 const base='http://127.0.0.1:'+(process.env.QA_BACKEND_PORT||9876);const headers={Authorization:'Bearer '+required('QA_API_TOKEN'),'Content-Type':'application/json'};
 let r=await fetch(base+'/api/runs',{method:'POST',headers,body:JSON.stringify(body)});let run=await r.json();if(!r.ok)throw new Error(run.error);console.log(JSON.stringify(run));const id=run.id;
 while(['queued','running'].includes(run.status)){await new Promise(r=>setTimeout(r,1000));r=await fetch(base+'/api/runs/'+id,{headers});run=await r.json();}
 console.log(JSON.stringify({id,status:run.status,startedAt:run.startedAt,finishedAt:run.finishedAt,durationMs:run.durationMs,summary:run.summary,infrastructureError:run.infrastructureError},null,2));process.exitCode=run.status==='passed'?0:run.status==='tests_failed'?1:2;
})().catch(e=>{console.error(e.message);process.exitCode=2;});
