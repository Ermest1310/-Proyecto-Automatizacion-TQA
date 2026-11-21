const {redact,scrub}=require('../config/runtime.cjs');
function classify(result){if(result.status==='passed')return {status:'PASS',failureKind:null};if(result.status==='skipped')return {status:'SKIPPED',failureKind:null};if(result.status==='interrupted')return {status:'NOT_RUN',failureKind:'runner'};
 const message=(result.errors||[]).map(e=>e.message||String(e)).join('\n');
 const infra=/BLOCKED:|QA_CONFIG:|Executable doesn't exist|browserType\.launch|ECONNREFUSED|ENOTFOUND|ERR_CONNECTION_REFUSED|NS_ERROR_CONNECTION_REFUSED|ERR_NAME_NOT_RESOLVED|Could not connect to the server|ERR_NETWORK_ACCESS_DENIED|could not find driver|SQLSTATE\[HY000\] \[2002\]/i.test(message);
 return {status:infra?'NOT_RUN':'FAIL',failureKind:infra?'infrastructure':'test'};
}
function summary(rows){return {PASS:rows.filter(r=>r.status==='PASS').length,FAIL:rows.filter(r=>r.status==='FAIL').length,SKIPPED:rows.filter(r=>r.status==='SKIPPED').length,NOT_RUN:rows.filter(r=>r.status==='NOT_RUN').length,infrastructureCases:rows.filter(r=>r.failureKind==='infrastructure').length};}
function complete(selection,report,reason){const rows=report?.results||[];return selection.cases.flatMap(c=>selection.projects.map(project=>rows.find(r=>r.caseId===c.id&&r.project===project)||{caseId:c.id,name:c.source.testTitle,project,status:'NOT_RUN',failureKind:'runner',durationMs:0,error:redact(reason||'El runner no ejecutó este caso')}));}
module.exports={classify,summary,complete,scrub};
