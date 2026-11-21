const test=require('node:test'),assert=require('node:assert/strict');
const {redactText,redactModel,policy}=require('../platform/redaction.cjs');
test('la política elimina secretos desconocidos, JWT, rutas y destinatarios',()=>{
 const sensitive=['someone@example.test','C:\\private\\user.json','https://private.test/account','eyJabcdefghijk.abcdefghijk.abcdefghijk'];
 const text=redactText(sensitive.join(' ')+' access_token=unknown-private-value telefono=77777777 documento=12345678');
 for(const item of [...sensitive,'unknown-private-value','77777777','12345678'])assert(!text.includes(item));
 assert.equal(redactText('x'.repeat(5000)).length,policy.limits.text);
});
test('la depuración estructurada no permite campos personales ni anidamiento ilimitado',()=>{
 const original={nested:{email:'somebody@example.test',Nombre:'Persona privada',password:'unknown',status:200}};
 const value=redactModel(original);assert.equal(value.nested.Nombre,'[DATO OMITIDO]');assert.equal(value.nested.status,200);assert.equal(original.nested.Nombre,'Persona privada');
 let deep={};for(let i=0;i<15;i++)deep={next:deep};assert.throws(()=>redactModel(deep),e=>e.statusCode===413);
 assert.throws(()=>redactModel(Array(21000).fill(1)),e=>e.statusCode===413);
});
