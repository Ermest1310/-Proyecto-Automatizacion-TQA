const { appUrl } = require('./runtime.cjs');
export interface EnvironmentConfig {
  name: string;
  baseURL: string;
  apiBaseUrl?: string;
}

export const environments: Record<string, EnvironmentConfig> = {
  local: {
    name: 'local',
    baseURL: appUrl(),
    apiBaseUrl: appUrl(),
  },
};

export const currentEnvironment = (process.env.QA_ENV as string) || 'local';

export const config = {
  environment: currentEnvironment,
  baseURL: environments[currentEnvironment]?.baseURL ?? appUrl(),
  apiBaseUrl: environments[currentEnvironment]?.apiBaseUrl ?? appUrl(),
};

export const APP_URL = config.baseURL;
export const API_BASE_URL = config.apiBaseUrl ?? config.baseURL;
