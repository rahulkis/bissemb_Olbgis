jQuery(document).ready(function(n){"use strict";n(document).on("click",".install-tutor-button",function(t){t.preventDefault();var o=n(this);n.ajax({type:"POST",url:ajaxurl,data:{install_plugin:"tutor",action:"install_tutor_plugin"},beforeSend:function(){o.addClass("is-loading")},success:function(t){n(".install-tutor-button").remove(),n("#tutor_install_msg").html(t)},complete:function(){o.removeClass("is-loading")}})}),n(document).on("click","#import-gradebook-sample-data",function(t){t.preventDefault();var o=n(this);n.ajax({type:"POST",url:ajaxurl,data:{action:"import_gradebook_sample_data"},beforeSend:function(){o.addClass("is-loading")},success:function(t){t.success&&location.reload()},complete:function(){o.removeClass("is-loading")}})}),n('[name="tutor_option[tutor_email_disable_wpcron]"]').change(function(){n('[name="tutor_option[tutor_email_cron_frequency]"]').closest(".tutor-option-field-row")[n(this).prop("checked")?"hide":"show"]()}).trigger("change")});