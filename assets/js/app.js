// Car Rental System JavaScript

// Global utility functions
function showAlert(message, type = 'info') {
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    const container = document.querySelector('.container');
    container.insertBefore(alert, container.firstChild);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// API helper functions
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || `HTTP error! status: ${response.status}`);
        }
        
        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

// Form validation
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    const errors = [];
    
    Object.keys(rules).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        const rule = rules[fieldName];
        
        if (!field) return;
        
        const value = field.value.trim();
        
        if (rule.required && !value) {
            errors.push(`${rule.label || fieldName} is required`);
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
        
        if (value && rule.type === 'email' && !isValidEmail(value)) {
            errors.push(`${rule.label || fieldName} must be a valid email`);
            field.classList.add('error');
        }
        
        if (value && rule.type === 'number' && isNaN(value)) {
            errors.push(`${rule.label || fieldName} must be a number`);
            field.classList.add('error');
        }
        
        if (value && rule.min && parseFloat(value) < rule.min) {
            errors.push(`${rule.label || fieldName} must be at least ${rule.min}`);
            field.classList.add('error');
        }
        
        if (value && rule.max && parseFloat(value) > rule.max) {
            errors.push(`${rule.label || fieldName} must be at most ${rule.max}`);
            field.classList.add('error');
        }
    });
    
    return errors;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Modal management
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Table utilities
function createTableRow(data, columns) {
    const row = document.createElement('tr');
    
    columns.forEach(column => {
        const cell = document.createElement('td');
        
        if (typeof column === 'string') {
            cell.textContent = data[column] || '';
        } else if (typeof column === 'object') {
            if (column.render) {
                cell.innerHTML = column.render(data);
            } else {
                cell.textContent = data[column.key] || '';
            }
        }
        
        row.appendChild(cell);
    });
    
    return row;
}

function updateTable(tableBodyId, data, columns) {
    const tbody = document.getElementById(tableBodyId);
    
    if (!tbody) return;
    
    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${columns.length}">No data found</td></tr>`;
        return;
    }
    
    tbody.innerHTML = '';
    data.forEach(item => {
        const row = createTableRow(item, columns);
        tbody.appendChild(row);
    });
}

// Search and filter utilities
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function filterData(data, searchTerm, searchFields) {
    if (!searchTerm) return data;
    
    const term = searchTerm.toLowerCase();
    return data.filter(item => {
        return searchFields.some(field => {
            const value = item[field];
            return value && value.toString().toLowerCase().includes(term);
        });
    });
}

// Date utilities
function getTodayDate() {
    return new Date().toISOString().split('T')[0];
}

function addDays(date, days) {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result.toISOString().split('T')[0];
}

function calculateDaysDifference(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

// Local storage utilities
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
    } catch (error) {
        console.error('Error saving to localStorage:', error);
    }
}

function loadFromLocalStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
        console.error('Error loading from localStorage:', error);
        return defaultValue;
    }
}

// Form auto-save
function enableAutoSave(formId, storageKey, interval = 5000) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const autoSave = () => {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        saveToLocalStorage(storageKey, data);
    };
    
    form.addEventListener('input', debounce(autoSave, 1000));
    
    setInterval(autoSave, interval);
    
    const savedData = loadFromLocalStorage(storageKey);
    if (savedData) {
        Object.keys(savedData).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = savedData[key];
            }
        });
    }
}

// Loading states
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="loading">Loading...</div>';
    }
}

function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const loading = element.querySelector('.loading');
        if (loading) {
            loading.remove();
        }
    }
}

// Print functionality
function printPage() {
    window.print();
}

function exportToCSV(data, filename) {
    if (!data || data.length === 0) {
        showAlert('No data to export', 'error');
        return;
    }
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => {
            const value = row[header] || '';
            return `"${value.toString().replace(/"/g, '""')}"`;
        }).join(','))
    ].join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Event delegation for dynamic content
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('close-modal')) {
        const modal = e.target.closest('.modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }
});

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    console.error('Error details:', e.filename, e.lineno, e.colno);
    
    // Don't show alert for certain minor errors
    if (e.error && e.error.message && e.error.message.includes('Non-Error promise rejection')) {
        return;
    }
    
    showAlert('An unexpected error occurred. Check the console for details.', 'error');
});

// Unhandled promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    showAlert('Network or API error occurred. Please try again.', 'error');
});

// Initialize tooltips and other UI enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Auto-close alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.remove();
        }, 5000);
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal[style*="block"]');
            openModals.forEach(modal => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        }
    });
    
    // Add focus management for modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown', function() {
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });
});

// Add smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Performance monitoring
function measurePerformance(name, fn) {
    const start = performance.now();
    const result = fn();
    const end = performance.now();
    console.log(`${name} took ${end - start} milliseconds`);
    return result;
}

// Retry mechanism for failed requests
async function retryRequest(requestFn, maxRetries = 3, delay = 1000) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            return await requestFn();
        } catch (error) {
            if (i === maxRetries - 1) throw error;
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
}

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showAlert,
        formatCurrency,
        formatDate,
        apiRequest,
        validateForm,
        isValidEmail,
        openModal,
        closeModal,
        createTableRow,
        updateTable,
        debounce,
        filterData,
        getTodayDate,
        addDays,
        calculateDaysDifference,
        saveToLocalStorage,
        loadFromLocalStorage,
        enableAutoSave,
        showLoading,
        hideLoading,
        printPage,
        exportToCSV,
        measurePerformance,
        retryRequest
    };
}