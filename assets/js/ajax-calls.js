// FitLife – Global AJAX helpers (jQuery required)

// Generic POST helper
function ajaxPost(url, data, callback) {
  $.ajax({ url, type: 'POST', data, dataType: 'json', success: callback,
    error: () => console.error('AJAX error:', url) });
}

// Generic GET helper
function ajaxGet(url, data, callback) {
  $.ajax({ url, type: 'GET', data, dataType: 'json', success: callback,
    error: () => console.error('AJAX error:', url) });
}

// Show temporary feedback message
function showFeedback(selector, msg, type = 'success') {
  const colors = { success: '#059669', error: '#dc2626', info: '#2563eb' };
  $(selector).html(`<span style="color:${colors[type]};font-size:13px;font-weight:600">${msg}</span>`);
  setTimeout(() => $(selector).html(''), 3000);
}
