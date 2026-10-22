(function (window, $) {
  'use strict';

  if (!$) {
    return;
  }

  var alphaRows = [
    ['1','2','3','4','5','6','7','8','9','0'],
    ['Q','W','E','R','T','Y','U','I','O','P'],
    ['A','S','D','F','G','H','J','K','L'],
    ['Z','X','C','V','B','N','M']
  ];
  var numericRows = [
    ['1','2','3'],
    ['4','5','6'],
    ['7','8','9'],
    ['0','.','+']
  ];

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function buildRow(keys, klass) {
    return '<div class="kt-vk-row ' + klass + '">' + keys.map(function (key) {
      return '<button type="button" class="kt-vk-key" data-key="' + escapeHtml(key) + '">' + escapeHtml(key) + '</button>';
    }).join('') + '</div>';
  }

  function buildAlphaPad() {
    return ''
      + buildRow(alphaRows[0], 'alpha-10')
      + buildRow(alphaRows[1], 'alpha-10')
      + buildRow(alphaRows[2], 'alpha-9')
      + '<div class="kt-vk-row alpha-7">'
      + alphaRows[3].map(function (key) {
          return '<button type="button" class="kt-vk-key" data-key="' + escapeHtml(key) + '">' + escapeHtml(key) + '</button>';
        }).join('')
      + '</div>'
      + '<div class="kt-vk-row alpha-7">'
      + '<button type="button" class="kt-vk-key soft" data-key="@">@</button>'
      + '<button type="button" class="kt-vk-key soft" data-key=".">.</button>'
      + '<button type="button" class="kt-vk-key soft" data-key="-">-</button>'
      + '<button type="button" class="kt-vk-key soft" data-key="_">_</button>'
      + '<button type="button" class="kt-vk-key soft" data-key="/">/</button>'
      + '<button type="button" class="kt-vk-key soft" data-action="layout" data-layout="numeric">123</button>'
      + '<button type="button" class="kt-vk-key action" data-action="backspace">←</button>'
      + '</div>'
      + '<div class="kt-vk-row alpha-7">'
      + '<button type="button" class="kt-vk-key soft wide-2" data-action="clear">Clear</button>'
      + '<button type="button" class="kt-vk-key space" data-key=" ">Space</button>'
      + '<button type="button" class="kt-vk-key action wide-2" data-action="done">Done</button>'
      + '</div>';
  }

  function buildNumericPad() {
    return ''
      + buildRow(numericRows[0], 'numeric-3')
      + buildRow(numericRows[1], 'numeric-3')
      + buildRow(numericRows[2], 'numeric-3')
      + buildRow(numericRows[3], 'numeric-3')
      + '<div class="kt-vk-row numeric-3">'
      + '<button type="button" class="kt-vk-key soft" data-action="layout" data-layout="alpha">ABC</button>'
      + '<button type="button" class="kt-vk-key soft" data-action="clear">Clear</button>'
      + '<button type="button" class="kt-vk-key action" data-action="backspace">←</button>'
      + '</div>'
      + '<div class="kt-vk-row numeric-3">'
      + '<button type="button" class="kt-vk-key action wide-3" data-action="done">Done</button>'
      + '</div>';
  }

  function renderKeyboard(layout, title) {
    var body = layout === 'numeric' ? buildNumericPad() : buildAlphaPad();
    return ''
      + '<div class="kt-vk ' + (layout === 'numeric' ? 'kt-vk-numeric' : 'kt-vk-alpha') + '" data-layout-active="' + layout + '" hidden>'
      + '<div class="kt-vk-head">'
      + '<div class="kt-vk-title">' + escapeHtml(title || 'Keyboard') + '</div>'
      + '<div class="kt-vk-meta">' + (layout === 'numeric' ? 'Numeric' : 'QWERTY') + '</div>'
      + '</div>'
      + '<div class="kt-vk-preview">'
      + '<div class="kt-vk-preview-label">Active Field</div>'
      + '<div class="kt-vk-preview-value"></div>'
      + '</div>'
      + '<div class="kt-vk-body">' + body + '</div>'
      + '</div>';
  }

  function setCaretValue(input, chunk) {
    var value = input.value || '';
    var supportsSelection = typeof input.selectionStart === 'number' && typeof input.selectionEnd === 'number';
    var start = supportsSelection ? input.selectionStart : value.length;
    var end = supportsSelection ? input.selectionEnd : value.length;
    input.value = value.slice(0, start) + chunk + value.slice(end);
    var nextPos = start + chunk.length;
    if (typeof input.setSelectionRange === 'function' && supportsSelection) {
      input.setSelectionRange(nextPos, nextPos);
    }
    $(input).trigger('input').trigger('change');
  }

  function backspaceValue(input) {
    var value = input.value || '';
    var supportsSelection = typeof input.selectionStart === 'number' && typeof input.selectionEnd === 'number';
    var start = supportsSelection ? input.selectionStart : value.length;
    var end = supportsSelection ? input.selectionEnd : value.length;
    if (start !== end) {
      input.value = value.slice(0, start) + value.slice(end);
      if (typeof input.setSelectionRange === 'function' && supportsSelection) {
        input.setSelectionRange(start, start);
      }
    } else if (start > 0) {
      input.value = value.slice(0, start - 1) + value.slice(end);
      if (typeof input.setSelectionRange === 'function' && supportsSelection) {
        input.setSelectionRange(start - 1, start - 1);
      }
    }
    $(input).trigger('input').trigger('change');
  }

  function getFieldLayout($field) {
    return ($field.data('vkLayout') || $field.attr('data-vk-layout') || 'alpha').toString();
  }

  function findScrollParent(element) {
    var current = element ? element.parentElement : null;
    while (current) {
      var style = window.getComputedStyle(current);
      var overflowY = style.overflowY;
      if ((overflowY === 'auto' || overflowY === 'scroll') && current.scrollHeight > current.clientHeight) {
        return current;
      }
      current = current.parentElement;
    }
    return null;
  }

  window.KTouchKeyboard = {
    init: function (scope, options) {
      var $scope = $(scope);
      if (!$scope.length) {
        return;
      }

      var settings = $.extend({
        inputSelector: '[data-vk-layout]',
        hostSelector: '[data-vk-host]',
        title: 'On Screen Keyboard'
      }, options || {});

      var $host = $scope.find(settings.hostSelector).first();
      if (!$host.length) {
        return;
      }

      $host.css({
        position: 'fixed',
        left: '-9999px',
        top: '-9999px',
        width: '1px',
        height: '1px',
        overflow: 'visible',
        zIndex: 2500
      });
      $host.html(renderKeyboard('alpha', settings.title));
      var $keyboard = $host.find('.kt-vk');
      var $meta = $keyboard.find('.kt-vk-meta');
      var $previewLabel = $keyboard.find('.kt-vk-preview-label');
      var $previewValue = $keyboard.find('.kt-vk-preview-value');
      var activeInput = null;
      var $padTarget = $scope.closest('.modal-body');
      if (!$padTarget.length) {
        $padTarget = $scope;
      }

      function getInputLabel(input) {
        if (!input) {
          return 'Active Field';
        }
        var $input = $(input);
        var aria = $input.attr('aria-label');
        if (aria) {
          return aria;
        }
        var id = $input.attr('id');
        if (id) {
          var $label = $('label[for="' + id + '"]').first();
          if ($label.length) {
            return $.trim($label.text()) || 'Active Field';
          }
        }
        var placeholder = $input.attr('placeholder');
        if (placeholder) {
          return placeholder;
        }
        return 'Active Field';
      }

      function syncPreview(input) {
        if (!input) {
          return;
        }
        $previewLabel.text(getInputLabel(input));
        $previewValue.text(input.value || '');
      }

      function keepInputVisible(input) {
        if (!input) {
          return;
        }
        var keyboardHeight = $keyboard.outerHeight() || 0;
        var keyboardTop = window.innerHeight - keyboardHeight - 14;
        var inputRect = input.getBoundingClientRect();
        var overlap = inputRect.bottom - keyboardTop + 14;

        if (overlap > 0) {
          var scrollParent = findScrollParent(input);
          if (scrollParent) {
            scrollParent.scrollTop += overlap;
          } else {
            window.scrollBy({ top: overlap, behavior: 'smooth' });
          }
        }
      }

      function showFor($field) {
        if (!$field.length) {
          return;
        }
        activeInput = $field.get(0);
        var layout = getFieldLayout($field);
        $host.html(renderKeyboard(layout, settings.title));
        $keyboard = $host.find('.kt-vk');
        $meta = $keyboard.find('.kt-vk-meta');
        $previewLabel = $keyboard.find('.kt-vk-preview-label');
        $previewValue = $keyboard.find('.kt-vk-preview-value');
        $keyboard.prop('hidden', false);
        $meta.text(layout === 'numeric' ? 'Numeric' : 'QWERTY');
        syncPreview(activeInput);
        $padTarget.css('padding-bottom', (($keyboard.outerHeight() || 0) + 26) + 'px');
        setTimeout(function () {
          keepInputVisible(activeInput);
        }, 60);
      }

      function hideKeyboard() {
        $keyboard.prop('hidden', true);
        $padTarget.css('padding-bottom', '');
      }

      $scope.off('focus.ktVk click.ktVk', settings.inputSelector)
        .on('focus.ktVk click.ktVk', settings.inputSelector, function () {
          showFor($(this));
        })
        .on('input.ktVk keyup.ktVk change.ktVk', settings.inputSelector, function () {
          if (activeInput === this) {
            syncPreview(activeInput);
          }
        });

      $host.off('click.ktVk').on('click.ktVk', '.kt-vk-key', function () {
        if (!activeInput) {
          return;
        }

        var $btn = $(this);
        var action = $btn.data('action');
        var layout = $btn.data('layout');

        activeInput.focus();

        if (action === 'backspace') {
          backspaceValue(activeInput);
          syncPreview(activeInput);
          return;
        }
        if (action === 'clear') {
          activeInput.value = '';
          $(activeInput).trigger('input').trigger('change');
          syncPreview(activeInput);
          return;
        }
        if (action === 'done') {
          hideKeyboard();
          activeInput.blur();
          return;
        }
        if (action === 'layout' && layout) {
          $(activeInput).attr('data-vk-layout', layout);
          showFor($(activeInput));
          return;
        }

        var key = $btn.data('key');
        if (typeof key !== 'undefined') {
          setCaretValue(activeInput, String(key));
          syncPreview(activeInput);
          keepInputVisible(activeInput);
        }
      });

      return {
        showFor: showFor,
        hide: hideKeyboard
      };
    }
  };
})(window, window.jQuery);
