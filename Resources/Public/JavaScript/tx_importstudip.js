Tx_ImportStudip = {

    changeSelection: function(type) {
        switch (type) {
            case 'pagetype':
                Tx_ImportStudip.adjustToPageType();
                break;
            case 'institute':
                Tx_ImportStudip.getExternConfigurations();
            case 'content':
        }
    },

    adjustToPageType: function() {
        TYPO3.jQuery('#tx-importstudip-externconfigs').html();
        TYPO3.jQuery('#tx-importstudip-externconfigs').closest('.t3-form-field-container').hide();
        Tx_ImportStudip.getInstitutes();
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'additionaloptions',
                configtype: TYPO3.jQuery('#tx-importstudip-pagetypes').find('input[type="radio"]:checked').val()
            },
            success: function(data, textStatus, jqXHR) {
                TYPO3.jQuery('#tx-importstudip-institutes').html(response.tx_importstudip);
            }
        });
    },

    getInstitutes: function() {
        var div = TYPO3.jQuery('#tx-importstudip-institutes');
        div.parents('.t3-form-field-container').show();
        div.html(Tx_ImportStudip.getSpinner(div.data('loading-text')));
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
                        Tx_ImportStudip.openSelectedParents('tx-importstudip-institutes');
                    }
                });
            },
            error: function(data, textStatus, errorThrown) {
                TYPO3.jQuery('#tx-importstudip-institutes').html('Error: '+errorThrown);
            },
        });
    },

    getExternConfigurations: function() {
        var div = TYPO3.jQuery('#tx-importstudip-externconfigs');
        div.parents('.t3-form-field-container').show();
        div.html(Tx_ImportStudip.getSpinner(div.data('loading-text')));
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

    openSelectedParents: function(id) {
        TYPO3.jQuery('#'+id).find('.tx-importstudip-selector:checked').
            parents('.tx-importstudip-treebranch').
            children('input.tx-importstudip-treeinput').attr('checked', true);
    },

    getSubjects: function(id) {
        var li = TYPO3.jQuery('#tx-importstudip-subject-'+id).siblings('ul.tx-importstudip-tree').children('li');
        li.show();
        li.html(Tx_ImportStudip.getSpinner(li.data('loading-text')));
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'subjectsform',
                parent: id,
                inputname: TYPO3.jQuery('#tx-importstudip-subjects').data('input-name'),
                selected: TYPO3.jQuery('#tx-importstudip-subjects').data('input-value')
            },
            success: function(response, textStatus, jqXHR) {
                TYPO3.jQuery('#tx-importstudip-subject-'+id).siblings('ul.tx-importstudip-tree').remove();
                TYPO3.jQuery('#tx-importstudip-subject-'+id).parent().append(response.tx_importstudip);
                TYPO3.jQuery('#tx-importstudip-subject-'+id).removeAttr('onclick');
            },
            error: function(data, textStatus, errorThrown) {
                alert('Error: '+errorThrown);
            },
        });
    },

    getSpinner: function(text) {
        html = '<img src="gfx/spinner.gif"/>';
        if (text != '') {
            html += text;
        }
        return html;
    }

};

TYPO3.jQuery(function () {
    Tx_ImportStudip.openSelectedParents('tx-importstudip-institutes');
    var options = TYPO3.jQuery('#tx-importstudip-options');
    // Open parent tree structure of selected nodes.
    options.append(TYPO3.jQuery('#tx-importstudip-aggregate').closest('.t3-form-field-container'));
    options.append(TYPO3.jQuery('#tx-importstudip-coursetypes').closest('.t3-form-field-container'));
    options.append(TYPO3.jQuery('#tx-importstudip-subjects').closest('.t3-form-field-container'));
    options.append(TYPO3.jQuery('#tx-importstudip-statusgroups').closest('.t3-form-field-container'));
    options.hide();
    options.closest('.t3-form-field-container').on('click', function() {
        options.toggle();
    });
});
