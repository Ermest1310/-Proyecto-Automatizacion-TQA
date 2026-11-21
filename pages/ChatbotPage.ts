import { Page, Locator } from '@playwright/test';
import { expect } from '@playwright/test';

export class ChatbotPage {
  readonly page: Page;
  readonly chatInput: Locator;
  readonly sendButton: Locator;
  readonly messagesContainer: Locator;

  constructor(page: Page) {
    this.page = page;
    // Selectores reales tomados de resources/views/areamedica/chatbot.blade.php
    this.chatInput = page.locator('#user-input');
    this.sendButton = page.locator('#send-button');
    this.messagesContainer = page.locator('#chat-messages');
  }

  async sendMessage(message: string) {
    await this.chatInput.fill(message);
    await this.sendButton.click();
  }

  get lastBotReply() { return this.messagesContainer.locator('.message.bot').last(); }

  async expectBotReply(text: string) {
    await expect(this.lastBotReply).toContainText(text);
  }
}
