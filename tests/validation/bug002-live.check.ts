import { test, expect } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import {syntheticFixtures,mysqlFixtureAction} from '../../utils/SyntheticFixtures';
import { RegistrationPage } from '../../pages/RegistrationPage';

test('QA-REG-002-LIVE Registro real normal y con & persiste en MySQL de pruebas', async ({ page }, info) => {
  test.setTimeout(120000);
  if (process.env.APP_URL!=='http://127.0.0.1:8187' || !process.env.QA_LIVE_STORAGE)
    throw new Error('BLOCKED: solo ejecutar contra la instancia QA aislada documentada');
  const cases=syntheticFixtures().registration;
  mysqlFixtureAction('claim');let failed=false;
  try {
  const registration=new RegistrationPage(page);
  const outcomes=[];
  for (const item of cases.slice(0,2)) {
    await registration.open();
    await registration.fill(item.surname,item.email,item.document);
    expect(await registration.isSurnameValid()).toBe(true);
    const result=await registration.submit();
    expect(result.status()).toBe(302);
    await expect(page).toHaveURL(/\/login$/);
    outcomes.push({ email:item.email, surname:item.surname, status:result.status() });
    await info.attach('Registro-'+(outcomes.length),{ body:await page.screenshot(),contentType:'image/png' });
  }
  await registration.open();
  await registration.fill(cases[2].surname,cases[2].email,cases[2].document);
  expect(await registration.isSurnameValid()).toBe(false);
  const token=await registration.csrfToken();
  const denied=await page.request.post('/register',{ headers:{Accept:'application/json','X-CSRF-TOKEN':token},
    data:{Nombre:'Pedro',Apellido:cases[2].surname,Email:cases[2].email,CedulaIdentidad:cases[2].document,
      Nacionalidad:'Bolivia',FechaNacimiento:'2010-05-05',Genero:'M',Categoria:'Junior',Peso:60,Altura:1.62,Nivel:'Principiante'} });
  expect(denied.status()).toBe(422);
  expect((await denied.json()).errors.Apellido).toBeDefined();
  const input=info.outputPath('inputs.json'); fs.writeFileSync(input,JSON.stringify(cases));
  const probe=spawnSync('php',[process.env.QA_DB_VERIFY_SCRIPT!,input],{encoding:'utf8',env:process.env,timeout:20000});
  expect(probe.status,probe.stdout+'\n'+probe.stderr).toBe(0);
  const db=JSON.parse(probe.stdout);
  expect(db.database).toBe('gimnasia_unificada_test');
  expect(db.rows).toHaveLength(2);
  for(const item of cases.slice(0,2))expect(db.rows).toContainEqual({Email:item.email,Nombre:'Pedro',Apellido:item.surname,estado:'pendiente'});
  expect(db.rows.some((r:{Email:string})=>r.Email===cases[2].email)).toBe(false);
  fs.writeFileSync(info.outputPath('live-result.json'),JSON.stringify({outcomes,negativeStatus:denied.status(),...db},null,2));
  await info.attach('MySQL-persistencia',{path:info.outputPath('live-result.json'),contentType:'application/json'});
  }catch(error){failed=true;throw error;}finally{try{const capture=mysqlFixtureAction('capture'),cleanup=mysqlFixtureAction('cleanup');await info.attach('fixture-cleanup',{body:JSON.stringify({capture,cleanup}),contentType:'application/json'});}catch(error){await info.attach('fixture-cleanup-pending',{body:JSON.stringify({blocked:true,reason:'Owned resources retained; cleanup not confirmed'}),contentType:'application/json'});if(!failed)throw error;}}

});
