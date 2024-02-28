jQuery(document).ready(function()
{
    // Form Submit Event
    jQuery('.btn-submit').click(function() {
        jQuery('form#'+jQuery(this).data('form')).submit();
    });
    //------------------
});
