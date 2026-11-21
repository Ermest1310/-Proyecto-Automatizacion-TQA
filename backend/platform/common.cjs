const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto');
function fail(message,statusCode=422){throw Object.assign(new Error(message),{statusCode});}
function canonical(value){if(Array.isArray(value))return '['+value.map(canonical).join(',')+']';if(value&&typeof value==='object')return '{'+Object.keys(value).sort().filter(k=>value[k]!==undefined).map(k=>JSON.stringify(k)+':'+canonical(value[k])).join(',')+'}';return JSON.stringify(value);}
const hash=value=>crypto.createHash('sha256').update(Buffer.isBuffer(value)?value:typeof value==='string'?value:canonical(value)).digest('hex');
function atomic(file,value){fs.mkdirSync(path.dirname(file),{recursive:true});const temporary=file+'.'+crypto.randomBytes(6).toString('hex')+'.tmp';fs.writeFileSync(temporary,JSON.stringify(value,null,2),{flag:'wx',mode:0o600});fs.renameSync(temporary,file);}
function read(file){return JSON.parse(fs.readFileSync(file,'utf8'));}
function id(value){if(typeof value!=='string'||(!/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,99}$/.test(value)||/^(?:con|prn|aux|nul|com[1-9]|lpt[1-9])$/i.test(value)))fail('Identificador inválido');return value;}
function runId(value){if(typeof value!=='string'||!/^qa-[0-9TZ-]+-[a-f0-9]{12}$/.test(value))fail('ID de ejecución inválido',404);return value;}
function within(root,file,{exists=true}={}){const base=fs.realpathSync(root),resolved=path.resolve(root,file);if(!resolved.startsWith(path.resolve(root)+path.sep))fail('Ruta fuera del espacio permitido',403);if(exists){const actual=fs.realpathSync(resolved);if(!actual.startsWith(base+path.sep))fail('Enlace fuera del espacio permitido',403);return actual;}return resolved;}
function mkdirWithin(root,relative){within(root,relative,{exists:false});let current=root;for(const part of path.relative(root,path.resolve(root,relative)).split(path.sep)){current=path.join(current,part);if(!fs.existsSync(current))try{fs.mkdirSync(current);}catch(error){if(error.code!=='EEXIST')throw error;}within(root,path.relative(root,current));if(!fs.statSync(current).isDirectory())fail('La ruta de estado no es un directorio',409);}return current;}
function fields(input,allowed){if(!input||typeof input!=='object'||Array.isArray(input))fail('Se requiere un objeto JSON');for(const key of Object.keys(input))if(!allowed.includes(key))fail('Campo no permitido: '+key);}
function string(value,label,{min=1,max=2000}={}){if(typeof value!=='string'||value.length<min||(min>0&&!value.trim())||value.length>max||/[\u0000-\u0008\u000b\u000c\u000e-\u001f]/.test(value))fail(label+' inválido');return value;}
function integer(value,label,min,max){if(!Number.isInteger(value)||value<min||value>max)fail(label+' inválido');return value;}
function strings(value,label,{max=100,itemMax=150,unique=true}={}){if(!Array.isArray(value)||value.length>max)fail(label+' inválido');const mapped=value.map(v=>string(v,label,{max:itemMax}));return unique?[...new Set(mapped)]:mapped;}
function publicText(value){return require('../report-model.cjs').text(value);}
module.exports={fs,path,crypto,fail,canonical,hash,atomic,read,id,runId,within,mkdirWithin,fields,string,integer,strings,publicText};
