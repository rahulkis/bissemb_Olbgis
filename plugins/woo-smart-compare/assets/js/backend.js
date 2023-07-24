'use strict';

(function($) {
  $(function() {
    woosc_button_action();

    $('.woosc_color_picker').wpColorPicker();

    $('.woosc-fields').sortable({
      handle: '.label',
    });

    $('.woosc-attributes').sortable({
      handle: '.label',
    });
  });

  $(document).on('change', 'select[name="woosc_button_action"]', function() {
    woosc_button_action();
  });

  function woosc_button_action() {
    var action = $('select[name="woosc_button_action"]').val();

    $('.woosc_button_action_hide').hide();
    $('.woosc_button_action_' + action).show();
  }
})(jQuery);