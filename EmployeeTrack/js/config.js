document.addEventListener('DOMContentLoaded', async () => {
  const form = document.getElementById('configForm');
  const serverUrlInput = document.getElementById('serverUrl');
  const authTokenInput = document.getElementById('authToken');
  const consentCheckbox = document.getElementById('consent');
  
  // Load existing configuration
  const config = await chrome.storage.local.get([
    'serverUrl', 'authToken', 'consent'
  ]);
  
  serverUrlInput.value = config.serverUrl || '';
  authTokenInput.value = config.authToken || '';
  consentCheckbox.checked = config.consent || false;
  
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const newConfig = {
      serverUrl: serverUrlInput.value,
      authToken: authTokenInput.value,
      consent: consentCheckbox.checked,
      isActive: consentCheckbox.checked,
      configuredAt: new Date().toISOString()
    };
    
    await chrome.storage.local.set(newConfig);
    alert('Configuration saved successfully!');
  });
});