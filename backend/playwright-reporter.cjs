const fs=require('node:fs'),path=require('node:path');
const {classify,scrub}=require('./results.cjs');
class Reporter{
 constructor(){this.selection=JSON.parse(fs.readFileSync(process.env.QA_SELECTION_FILE,'utf8'));this.output=process.env.QA_RESULT_FILE;this.results=[];this.errors=[];}
 save(extra={}){const value=scrub({runId:process.env.QA_RUN_ID,updatedAt:new Date().toISOString(),results:this.results,errors:this.errors,...extra});const temp=this.output+'.tmp';fs.writeFileSync(temp,JSON.stringify(value,null,2));fs.renameSync(temp,this.output);}
 onBegin(_,suite){this.discovered=suite.allTests().length;this.save({discovered:this.discovered});}
 onTestEnd(test,result){const c=this.selection.cases.find(c=>c.source.testTitle===test.title);if(!c){this.errors.push('Runner ejecutó un caso fuera de selección');return;}
 const project=test.parent.project().name;
 let metrics=null;const attachment=(result.attachments||[]).find(a=>a.name==='performance-metrics'&&a.path);if(attachment){const file=path.resolve(attachment.path),base=path.resolve(path.dirname(process.env.QA_RESULT_FILE),'evidence');if(file.startsWith(base+path.sep)){try{metrics=JSON.parse(fs.readFileSync(file,'utf8'));}catch{this.errors.push('Métricas de rendimiento ilegibles');}}}
 let classification=classify(result);if(c.mode==='http-performance'&&result.status==='passed'&&(!metrics||!metrics.requests||!Array.isArray(metrics.criteria)||!metrics.criteria.length||!metrics.criteria.every(c=>c.passed===true))){this.errors.push('No se puede aprobar rendimiento sin métricas y criterios válidos');classification={status:'NOT_RUN',failureKind:'infrastructure'};}
 const evidenceBase=path.resolve(path.dirname(this.output),'evidence');for(const [index,a]of (result.attachments||[]).entries()){if(!a.path&&a.body&&a.contentType==='application/json'&&a.body.length<=65536){try{JSON.parse(a.body.toString());const dir=path.join(evidenceBase,'reporter-json');fs.mkdirSync(dir,{recursive:true});const file=path.join(dir,c.id+'-'+project+'-'+index+'-'+require('node:crypto').randomBytes(4).toString('hex')+'.json');fs.writeFileSync(file,a.body,{flag:'wx'});a.path=file;}catch{/* Un adjunto no legible no se publica. */}}}const attachments=(result.attachments||[]).filter(a=>a.path&&path.resolve(a.path).startsWith(evidenceBase+path.sep)).map(a=>({path:path.relative(path.dirname(this.output),path.resolve(a.path)),contentType:a.contentType||'application/octet-stream'}));
 this.results.push({attachments,caseId:c.id,name:c.source.testTitle,project,...classification,durationMs:result.duration,metrics,error:(result.errors||[]).map(e=>e.message||String(e)).join('\n').slice(0,12000),steps:(result.steps||[]).map(s=>({name:s.title,durationMs:s.duration,error:s.error?.message||null}))});this.save({discovered:this.discovered});}
 onError(error){this.errors.push(error.message||String(error));this.save();}
 onEnd(result){this.save({discovered:this.discovered,runnerStatus:result.status,finishedAt:new Date().toISOString()});}
}
module.exports=Reporter;
