jQuery(document).ready(function(){
    jQuery('.zoho_campaigns_gl').click(function(){
        ListsSelect=jQuery('#'+jQuery(this).attr('rel-id'));
        ListsSelect.find('option').remove();
        jQuery("<option/>").val(0).text('Loading...').appendTo(ListsSelect);
        jQuery.ajax({
            url: ajaxurl,
            data:{
                'action': 'snp_ml_list',
                'ml_manager': 'zoho_campaign'
            },
            dataType: 'JSON',
            type: 'POST',
            success:function(response){
                ListsSelect.find('option').remove();
                jQuery.each(response, function(i, option)
                {
                    jQuery("<option/>").val(i).text(option.name).appendTo(ListsSelect);
                });
            },
            error: function(errorThrown){
                alert('Error...');
            }
        });
    });
});