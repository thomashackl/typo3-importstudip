Tx_ImportStudip = {

    getInstitutes: function() {
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'institutes'
            },
            success: function(data, textStatus, jqXHR) {
                TYPO3.jQuery('#institutes').html('So schauts aus.');
                TYPO3.jQuery('#institutes').parents('.t3-form-field-container').show();
            },
            error: function(data, textStatus, errorThrown) {
                console.log('Error:');
                console.log(data);
                console.log(textStatus);
                console.log(errorThrown);
            },
        });
    }

};