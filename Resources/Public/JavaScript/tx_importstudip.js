Tx_ImportStudip = {

    getInstitutes: function() {
        TYPO3.jQuery('#institutes').parents('.t3-form-field-container').show();
        TYPO3.jQuery('#institutes').html(this.getSpinner());
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'institutes'
            },
            success: function(data, textStatus, jqXHR) {
                TYPO3.jQuery('#institutes').html(Tx_ImportStudip.buildTree(TYPO3.jQuery.parseJSON(data.tx_importstudip)));
                //TYPO3.jQuery('#institutes').html(data.tx_importstudip);
            },
            error: function(data, textStatus, errorThrown) {
                TYPO3.jQuery('#institutes').html('Error: '+errorThrown);
            },
        });
    },

    getSpinner: function() {
        return '<img src="gfx/spinner.gif"/>';
    },

    buildTree: function(data) {
        var html = '<ul>';
        for (var i = 0; i < data.length; i++) {
            html += '<li class="' +
                (data[i].children != null ? 'tx_importstudip_treebranch' : 'tx_importstudip_treeleaf') +
                '">' +
                '<input type="radio" name="' + 
                TYPO3.jQuery('#institutes').data('input-name') + '" value="' +
                data[i].id + '"/>' +
                '<label for="' + data[i].id + '">' + data[i].name + '</label>' +
                '<input type="checkbox" class="tx_importstudip_treeinput" id="' +
                data[i].id + '"/>';
            if (data[i].children != null) {
                html += Tx_ImportStudip.buildTree(data[i].children);
            }
            html += '</li>';
        }
        html += '</ul>';
        return html;
    }

};