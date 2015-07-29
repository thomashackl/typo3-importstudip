Tx_ImportStudip = {

    changeSelection: function(type) {
        switch (type) {
            case 'pagetype':
                Tx_ImportStudip.adjustToPageType();
                break;
            case 'institute':
                Tx_ImportStudip.getExternConfigurations();
                Tx_ImportStudip.changeInstitute();
                break;
        }
    },

    /**
     * According to the selected page type, not all of the defined input fields
     * are needed. So this function hides or shows all necessary options for
     * the current page type.
     */
    adjustToPageType: function() {
        if (TYPO3.jQuery('#tx-importstudip-institutes').find('input.tx-importstudip-selector:checked').length == 0) {
            Tx_ImportStudip.disableInput('tx-importstudip-externconfigs');
        }
        var pagetype = TYPO3.jQuery('#tx-importstudip-pagetypes input[type="radio"]:checked').val();
        // There is a page type selected.
        if (pagetype != '' && pagetype != null) {
            switch (pagetype) {
                // Course list.
                case 'courses':
                    Tx_ImportStudip.disableInput('tx-importstudip-externconfigs');
                    Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    TYPO3.jQuery('.tx-importstudip-options-container').show();
                    Tx_ImportStudip.enableInput('tx-importstudip-aggregate',
                        {
                            action: 'aggregationform',
                            inputname: TYPO3.jQuery('#tx-importstudip-aggregate').data('input-name'),
                            value: TYPO3.jQuery('#tx-importstudip-aggregate').data('input-value')
                        }
                    );
                    Tx_ImportStudip.enableInput('tx-importstudip-coursetypes',
                        {
                            action: 'coursetypeform',
                            inputname: TYPO3.jQuery('#tx-importstudip-coursetypes').data('input-name'),
                            value: TYPO3.jQuery('#tx-importstudip-coursetypes').data('input-value')
                        }
                    );
                    Tx_ImportStudip.enableInput('tx-importstudip-subjects',
                        {
                            action: 'subjectsform',
                            parent: 'root',
                            inputname: TYPO3.jQuery('#tx-importstudip-subjects').data('input-name'),
                            value: TYPO3.jQuery('#tx-importstudip-subjects').data('input-value')
                        }
                    );
                    Tx_ImportStudip.getInstitutes();
                    break;
                // Single course.
                case 'coursedetails':
                    Tx_ImportStudip.disableInput('tx-importstudip-institutes');
                    Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-aggregate');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
                    Tx_ImportStudip.disableInput('tx-importstudip-subjects');
                    TYPO3.jQuery('.tx-importstudip-options-container').hide();
                    if (TYPO3.jQuery('#tx-importstudip-choose-course option').length > 0) {
                        Tx_ImportStudip.enableInput('tx-importstudip-coursesearch');
                        Tx_ImportStudip.enableInput('tx-importstudip-choose-course-institute',
                            {
                                action: 'choosecourseinstitute',
                                inputname: TYPO3.jQuery('#tx-importstudip-choose-course-institute').data('input-name'),
                                value: TYPO3.jQuery('#tx-importstudip-choose-course-institute').data('input-value'),
                                courseid: TYPO3.jQuery('#tx-importstudip-choose-course option:selected').val()
                            }
                        );
                        Tx_ImportStudip.getExternConfigurations('course-select');
                    } else {
                        Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                        Tx_ImportStudip.disableInput('tx-importstudip-externconfigs');
                    }
                    TYPO3.jQuery('#tx-importstudip-externconfigs').
                        closest('.t3-form-field-container').insertAfter(
                        TYPO3.jQuery('#tx-importstudip-choose-course-institute').
                            closest('.t3-form-field-container')
                    );
                    break;
                // Person list.
                case 'persons':
                    Tx_ImportStudip.disableInput('tx-importstudip-externconfigs');
                    Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
                    Tx_ImportStudip.disableInput('tx-importstudip-subjects');
                    TYPO3.jQuery('.tx-importstudip-options-container').show();
                    Tx_ImportStudip.enableInput('tx-importstudip-statusgroups',
                        {
                            action: 'statusgroupform',
                            inputname: TYPO3.jQuery('#tx-importstudip-statusgroups').data('input-name'),
                            value: TYPO3.jQuery('#tx-importstudip-statusgroups').data('input-value'),
                            institute: TYPO3.jQuery('#tx-importstudip-institutes').find('input[type="radio"]:checked').val(),
                        }
                    );
                    Tx_ImportStudip.getInstitutes();
                    break;
                // Single person.
                case 'persondetails':
                    Tx_ImportStudip.disableInput('tx-importstudip-institutes');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-aggregate');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
                    Tx_ImportStudip.disableInput('tx-importstudip-subjects');
                    TYPO3.jQuery('.tx-importstudip-options-container').hide();
                    if (TYPO3.jQuery('#tx-importstudip-choose-user option').length > 0) {
                        Tx_ImportStudip.enableInput('tx-importstudip-personsearch');
                        Tx_ImportStudip.enableInput('tx-importstudip-choose-user-institute',
                            {
                                action: 'chooseuserinstitute',
                                inputname: TYPO3.jQuery('#tx-importstudip-choose-user-institute').data('input-name'),
                                value: TYPO3.jQuery('#tx-importstudip-choose-user-institute').data('input-value'),
                                userid: TYPO3.jQuery('#tx-importstudip-personsearch').find('select option:selected').val()
                            }
                        );
                        Tx_ImportStudip.getExternConfigurations('user-select');
                    } else {
                        Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                        Tx_ImportStudip.disableInput('tx-importstudip-externconfigs');
                    }
                    TYPO3.jQuery('#tx-importstudip-externconfigs').
                        closest('.t3-form-field-container').insertAfter(
                        TYPO3.jQuery('#tx-importstudip-choose-user-institute').
                            closest('.t3-form-field-container')
                    );
                    break;
                // News list.
                case 'news':
                    Tx_ImportStudip.disableInput('tx-importstudip-externconfigs');
                    Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                    TYPO3.jQuery('.tx-importstudip-options-container').hide();
                    Tx_ImportStudip.disableInput('tx-importstudip-aggregate');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
                    Tx_ImportStudip.disableInput('tx-importstudip-subjects');
                    Tx_ImportStudip.getInstitutes();
                    break;
            }
            TYPO3.jQuery.ajax({
                url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                method: 'post',
                data: {
                    action: 'additionaloptions',
                    configtype: TYPO3.jQuery('#tx-importstudip-pagetypes').find('input[type="radio"]:checked').val()
                },
                success: function(data, textStatus, jqXHR) {
                    TYPO3.jQuery('#tx-importstudip-institutes').html(data.tx_importstudip);
                }
            });
            //TYPO3.jQuery('#tx-importstudip-options').closest('.t3-form-field-container').show();
        // No page type set, show default view.
        } else {
            Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
            Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
            Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
            Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
            Tx_ImportStudip.disableInput('tx-importstudip-options');
            Tx_ImportStudip.disableInput('tx-importstudip-aggregate');
            Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
            Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
            Tx_ImportStudip.disableInput('tx-importstudip-subjects');
        }
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
                        if (TYPO3.jQuery('#tx-importstudip-institutes').find('input[type="radio"]:checked').length > 0) {
                            Tx_ImportStudip.getExternConfigurations('radio');
                        }
                    }
                });
            },
            error: function(data, textStatus, errorThrown) {
                TYPO3.jQuery('#tx-importstudip-institutes').html('Error: '+errorThrown);
            }
        });
    },

    changeInstitute: function() {
        // Set course types for chosen institute.
        if (TYPO3.jQuery('#tx-importstudip-pagetypes').find('input[type="radio"]:checked').val() == 'courses') {
            TYPO3.jQuery.ajax({
                url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                method: 'post',
                data: {
                    action: 'coursetypeform',
                    institute: TYPO3.jQuery('#tx-importstudip-institutes').find('input[type="radio"]:checked').val(),
                    inputname: TYPO3.jQuery('#tx-importstudip-coursetypes').data('input-name'),
                    selected: TYPO3.jQuery('#tx-importstudip-coursetypes').data('input-value')
                },
                success: function(response, textStatus, jqXHR) {
                    TYPO3.jQuery('#tx-importstudip-coursetypes').html(response.tx_importstudip);
                }
            });
        }
    },

    /**
     * Fetches available external page configurations.
     *
     * @param sourcetype 'radio', 'user-select' or 'course-select':
     *                   Use the full list of all institutes or a
     *                   given selection of institutes.
     */
    getExternConfigurations: function(sourcetype) {
        var div = TYPO3.jQuery('#tx-importstudip-externconfigs');
        div.parents('.t3-form-field-container').show();
        div.html(Tx_ImportStudip.getSpinner(div.data('loading-text')));
        var source = null;
        /*
         * Is there a full institute hierarchy with radio buttons or a select
         * with only some chosen institutes?
         */
        switch (sourcetype) {
            // We come from single user selection.
            case 'user-select':
                var source  = TYPO3.jQuery('#tx-importstudip-choose-user-institute').children('select');
                if (source.children('option:selected').length > 0) {
                    source = source.children('option:selected').val();
                } else {
                    source = source.children('option').first().val();
                }
                break;
            // We come from single course selection.
            case 'course-select':
                var source  = TYPO3.jQuery('#tx-importstudip-choose-course-institute').children('select');
                if (source.children('option:selected').length > 0) {
                    source = source.children('option:selected').val();
                } else {
                    source = source.children('option').first().val();
                }
                break;
            // Full institute hierarchy with radio buttons.
            case 'radio':
            default:
                source = TYPO3.jQuery('#tx-importstudip-institutes').find('input[type="radio"]:checked').val();
                console.log(source);
                break;
        }
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'externconfigurations',
                institute: source,
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
            }
        });
    },

    performPersonSearch: function() {
        TYPO3.jQuery('#tx-importstudip-personsearch-result').html(Tx_ImportStudip.getSpinner(''));
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'personsearchform',
                searchterm: TYPO3.jQuery('#tx-importstudip-personsearchterm').val(),
                inputname: TYPO3.jQuery('#tx-importstudip-personsearch').data('input-name'),
                selected: TYPO3.jQuery('#tx-importstudip-personsearch').data('input-value')
            },
            success: function(response, textStatus, jqXHR) {
                TYPO3.jQuery('#tx-importstudip-personsearch-result').html(response.tx_importstudip);
                Tx_ImportStudip.getPersonInstitutes();
            },
            error: function(data, textStatus, errorThrown) {
                alert('Error: '+errorThrown);
            }
        });
        return false;
    },

    getPersonInstitutes: function() {
        TYPO3.jQuery('#tx-importstudip-choose-user-institute').html(Tx_ImportStudip.getSpinner(''));
        var userselect = TYPO3.jQuery('#tx-importstudip-choose-user');
        if (userselect.children('option:selected').length > 0) {
            var user = TYPO3.jQuery('#tx-importstudip-choose-user option:selected').val();
        } else {
            var user = TYPO3.jQuery('#tx-importstudip-choose-user option').first().val();
        }
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'chooseuserinstitute',
                userid: user,
                inputname: TYPO3.jQuery('#tx-importstudip-choose-user-institute').data('input-name'),
                selected: TYPO3.jQuery('#tx-importstudip-choose-user-institute').data('input-value')
            },
            success: function(response, textStatus, jqXHR) {
                TYPO3.jQuery('#tx-importstudip-choose-user-institute').html(response.tx_importstudip);
                Tx_ImportStudip.getExternConfigurations('user-select');
            },
            error: function(data, textStatus, errorThrown) {
                alert('Error: '+errorThrown);
            }
        });
        return false;
    },

    performCourseSearch: function() {
        TYPO3.jQuery('#tx-importstudip-coursesearch-result').html(Tx_ImportStudip.getSpinner(''));
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'coursesearchform',
                searchterm: TYPO3.jQuery('#tx-importstudip-coursesearchterm').val(),
                semester: TYPO3.jQuery('#tx-importstudip-semester option:selected').val(),
                inputname: TYPO3.jQuery('#tx-importstudip-coursesearch').data('input-name'),
                selected: TYPO3.jQuery('#tx-importstudip-coursesearch').data('input-value')
            },
            success: function(response, textStatus, jqXHR) {
                TYPO3.jQuery('#tx-importstudip-coursesearch-result').html(response.tx_importstudip);
                Tx_ImportStudip.getCourseInstitutes();
            },
            error: function(data, textStatus, errorThrown) {
                alert('Error: '+errorThrown);
            }
        });
        return false;
    },

    getCourseInstitutes: function() {
        TYPO3.jQuery('#tx-importstudip-choose-course-institute').html(Tx_ImportStudip.getSpinner(''));
        var courseselect = TYPO3.jQuery('#tx-importstudip-choose-course');
        if (courseselect.children('option:selected').length > 0) {
            var course = TYPO3.jQuery('#tx-importstudip-choose-course option:selected').val();
        } else {
            var course = TYPO3.jQuery('#tx-importstudip-choose-course option').first().val();
        }
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'choosecourseinstitute',
                courseid: course,
                inputname: TYPO3.jQuery('#tx-importstudip-choose-course-institute').data('input-name'),
                selected: TYPO3.jQuery('#tx-importstudip-choose-course-institute').data('input-value')
            },
            success: function(response, textStatus, jqXHR) {
                TYPO3.jQuery('#tx-importstudip-choose-course-institute').html(response.tx_importstudip);
                Tx_ImportStudip.getExternConfigurations('course-select');
            },
            error: function(data, textStatus, errorThrown) {
                alert('Error: '+errorThrown);
            }
        });
        return false;
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
            }
        });
    },

    openSelectedParents: function(id) {
        TYPO3.jQuery('#'+id).find('.tx-importstudip-selector:checked').
            parents('.tx-importstudip-treebranch').
            children('input.tx-importstudip-treeinput').attr('checked', true);
    },

    enableInput: function(id, parameters) {
        TYPO3.jQuery('#'+id).closest('.t3-form-field-container').show();
        if (parameters != '' && parameters != null) {
            TYPO3.jQuery.ajax({
                url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                method: 'post',
                data: parameters,
                success: function (response, textStatus, jqXHR) {
                    TYPO3.jQuery('#' + id).html(response.tx_importstudip);
                },
                error: function (data, textStatus, errorThrown) {
                    alert('Error: ' + errorThrown);
                }
            });
        }
    },

    disableInput: function(id) {
        TYPO3.jQuery('#'+id).html();
        TYPO3.jQuery('#'+id).closest('.t3-form-field-container').hide();
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
    var container = options.closest('.t3-form-field-container');
    container.addClass('tx-importstudip-options-container');
    container.children('.t3-form-field-label').prepend('<img src="gfx/ol/plusbullet.gif" data-toggle-image="gfx/ol/minusbullet.gif"/>');
    container.children('.t3-form-field-label').on('click', function() {
        options.toggle();
        var img = TYPO3.jQuery('.tx-importstudip-options-container').children('.t3-form-field-label').children('img');
        var src = img.attr('src');
        var other = img.data('toggle-image');
        img.attr('src', other);
        img.data('toggle-image', src);
    });
    Tx_ImportStudip.adjustToPageType();
});
