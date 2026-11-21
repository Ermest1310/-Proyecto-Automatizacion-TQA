import { Page } from '@playwright/test';
import fs from 'fs';
import path from 'path';

const EVIDENCE_DIR = path.resolve(__dirname, '..', 'reports', 'evidence');

/**
 * Manager de evidencias: capturas de pantalla y archivos de log
 * para adjuntar a los reportes de QA.
 */
export class EvidenceManager {
  static async saveScreenshot(page: Page, name: string): Promise<string> {
    fs.mkdirSync(EVIDENCE_DIR, { recursive: true });
    const file = path.join(EVIDENCE_DIR, `${name}.png`);
    await page.screenshot({ path: file, fullPage: true });
    return file;
  }

  static save(name: string, content: string): string {
    fs.mkdirSync(EVIDENCE_DIR, { recursive: true });
    const file = path.join(EVIDENCE_DIR, `${name}.txt`);
    fs.writeFileSync(file, content);
    return file;
  }
}
