import { APIRequestContext, APIResponse } from '@playwright/test';

export class ApiClient {
  constructor(private readonly request: APIRequestContext) {}

  async get(path: string, options: Record<string, unknown> = {}): Promise<APIResponse> {
    return await this.request.get(path, options);
  }

  async post(path: string, data?: unknown, options: Record<string, unknown> = {}): Promise<APIResponse> {
    return await this.request.post(path, { data, ...options });
  }

  async put(path: string, data?: unknown, options: Record<string, unknown> = {}): Promise<APIResponse> {
    return await this.request.put(path, { data, ...options });
  }

  async delete(path: string, options: Record<string, unknown> = {}): Promise<APIResponse> {
    return await this.request.delete(path, options);
  }
}
