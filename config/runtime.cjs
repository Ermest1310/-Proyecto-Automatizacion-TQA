const fs=require('node:fs'),path=require('node:path'),{parseEnv}=require('node:util');
const root=path.resolve(__dirname,'..');
const privateFile=path.join(root,'.env.local');
if(fs.existsSync(privateFile))for(const [k,v] of Object.entries(parseEnv(fs.readFileSync(privateFile,'utf8'))))if(process.env[k]===undefined)process.env[k]=v;
function required(key){const value=process.env[key];if(value===undefined||value==='')throw new Error('QA_CONFIG: falta '+key);return value;}
function appUrl(){const value=process.env.APP_URL||'http://127.0.0.1:8187';const u=new URL(value);if(!['127.0.0.1','localhost','[::1]'].includes(u.hostname)||u.protocol!=='http:'||u.username||u.password||u.search||u.hash||u.pathname!=='/')throw new Error('QA_CONFIG: APP_URL debe ser una instancia HTTP local de QA');return u.origin;}
function database(){const name=required('QA_DB_DATABASE');if(name!=='gimnasia_unificada_test')throw new Error('QA_CONFIG: se permite únicamente gimnasia_unificada_test');return {host:required('QA_DB_HOST'),port:required('QA_DB_PORT'),user:required('QA_DB_USER'),password:process.env.QA_DB_PASSWORD??'',database:name};}
function secrets(){return [...new Set(Object.entries(process.env).filter(([k,v])=>v&&/(?:PASSWORD|TOKEN|SECRET|API_KEY)$/i.test(k)).map(([,v])=>v))].sort((a,b)=>b.length-a.length);}
function redact(value){let s=String(value??'');for(const secret of secrets())s=s.split(secret).join('[REDACTED]');return s.replace(/\bsk-[A-Za-z0-9_-]{16,}/g,'[REDACTED]').replace(/((?:APP_KEY|DB_PASSWORD|api_key|password)["']?\s*[:=]\s*["']?)[^\s,"'<>]+/gi,'$1[REDACTED]').replace(/(Bearer\s+)[\w.~-]+/gi,'$1[REDACTED]').replace(/((?:cookie|set-cookie|authorization|x-csrf-token)\s*:\s*)[^\r\n]+/gi,'$1[REDACTED]');}
function scrub(value){if(Array.isArray(value))return value.map(scrub);if(value&&typeof value==='object')return Object.fromEntries(Object.entries(value).map(([k,v])=>[k,/(?:password|token|secret|api_?key)$|^(?:authorization|cookie|cookies|_token)$/i.test(k)?'[REDACTED]':scrub(v)]));return typeof value==='string'?redact(value):value;}
module.exports={root,required,appUrl,database,redact,scrub};
