import base from './playwright.config';
import { defineConfig } from '@playwright/test';
export default defineConfig({ ...base, testDir:'./tests/validation', testMatch:'bug002-live.check.ts',
  projects:base.projects?.filter(p=>p.name==='chromium') });
