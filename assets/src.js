jQuery(() => {
    jQuery("#add_default_pattern").click(() => {
        jQuery("#pattern").val(jQuery("#_pattern_default").val());
        return false
    })

    jQuery(".shortcode-code").click(function (elm) {
        var text = jQuery(elm)
        jQuery("#pattern").val(jQuery("#pattern").val() + ' ' + text[0].currentTarget.innerHTML)
        return false
    });
})