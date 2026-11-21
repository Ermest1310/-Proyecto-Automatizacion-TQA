const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto');
function equal(a,b){if(typeof a!=='string'||typeof b!=='string')return false;const x=Buffer.from(a),y=Buffer.from(b);return x.length===y.length&&crypto.timingSafeEqual(x,y);}
async function jsonBody(req){if(!req.headers['content-type']?.startsWith('application/json'))throw Object.assign(new Error('Content-Type application/json requerido'),{statusCode:415});let body='';for await(const b of req){body+=b;if(Buffer.byteLength(body)>16384)throw Object.assign(new Error('Solicitud demasiado grande'),{statusCode:413});}try{return JSON.parse(body);}catch{throw Object.assign(new Error('JSON inválido'),{statusCode:400});}}
function web(root,token){const sessions=new Map(),attempts=new Map();const files={'/':['index.html','text/html; charset=utf-8'],'/assets/app.css':['app.css','text/css; charset=utf-8'],'/assets/app.js':['app.js','text/javascript; charset=utf-8'],'/assets/platform.js':['platform.js','text/javascript; charset=utf-8'],'/assets/platform-catalog.js':['platform-catalog.js','text/javascript; charset=utf-8'],'/assets/platform-data.js':['platform-data.js','text/javascript; charset=utf-8'],'/assets/platform-ops.js':['platform-ops.js','text/javascript; charset=utf-8'],'/assets/platform.css':['platform.css','text/css; charset=utf-8']};
 return async(req,res,send)=>{const u=new URL(req.url,'http://127.0.0.1'),pathname=u.pathname;
  if(req.method==='GET'&&files[pathname]){const [file,type]=files[pathname];res.setHeader('Content-Type',type);res.setHeader('Content-Security-Policy',"default-src 'self'; script-src 'self'; style-src 'self'; connect-src 'self'; img-src 'self' data:; object-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'");res.end(fs.readFileSync(path.join(root,'web',file)));return {handled:true};}
  if(req.headers.origin&&req.headers.origin!==`http://${req.headers.host}`){send(403,{error:'Origen no permitido'});return {handled:true};}
  const cookie=(req.headers.cookie||'').split(';').map(s=>s.trim()).find(s=>s.startsWith('qa_session='))?.slice(11);let session=cookie&&sessions.get(cookie);if(session&&session.expires<Date.now()){sessions.delete(cookie);session=null;}
  if(pathname==='/api/session'&&req.method==='POST'){
   const input=await jsonBody(req);const key=req.socket.remoteAddress;const now=Date.now();let rate=attempts.get(key);if(!rate||rate.until<now)rate={count:0,until:now+300000};if(rate.count>=10){send(429,{error:'Demasiados intentos de acceso; espere cinco minutos'});return {handled:true};}
   if(!input||typeof input!=='object'||Object.keys(input).some(k=>k!=='token')||!equal(input.token,token)){rate.count++;attempts.set(key,rate);send(401,{error:'Token local no válido'});return {handled:true};}
   if(sessions.size>=1000){for(const [id,s] of sessions)if(s.expires<now)sessions.delete(id);if(sessions.size>=1000){send(503,{error:'Límite de sesiones alcanzado'});return {handled:true};}}
   const id=crypto.randomBytes(32).toString('hex'),csrf=crypto.randomBytes(24).toString('hex');sessions.set(id,{csrf,expires:now+21600000});attempts.delete(key);res.setHeader('Set-Cookie',`qa_session=${id}; HttpOnly; SameSite=Strict; Path=/; Max-Age=21600`);send(200,{authenticated:true,csrf});return {handled:true};
  }
  const bearer=equal((req.headers.authorization||'').replace(/^Bearer /,''),token);const authenticated=bearer||Boolean(session);
  if(!authenticated){send(401,{error:'No autorizado'});return {handled:true};}
  if(!bearer&&['POST','PUT','PATCH','DELETE'].includes(req.method)&&!equal(req.headers['x-qa-csrf'],session.csrf)){send(403,{error:'CSRF no válido; vuelva a iniciar sesión'});return {handled:true};}
  if(pathname==='/api/session'&&req.method==='GET'){send(200,{authenticated:true,csrf:session?.csrf||null});return {handled:true};}
  if(pathname==='/api/session/logout'&&req.method==='POST'){if(cookie)sessions.delete(cookie);res.setHeader('Set-Cookie','qa_session=; HttpOnly; SameSite=Strict; Path=/; Max-Age=0');send(200,{authenticated:false});return {handled:true};}
  return {handled:false,cookieAuth:!bearer};
 };
}
module.exports={web,jsonBody,equal};
