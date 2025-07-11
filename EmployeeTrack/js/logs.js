// Logs page script
class LogsManager {
  constructor() {
    this.allLogs = [];
    this.filteredLogs = [];
    this.init();
  }

  async init() {
    await this.loadLogs();
    this.setupEventListeners();
    this.setupFilters();
    this.renderLogs();
    this.updateStats();
  }

  async loadLogs() {
    try {
      const result = await chrome.storage.local.get(['logs']);
      this.allLogs = result.logs || [];
      this.filteredLogs = [...this.allLogs];
      
      // Sort by timestamp (newest first)
      this.filteredLogs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
    } catch (error) {
      console.error('Failed to load logs:', error);
      this.allLogs = [];
      this.filteredLogs = [];
    }
  }

  setupEventListeners() {
    // Refresh button
    document.getElementById('refreshBtn').addEventListener('click', async () => {
      await this.loadLogs();
      this.applyFilters();
      this.renderLogs();
      this.updateStats();
    });

    // Clear logs button
    document.getElementById('clearBtn').addEventListener('click', async () => {
      if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
        await chrome.storage.local.set({ logs: [] });
        this.allLogs = [];
        this.filteredLogs = [];
        this.renderLogs();
        this.updateStats();
      }
    });

    // Export button
    document.getElementById('exportBtn').addEventListener('click', () => {
      this.exportLogs();
    });

    // Filter event listeners
    document.getElementById('typeFilter').addEventListener('change', () => this.applyFilters());
    document.getElementById('fromDate').addEventListener('change', () => this.applyFilters());
    document.getElementById('toDate').addEventListener('change', () => this.applyFilters());
    document.getElementById('urlSearch').addEventListener('input', () => this.applyFilters());
  }

  setupFilters() {
    // Set default date range (last 7 days)
    const today = new Date();
    const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
    
    document.getElementById('toDate').value = today.toISOString().split('T')[0];
    document.getElementById('fromDate').value = weekAgo.toISOString().split('T')[0];
  }

  applyFilters() {
    const typeFilter = document.getElementById('typeFilter').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    const urlSearch = document.getElementById('urlSearch').value.toLowerCase();

    this.filteredLogs = this.allLogs.filter(log => {
      // Type filter
      if (typeFilter !== 'all' && log.type !== typeFilter) {
        return false;
      }

      // Date filter
      const logDate = new Date(log.timestamp).toISOString().split('T')[0];
      if (fromDate && logDate < fromDate) {
        return false;
      }
      if (toDate && logDate > toDate) {
        return false;
      }

      // URL search filter
      if (urlSearch && log.url && !log.url.toLowerCase().includes(urlSearch)) {
        return false;
      }

      return true;
    });

    // Sort by timestamp (newest first)
    this.filteredLogs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
    
    this.renderLogs();
    this.updateStats();
  }

  renderLogs() {
    const container = document.getElementById('logsContainer');
    
    if (this.filteredLogs.length === 0) {
      container.innerHTML = '<div class="no-logs">No logs found matching the current filters.</div>';
      return;
    }

    const logsHtml = this.filteredLogs.map(log => this.renderLogEntry(log)).join('');
    container.innerHTML = logsHtml;
  }

  renderLogEntry(log) {
    const timestamp = new Date(log.timestamp).toLocaleString();
    const typeClass = `type-${log.type}`;
    
    let contentHtml = '';
    if (log.type === 'keystroke' && log.content) {
      contentHtml = `<div class="log-content-text">${this.escapeHtml(log.content)}</div>`;
    } else if (log.type === 'website_visit' && log.title) {
      contentHtml = `<div><strong>Title:</strong> ${this.escapeHtml(log.title)}</div>`;
      if (log.domain) {
        contentHtml += `<div><strong>Domain:</strong> ${this.escapeHtml(log.domain)}</div>`;
      }
    } else if (log.type === 'activity' && log.activity) {
      contentHtml = `<div class="log-content-text">${this.escapeHtml(JSON.stringify(log.activity, null, 2))}</div>`;
    }

    return `
      <div class="log-entry">
        <div class="log-content">
          <span class="log-type ${typeClass}">${log.type.replace('_', ' ')}</span>
          ${contentHtml}
          ${log.url ? `<div class="log-url">${this.escapeHtml(log.url)}</div>` : ''}
        </div>
        <div class="log-timestamp">${timestamp}</div>
      </div>
    `;
  }

  updateStats() {
    const total = this.filteredLogs.length;
    const websiteVisits = this.filteredLogs.filter(log => log.type === 'website_visit').length;
    const keystrokes = this.filteredLogs.filter(log => log.type === 'keystroke').length;
    const activities = this.filteredLogs.filter(log => log.type === 'activity').length;

    document.getElementById('totalLogs').textContent = total;
    document.getElementById('websiteVisits').textContent = websiteVisits;
    document.getElementById('keystrokes').textContent = keystrokes;
    document.getElementById('activities').textContent = activities;
  }

  exportLogs() {
    const dataStr = JSON.stringify(this.filteredLogs, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = `employee-logs-${new Date().toISOString().split('T')[0]}.json`;
    link.click();
  }

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize logs manager when page loads
document.addEventListener('DOMContentLoaded', () => {
  new LogsManager();
});