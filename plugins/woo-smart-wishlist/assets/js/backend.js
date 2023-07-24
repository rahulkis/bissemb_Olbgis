'use strict';

(function($) {
  $(function() {
    woosw_button_action();

    if ($('.woosw_color_picker').length > 0) {
      $('.woosw_color_picker').wpColorPicker();
    }
  });

  $(document).on('click touch', '.woosw_action', function(e) {
    var pid = $(this).attr('data-pid');
    var key = $(this).attr('data-key');

    if ($('#woosw_popup').length < 1) {
      $('body').append('<div id=\'woosw_popup\'></div>');
    }

    $('#woosw_popup').html('Loading...');

    if (key && key != '') {
      $('#woosw_popup').
          dialog({
            minWidth: 460,
            title: 'Wishlist #' + key,
            modal: true,
            dialogClass: 'wpc-dialog',
            open: function() {
              $('.ui-widget-overlay').bind('click', function() {
                $('#woosw_popup').dialog('close');
              });
            },
          });

      var data = {
        action: 'wishlist_quickview',
        key: key,
      };

      $.post(ajaxurl, data, function(response) {
        $('#woosw_popup').html(response);
      });
    }

    if (pid && pid != '') {
      $('#woosw_popup').
          dialog({
            minWidth: 460,
            title: 'Product ID #' + pid,
            modal: true,
            dialogClass: 'wpc-dialog',
            open: function() {
              $('.ui-widget-overlay').bind('click', function() {
                $('#woosw_popup').dialog('close');
              });
            },
          });

      var data = {
        action: 'wishlist_quickview',
        pid: pid,
      };

      $.post(ajaxurl, data, function(response) {
        $('#woosw_popup').html(response);
      });
    }

    e.preventDefault();
  });

  $(document).on('change', 'select[name="woosw_button_action"]', function() {
    woosw_button_action();
  });

  function woosw_button_action() {
    var action = $('select[name="woosw_button_action"]').val();

    $('.woosw_button_action_hide').hide();
    $('.woosw_button_action_' + action).show();
  }
})(jQuery);