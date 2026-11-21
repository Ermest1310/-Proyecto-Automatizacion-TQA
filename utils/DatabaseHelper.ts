import { execFileSync } from 'node:child_process';
const { database, required } = require('../config/runtime.cjs');
export class DatabaseHelper {
  static run(_sql:string):never {throw new Error('QA_CONFIG: escrituras SQL deshabilitadas; usar fixtures controladas');}
  static query(sql:string):string[]{
    if(!/^\s*(SELECT|SHOW|EXPLAIN)\b/i.test(sql)||/;|\b(?:INTO\s+(?:OUTFILE|DUMPFILE)|FOR\s+UPDATE)\b/i.test(sql))throw new Error('QA_CONFIG: consulta de solo lectura requerida');
    const db=database(),args=['--host='+db.host,'--port='+db.port,'--user='+db.user,'--database='+db.database,'--batch','--skip-column-names'];
    const call=(statement:string)=>execFileSync(required('QA_MYSQL_CLI'),[...args,'--execute',statement],{encoding:'utf8',env:{...process.env,MYSQL_PWD:db.password},timeout:15000,windowsHide:true});
    if(call('SELECT DATABASE()').trim()!==db.database)throw new Error('QA_CONFIG: conexión fuera de QA');
    const text=call(sql).trim();return text?text.split(/\r?\n/):[];
  }
}
