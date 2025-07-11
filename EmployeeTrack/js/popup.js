// Popup script
document.addEventListener('DOMContentLoaded', async () => {
  const statusDiv = document.getElementById('status');
  const statusText = document.getElementById('statusText');
  const lastSyncSpan = document.getElementById('lastSync');
  
  // Load current status
  const data = await chrome.storage.local.get(['lastSync', 'isActive']);
  
  lastSyncSpan.textContent = data.lastSync ? new Date(data.lastSync).toLocaleString() : 'Never';
  
  if (data.isActive !== false) {
    statusDiv.className = 'status active';
    statusText.textContent = 'Active';
  } else {
    statusDiv.className = 'status inactive';
    statusText.textContent = 'Inactive';
  }
  
  // Configuration button
  document.getElementById('configBtn').addEventListener('click', () => {
    chrome.tabs.create({ url: chrome.runtime.getURL('html/config.html') });
  });
  
  // Logs button
  document.getElementById('logsBtn').addEventListener('click', () => {
    chrome.tabs.create({ url: chrome.runtime.getURL('html/logs.html') });
  });
});