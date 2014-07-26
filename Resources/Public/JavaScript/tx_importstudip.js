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
            },
            error: function(data, textStatus, errorThrown) {
                TYPO3.jQuery('#institutes').html('Error: '+errorThrown);
            },
        });
    },

    getExternConfigurations: function() {
        TYPO3.jQuery('#externconfigs').parents('.t3-form-field-container').show();
        TYPO3.jQuery('#externconfigs').html(this.getSpinner());
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'externconfigurations',
                institute: TYPO3.jQuery('#institutes').find('input[type="radio"]:checked').val(),
                configtype: TYPO3.jQuery('#pagetypes').find('input[type="radio"]:checked').val()
            },
            success: function(data, textStatus, jqXHR) {
                var response = TYPO3.jQuery.parseJSON(data.tx_importstudip);
                var html = '<select name="' +
                    TYPO3.jQuery('#externconfigs').data('input-name') +
                    '" size="1">';
                for (var i = 0; i < response.length; i++) {
                    html += '<option value="'+response[i].id+'">' +
                        response[i].name + '</option>';
                }
                html += '</select>';
                TYPO3.jQuery('#externconfigs').html(html);
            },
            error: function(data, textStatus, errorThrown) {
                TYPO3.jQuery('#externconfigs').html('Error: '+errorThrown);
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
                data[i].id + '" onclick="Tx_ImportStudip.getExternConfigurations()"/>' +
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