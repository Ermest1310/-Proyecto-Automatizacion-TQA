import { test as base } from '@playwright/test';
import { TEST_CREDENTIALS } from '../config/test-data';

export interface QaUserFixture {
  email: string;
  password: string;
  role: string;
}

export const test = base.extend<{ qaUser: QaUserFixture }>({
  qaUser: async ({}, use) => {
    await use({
      email: TEST_CREDENTIALS.admin.email,
      password: TEST_CREDENTIALS.admin.password,
      role: 'admin',
    });
  },
});
