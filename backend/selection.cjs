const fs=require('node:fs'),path=require('node:path');
const {email}=require('./email.cjs');
class InputError extends Error{constructor(message){super(message);this.statusCode=422;}}
function select(catalog,input){
 if(!input||typeof input!=='object'||Array.isArray(input))throw new InputError('Se requiere objeto JSON');
 for(const k of Object.keys(input))if(!['category','type','caseIds','projects','recipient'].includes(k))throw new InputError('Campo no permitido: '+k);
 if(Boolean(input.caseIds)!==Boolean(!input.category))throw new InputError('Indicar caseIds o category, exclusivamente');
 let projects=input.projects;if(projects!==undefined&&(!Array.isArray(projects)||!projects.length||projects.length>3||new Set(projects).size!==projects.length||projects.some(p=>!['chromium','firefox','webkit','http','demo'].includes(p))))throw new InputError('Proyectos inválidos');
 if(input.type&&!catalog.taxonomy.types.includes(input.type))throw new InputError('Tipo inválido');
 let cases;
 if(input.caseIds){if(!Array.isArray(input.caseIds)||!input.caseIds.length||input.caseIds.length>catalog.automatedCases.length||new Set(input.caseIds).size!==input.caseIds.length)throw new InputError('caseIds inválidos o duplicados');
 cases=input.caseIds.map(id=>{const c=catalog.automatedCases.find(c=>c.id===id);if(!c)throw new InputError('Caso automatizado desconocido: '+String(id));return c;});if(input.type&&cases.some(c=>c.type!==input.type))throw new InputError('Tipo incompatible con casos seleccionados');
 }else{if(input.category!=='all'&&!catalog.taxonomy.categories.includes(input.category))throw new InputError('Categoría desconocida');cases=catalog.automatedCases.filter(c=>c.discovery!=='explicit-config-only'&&(input.category!=='all'||c.discovery!=='demo-suite')&&(input.category!=='all'||c.mode!=='http-performance')&&(input.category==='all'||c.category===input.category)&&(!input.type||c.type===input.type));}
 if(!cases.length)throw new InputError('La selección no contiene pruebas implementadas');
 const configs=new Set(cases.map(c=>c.config||'playwright.config.ts'));if(configs.size!==1)throw new InputError('Ejecutar validaciones suplementarias en una corrida separada');
 const demo=cases.every(c=>c.mode==='qa-demo');const http=cases.every(c=>c.mode==='http-performance');projects=projects??(demo?['demo']:http?['http']:['chromium']);if(demo&&(projects.length!==1||projects[0]!=='demo'))throw new InputError('La demostración usa el proyecto demo de Chromium');if(!demo&&projects.includes('demo'))throw new InputError('Proyecto demo incompatible');if(http&&(projects.length!==1||projects[0]!=='http'))throw new InputError('Rendimiento usa el proyecto HTTP, no un motor de navegador');if(!http&&projects.includes('http'))throw new InputError('Proyecto HTTP incompatible con esta selección');
 if(cases.some(c=>c.discovery==='explicit-config-only')&&(projects.length!==1||projects[0]!=='chromium'))throw new InputError('La validación suplementaria es solo Chromium');
 return {category:input.category||null,type:input.type||null,recipient:email(input.recipient),projects,cases,config:[...configs][0]};
}
const escape=s=>s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
function runnerArgs(root,selection,runDir){
 const actualRoot=fs.realpathSync(root),config=path.resolve(root,selection.config);if(!config.startsWith(path.resolve(root)+path.sep)||!fs.existsSync(config)||!fs.realpathSync(config).startsWith(actualRoot+path.sep))throw new Error('QA_CONFIG: configuración fuera del proyecto');
 for(const c of selection.cases){const p=path.resolve(root,c.source.file);if(!p.startsWith(path.resolve(root)+path.sep)||!fs.existsSync(p)||!fs.realpathSync(p).startsWith(actualRoot+path.sep))throw new Error('QA_CONFIG: archivo del catálogo no disponible');const code=fs.readFileSync(p,'utf8');if(!code.includes("test('"+c.source.testTitle+"'"))throw new Error('QA_CONFIG: catálogo desactualizado para '+c.id);}
 const pattern='(?:^|\\s)(?:'+selection.cases.map(c=>escape(c.source.testTitle)).join('|')+')$';
 return [require.resolve('@playwright/test/cli'), 'test','--config',path.join(root,selection.config),...new Set(selection.cases.map(c=>c.source.file)), '--grep',pattern,...selection.projects.map(p=>'--project='+p),'--workers=1','--retries=0','--reporter='+path.join(root,'backend/playwright-reporter.cjs'),'--output='+path.join(runDir,'evidence')];
}
module.exports={select,runnerArgs,InputError};
