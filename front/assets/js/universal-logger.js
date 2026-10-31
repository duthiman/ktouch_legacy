(function (window) {
  'use strict';

  function clean(value) {
    if (!value || typeof value !== 'object') return value;
    var out = Array.isArray(value) ? [] : {};
    Object.keys(value).forEach(function (key) {
      out[key] = /password|passcode|pin|authorization|token|secret|jwt/i.test(key)
        ? '[REDACTED]' : clean(value[key]);
    });
    return out;
  }

  function write(level, message, details) {
    var entry = { timestamp: new Date().toISOString(), level: level, message: message, details: clean(details || {}) };
    var method = level === 'error' ? 'error' : level === 'warning' ? 'warn' : 'log';
    if (window.console && console[method]) console[method]('[KTouch]', entry);
    window.dispatchEvent(new CustomEvent('ktouch:log', { detail: entry }));
    return entry;
  }

  window.UniversalLogger = {
    success: function (message, details) { return write('success', message, details); },
    warning: function (message, details) { return write('warning', message, details); },
    error: function (message, details) { return write('error', message, details); },
    api: function (response, details) {
      var data = details || {};
      data.http_status = response && (response.http_status || response.status);
      if (response && response.success === false) return write('error', response.message || 'API request failed', data);
      return write('success', response && response.message || 'API request completed', data);
    }
  };
})(window);
