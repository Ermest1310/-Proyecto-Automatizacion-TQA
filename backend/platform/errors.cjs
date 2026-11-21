const {fs,path,crypto,within}=require('./common.cjs'),{redactText}=require('./redaction.cjs');
function response(root,error){
 const status=Number.isInteger(error.statusCode)&&error.statusCode>=400&&error.statusCode<=599?error.statusCode:500;
 if(status<500)return {status,body:{error:redactText(error.message)}};
 const reference=crypto.randomBytes(8).toString('hex'),diagnostic={at:new Date().toISOString(),reference,message:redactText(error.message),stack:redactText(error.stack)};
 try{const dir=path.join(root,'reports/platform');fs.mkdirSync(dir,{recursive:true});within(root,'reports/platform');fs.appendFileSync(path.join(dir,'system-errors.ndjson'),JSON.stringify(diagnostic)+'\n');}catch{/* La salida del backend conserva el diagnóstico si el archivo no se puede escribir. */}
 try{console.error('QA_PLATFORM_ERROR '+JSON.stringify(diagnostic));}catch{/* La respuesta no expone el detalle interno. */}
 return {status,body:{error:'Error interno de plataforma. Consulte el registro local con referencia '+reference,reference}};
}
module.exports={response};