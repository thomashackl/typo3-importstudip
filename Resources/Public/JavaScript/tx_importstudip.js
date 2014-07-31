Tx_ImportStudip = {

    getInstitutes: function() {
        TYPO3.jQuery('#tx-importstudip-institutes').parents('.t3-form-field-container').show();
        TYPO3.jQuery('#tx-importstudip-institutes').html(this.getSpinner());
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'institutes'
            },
            success: function(data, textStatus, jqXHR) {
                TYPO3.jQuery.ajax({
                    url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                    method: 'post',
                    data: {
                        action: 'instituteform',
                        institutes: data.tx_importstudip,
                        inputname: TYPO3.jQuery('#tx-importstudip-institutes').data('input-name'),
                        selected: TYPO3.jQuery('#tx-importstudip-institutes').data('input-value')
                    },
                    success: function(response, textStatus, jqXHR) {
                        TYPO3.jQuery('#tx-importstudip-institutes').html(response.tx_importstudip);
                    }
                });
            },
            error: function(data, textStatus, errorThrown) {
                TYPO3.jQuery('#tx-importstudip-institutes').html('Error: '+errorThrown);
            },
        });
    },

    getExternConfigurations: function() {
        TYPO3.jQuery('#tx-importstudip-externconfigs').parents('.t3-form-field-container').show();
        TYPO3.jQuery('#tx-importstudip-externconfigs').html(this.getSpinner());
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'externconfigurations',
                institute: TYPO3.jQuery('#tx-importstudip-institutes').find('input[type="radio"]:checked').val(),
                configtype: TYPO3.jQuery('#tx-importstudip-pagetypes').find('input[type="radio"]:checked').val()
            },
            success: function(data, textStatus, jqXHR) {
                TYPO3.jQuery.ajax({
                    url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                    method: 'post',
                    data: {
                        action: 'externconfigurationsform',
                        configurations: data.tx_importstudip,
                        inputname: TYPO3.jQuery('#tx-importstudip-externconfigs').data('input-name'),
                        selected: TYPO3.jQuery('#tx-importstudip-externconfigs').data('input-value')
                    },
                    success: function(response, textStatus, jqXHR) {
                        TYPO3.jQuery('#tx-importstudip-externconfigs').html(response.tx_importstudip);
                    }
                });
                TYPO3.jQuery('#tx-importstudip-externconfigs').html(html);
            },
            error: function(data, textStatus, errorThrown) {
                TYPO3.jQuery('#tx-importstudip-externconfigs').html('Error: '+errorThrown);
            },
        });
    },

    getSpinner: function() {
        return '<img src="gfx/spinner.gif"/>';
    },

};

TYPO3.jQuery(function () {
    // Open parent tree structure of selected nodes.
    TYPO3.jQuery('#tx-importstudip-institutes').find('.tx_importstudip_selector:checked').
        parents('.tx_importstudip_treebranch').
        children('input.tx_importstudip_treeinput').attr('checked', true);
    TYPO3.jQuery('#tx-importstudip-options').append(TYPO3.jQuery('#tx-importstudip-coursetypes'));
    TYPO3.jQuery('#tx-importstudip-options').append(TYPO3.jQuery('#tx-importstudip-subjects'));
    TYPO3.jQuery('#tx-importstudip-options').append(TYPO3.jQuery('#tx-importstudip-statusgroups'));
});
