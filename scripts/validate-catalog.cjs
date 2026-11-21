const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const root=path.resolve(__dirname,'..');
const catalog=JSON.parse(fs.readFileSync(path.join(root,'config/test-catalog.json'),'utf8'));
assert.equal(catalog.originalCases.length,37);
assert.equal(new Set(catalog.originalCases.map(c=>c.id)).size,37);
assert.equal(catalog.automatedCases.filter(c=>c.discovery==='default-suite').length,18);
assert.equal(catalog.automatedCases.filter(c=>c.discovery==='explicit-config-only').length,1);
assert.equal(catalog.automatedCases.filter(c=>c.discovery==='performance-suite').length,3);
assert.equal(catalog.automatedCases.filter(c=>c.discovery==='demo-suite').length,2);
assert.equal(catalog.automatedCases.filter(c=>c.discovery==='plan-suite').length,20);
assert.equal(catalog.automatedCases.filter(c=>c.discovery==='expanded-suite').length,127);
const ids=new Set(catalog.automatedCases.map(c=>c.id));
assert.equal(ids.size,catalog.automatedCases.length);
for(let i=1;i<=37;i++)assert(catalog.originalCases.some(c=>c.id==='TC-'+String(i).padStart(2,'0')));
for(const c of [...catalog.originalCases,...catalog.automatedCases]){
 for(const field of ['id','name','objective','type','category','preconditions','testData','steps','expectedResult'])assert(c[field],c.id+' sin '+field);
 assert(c.steps.length>0&&c.preconditions.length>0,c.id+' sin detalle');
 assert(catalog.taxonomy.types.includes(c.type));assert(catalog.taxonomy.categories.includes(c.category));
 assert.equal(c.testData.containsSecrets,false);
}
for(const c of catalog.originalCases){
 assert(['complete','partial','pending'].includes(c.coverage));
 for(const id of c.automationIds)assert(ids.has(id),c.id+' enlaza '+id+' inexistente');
 if(c.coverage==='pending')assert.equal(c.automationIds.length,0);
}
for(const c of catalog.automatedCases){
 const file=path.resolve(root,c.source.file);assert(file.startsWith(root+path.sep));
 const source=fs.readFileSync(file,'utf8');assert(source.includes("test('"+c.source.testTitle+"'"),c.id+' no coincide con test real');
 for(const id of c.originalCaseIds)assert(catalog.originalCases.some(o=>o.id===id&&o.automationIds.includes(c.id)),c.id+' vínculo inverso faltante '+id);
}
console.log(JSON.stringify({valid:true,originalCases:catalog.originalCases.length,defaultAutomatedTests:catalog.summary.defaultAutomatedTests,originalSuiteTests:catalog.summary.originalSuiteTests,planExtension:catalog.summary.planExtensionTests,expandedSystemTests:catalog.summary.expandedSystemTests,supplementary:catalog.summary.explicitSupplementaryTests,performance:catalog.summary.performanceTests,demonstration:catalog.summary.demonstrationTests,totalExecutable:catalog.automatedCases.length,coverage:catalog.summary.coverage},null,2));
