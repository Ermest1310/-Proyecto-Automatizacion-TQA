import {test as base} from '@playwright/test';
/** No generic SQL cleanup. Live registration uses the signed ownership adapter. */
export const test=base.extend<{cleanDatabase:()=>Promise<void>}>({
 cleanDatabase:async(_fixtures,use)=>{await use(async()=>{throw new Error('QA_CONFIG: limpieza SQL genérica deshabilitada; usar registro de propiedad por execution_id');});},
});
