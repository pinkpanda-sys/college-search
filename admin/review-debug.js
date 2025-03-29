// Create a debug overlay
const createDebugOverlay = () => {
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.bottom = '0';
    overlay.style.right = '0';
    overlay.style.backgroundColor = 'rgba(0,0,0,0.7)';
    overlay.style.color = 'white';
    overlay.style.padding = '10px';
    overlay.style.fontFamily = 'monospace';
    overlay.style.fontSize = '12px';
    overlay.style.maxHeight = '200px';
    overlay.style.width = '400px';
    overlay.style.overflow = 'auto';
    overlay.style.zIndex = '9999';
    overlay.style.border = '1px solid #444';
    overlay.style.borderRadius = '4px';
    
    const header = document.createElement('div');
    header.textContent = 'Review Management Debug';
    header.style.fontWeight = 'bold';
    header.style.marginBottom = '5px';
    header.style.paddingBottom = '5px';
    header.style.borderBottom = '1px solid #555';
    
    const content = document.createElement('div');
    content.id = 'debugContent';
    
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Close';
    closeBtn.style.position = 'absolute';
    closeBtn.style.top = '5px';
    closeBtn.style.right = '5px';
    closeBtn.style.padding = '2px 5px';
    closeBtn.style.backgroundColor = '#555';
    closeBtn.style.border = 'none';
    closeBtn.style.color = 'white';
    closeBtn.style.borderRadius = '2px';
    closeBtn.style.cursor = 'pointer';
    closeBtn.onclick = () => document.body.removeChild(overlay);
    
    overlay.appendChild(header);
    overlay.appendChild(content);
    overlay.appendChild(closeBtn);
    
    document.body.appendChild(overlay);
    
    return content;
};

// Log to both console and debug overlay
const debugLog = (message, data = null) => {
    console.log(message, data || '');
    
    let overlay = document.getElementById('debugContent');
    if (!overlay) {
        overlay = createDebugOverlay();
    }
    
    const line = document.createElement('div');
    line.style.marginBottom = '3px';
    line.style.borderBottom = '1px dotted #555';
    line.style.paddingBottom = '3px';
    
    const timestamp = new Date().toLocaleTimeString();
    line.innerHTML = `<span style="color:#aaa">[${timestamp}]</span> ${message}`;
    
    if (data) {
        try {
            const dataStr = typeof data === 'object' ? JSON.stringify(data, null, 2) : data.toString();
            const dataDetail = document.createElement('pre');
            dataDetail.style.marginTop = '2px';
            dataDetail.style.marginLeft = '10px';
            dataDetail.style.padding = '3px';
            dataDetail.style.backgroundColor = 'rgba(0,0,0,0.3)';
            dataDetail.style.maxHeight = '60px';
            dataDetail.style.overflow = 'auto';
            dataDetail.style.fontSize = '10px';
            dataDetail.textContent = dataStr;
            line.appendChild(dataDetail);
        } catch (e) {
            console.error('Could not stringify debug data', e);
        }
    }
    
    overlay.prepend(line);
    
    // Limit entries to 50
    if (overlay.children.length > 50) {
        overlay.removeChild(overlay.lastChild);
    }
};

// Check DOM elements
document.addEventListener('DOMContentLoaded', function() {
    debugLog('Debug script loaded');
    
    // Check important DOM elements
    const elementsToCheck = [
        'reviewsList', 
        'loadingIndicator', 
        'noDataMessage', 
        'searchInput', 
        'statusFilter', 
        'sourceFilter',
        'reviewModal',
        'reviewModalBody'
    ];
    
    elementsToCheck.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            debugLog(`✅ Element found: #${id}`);
        } else {
            debugLog(`❌ Element missing: #${id}`);
        }
    });
    
    // API Check
    debugLog('Testing API connection...');
    fetch('../api/reviews.php')
        .then(response => {
            debugLog(`API Response Status: ${response.status} ${response.statusText}`);
            return response.text();
        })
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
                debugLog(`API returned ${Array.isArray(data) ? data.length : 1} items`, data);
            } catch (e) {
                debugLog('API response is not valid JSON', text.substring(0, 100) + '...');
            }
        })
        .catch(error => {
            debugLog(`API Error: ${error.message}`);
        });
});

// Override fetch to debug
const originalFetch = window.fetch;
window.fetch = function() {
    const url = arguments[0];
    debugLog(`📤 Fetch request to: ${url}`);
    
    return originalFetch.apply(this, arguments)
        .then(response => {
            debugLog(`📥 Fetch response from: ${url} (${response.status})`);
            return response;
        })
        .catch(error => {
            debugLog(`❌ Fetch error for: ${url}`, error);
            throw error;
        });
}; 