const { required } = require('./runtime.cjs');
export type UserRole = 'admin' | 'judge' | 'athlete' | 'guest';
export interface TestUser { id:number; name:string; email:string; password:string; role:UserRole; }
function account(role:UserRole):TestUser { const p='QA_'+role.toUpperCase(); return {role,name:'QA '+role,
get id(){const n=Number(required(p+'_ID'));if(!Number.isInteger(n)||n<1)throw new Error('QA_CONFIG: ID inválido '+p);return n;},
get email(){return required(p+'_EMAIL');},get password(){return required(p+'_PASSWORD');}};}
export const TEST_USERS:Record<string,TestUser>={qa_admin:account('admin'),qa_judge:account('judge'),qa_athlete:account('athlete'),qa_guest:account('guest')};
export const TEST_CREDENTIALS={admin:TEST_USERS.qa_admin,judge:TEST_USERS.qa_judge,athlete:TEST_USERS.qa_athlete,guest:TEST_USERS.qa_guest};
