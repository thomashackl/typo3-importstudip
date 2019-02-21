Tx_ImportStudip = {

    changeSelection: function(type, selected) {
        switch (type) {
            case 'pagetype':
                Tx_ImportStudip.adjustToPageType();
                break;
            case 'institute':
                Tx_ImportStudip.getExternConfigurations();
                Tx_ImportStudip.changeInstitute(selected);
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
            Tx_ImportStudip.disableInput('tx-importstudip-externconfig');
        }
        var pagetype = TYPO3.jQuery('#tx-importstudip-pagetypes input[type="radio"]:checked').val();

        var persondetailtarget = TYPO3.jQuery('select[data-formengine-input-name*="persondetailtarget"]').
            closest(Tx_ImportStudip.getElementName('container'));
        persondetailtarget.hide();
        var coursedetailtarget = TYPO3.jQuery('select[data-formengine-input-name*="coursedetailtarget"]').
            closest(Tx_ImportStudip.getElementName('container'));
        coursedetailtarget.hide();
        var newsdetailtarget = TYPO3.jQuery('select[data-formengine-input-name*="newsdetailtarget"]').
            closest(Tx_ImportStudip.getElementName('container'));
        newsdetailtarget.hide();
        var linktarget = TYPO3.jQuery('select[data-formengine-input-name*="linktarget"]').
            closest(Tx_ImportStudip.getElementName('container'));
        linktarget.hide();
        var module = TYPO3.jQuery('#tx-importstudip-module').
            closest(Tx_ImportStudip.getElementName('container'));
        module.hide();
        // There is a page type selected.
        if (pagetype != '' && pagetype != null) {
            switch (pagetype) {
                // Course list.
                case 'courses':
                    //Tx_ImportStudip.disableInput('tx-importstudip-externconfig');
                    Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-preselectinst');
                    Tx_ImportStudip.disableInput('tx-importstudip-smallnews');
                    TYPO3.jQuery('.tx-importstudip-filters-container').show();
                    Tx_ImportStudip.enableInput('tx-importstudip-aggregate',
                        {
                            action: 'aggregationform',
                            inputname: TYPO3.jQuery('#tx-importstudip-aggregate').data('input-name'),
                            value: TYPO3.jQuery('#tx-importstudip-aggregate').data('input-value')
                        }
                    );
                    Tx_ImportStudip.enableInput('tx-importstudip-participating',
                        {
                            action: 'participatingform',
                            inputname: TYPO3.jQuery('#tx-importstudip-participating').data('input-name'),
                            value: TYPO3.jQuery('#tx-importstudip-participating').data('input-value')
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
                    Tx_ImportStudip.getInstitutes('tx-importstudip-institutes');
                    TYPO3.jQuery('div.tx-importstudip-linkingoptions-container').show();
                    persondetailtarget.show();
                    coursedetailtarget.show();
                    if (TYPO3.jQuery('#tx-importstudip-institutes').find('input[name*="settings.institute"]:checked').length > 0) {
                        Tx_ImportStudip.getExternConfigurations('radio');
                    }
                    break;
                // Single course.
                case 'coursedetails':
                    Tx_ImportStudip.disableInput('tx-importstudip-institutes');
                    Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-aggregate');
                    Tx_ImportStudip.disableInput('tx-importstudip-participating');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
                    Tx_ImportStudip.disableInput('tx-importstudip-subjects');
                    Tx_ImportStudip.disableInput('tx-importstudip-preselectinst');
                    Tx_ImportStudip.disableInput('tx-importstudip-smallnews');
                    TYPO3.jQuery('.tx-importstudip-filters-container').hide();
                    Tx_ImportStudip.enableInput('tx-importstudip-coursesearch');
                    if (TYPO3.jQuery('#tx-importstudip-choose-course option').length > 0) {
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
                        Tx_ImportStudip.disableInput('tx-importstudip-externconfig');
                    }
                    TYPO3.jQuery('#tx-importstudip-externconfig').
                        closest(Tx_ImportStudip.getElementName('container')).insertAfter(
                        TYPO3.jQuery('#tx-importstudip-choose-course-institute').
                            closest(Tx_ImportStudip.getElementName('container'))
                    );
                    TYPO3.jQuery('div.tx-importstudip-linkingoptions-container').show();
                    persondetailtarget.show();
                    break;
                // Person list.
                case 'persons':
                    Tx_ImportStudip.disableInput('tx-importstudip-externconfig');
                    Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-participating');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
                    Tx_ImportStudip.disableInput('tx-importstudip-subjects');
                    Tx_ImportStudip.disableInput('tx-importstudip-preselectinst');
                    Tx_ImportStudip.disableInput('tx-importstudip-smallnews');
                    TYPO3.jQuery('.tx-importstudip-filters-container').show();
                    if (TYPO3.jQuery('#tx-importstudip-institutes').find('input[type="radio"]:checked').length != 0) {
                        Tx_ImportStudip.enableInput('tx-importstudip-statusgroups',
                            {
                                action: 'statusgroupform',
                                inputname: TYPO3.jQuery('#tx-importstudip-statusgroups').data('input-name'),
                                value: TYPO3.jQuery('#tx-importstudip-statusgroups').data('input-value'),
                                institute: TYPO3.jQuery('#tx-importstudip-institutes').find('input[type="radio"]:checked').val(),
                            }
                        );
                    }
                    Tx_ImportStudip.getInstitutes('tx-importstudip-institutes');
                    TYPO3.jQuery('div.tx-importstudip-linkingoptions-container').show();
                    persondetailtarget.show();
                    coursedetailtarget.show();
                    if (TYPO3.jQuery('#tx-importstudip-institutes').find('input[name*="settings.institute"]:checked').length > 0) {
                        Tx_ImportStudip.getExternConfigurations('radio');
                    }
                    break;
                // Single person.
                case 'persondetails':
                    Tx_ImportStudip.disableInput('tx-importstudip-institutes');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-aggregate');
                    Tx_ImportStudip.disableInput('tx-importstudip-participating');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
                    Tx_ImportStudip.disableInput('tx-importstudip-subjects');
                    Tx_ImportStudip.disableInput('tx-importstudip-preselectinst');
                    Tx_ImportStudip.disableInput('tx-importstudip-smallnews');
                    TYPO3.jQuery('.tx-importstudip-filters-container').hide();
                    Tx_ImportStudip.enableInput('tx-importstudip-personsearch');
                    if (TYPO3.jQuery('#tx-importstudip-choose-user option').length > 0) {
                        var user = TYPO3.jQuery('#tx-importstudip-personsearch').find('select option:selected');
                        if (user.length < 1) {
                            user = TYPO3.jQuery('#tx-importstudip-personsearch').find('select option').first();
                        }
                        Tx_ImportStudip.getPersonInstitutes();
                        Tx_ImportStudip.enableInput('tx-importstudip-choose-user-institute');
                    } else {
                        Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                        Tx_ImportStudip.disableInput('tx-importstudip-externconfig');
                    }
                    TYPO3.jQuery('#tx-importstudip-externconfig').
                        closest(Tx_ImportStudip.getElementName('container')).insertAfter(
                        TYPO3.jQuery('#tx-importstudip-choose-user-institute').
                            closest(Tx_ImportStudip.getElementName('container'))
                    );
                    TYPO3.jQuery('div.tx-importstudip-linkingoptions-container').show();
                    coursedetailtarget.show();
                    break;
                // News list.
                case 'news':
                    Tx_ImportStudip.disableInput('tx-importstudip-externconfig');
                    Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-aggregate');
                    Tx_ImportStudip.disableInput('tx-importstudip-participating');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
                    Tx_ImportStudip.disableInput('tx-importstudip-subjects');
                    Tx_ImportStudip.disableInput('tx-importstudip-preselectinst');
                    Tx_ImportStudip.getInstitutes('tx-importstudip-institutes');
                    Tx_ImportStudip.enableInput('tx-importstudip-smallnews',
                        {
                            action: 'smallnewsform',
                            inputname: TYPO3.jQuery('#tx-importstudip-smallnews').data('input-name'),
                            value: TYPO3.jQuery('#tx-importstudip-smallnews').data('input-value')
                        }
                    );
                    TYPO3.jQuery('div.tx-importstudip-linkingoptions-container').show();
                    newsdetailtarget.show();
                    if (TYPO3.jQuery('#tx-importstudip-institutes').find('input[name*="settings.institute"]:checked').length > 0) {
                        Tx_ImportStudip.getExternConfigurations('radio');
                    }
                    break;
                // Course search
                case 'searchpage':
                    Tx_ImportStudip.disableInput('tx-importstudip-institutes');
                    Tx_ImportStudip.disableInput('tx-importstudip-externconfig');
                    Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
                    TYPO3.jQuery('.tx-importstudip-filters-container').hide();
                    TYPO3.jQuery('.tx-importstudip-linkingoptions-container').hide();
                    Tx_ImportStudip.disableInput('tx-importstudip-aggregate');
                    Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
                    Tx_ImportStudip.disableInput('tx-importstudip-participating');
                    Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
                    Tx_ImportStudip.disableInput('tx-importstudip-subjects');
                    Tx_ImportStudip.disableInput('tx-importstudip-smallnews');
                    Tx_ImportStudip.getInstitutes('tx-importstudip-preselectinst');
                    TYPO3.jQuery('div.tx-importstudip-linkingoptions-container').hide();
            }
            TYPO3.jQuery.ajax({
                url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                method: 'post',
                data: {
                    action: 'additionalfilters',
                    configtype: TYPO3.jQuery('#tx-importstudip-pagetypes').find('input[type="radio"]:checked').val()
                },
                success: function(data, textStatus, jqXHR) {
                    //TYPO3.jQuery('#tx-importstudip-institutes').html(data.tx_importstudip);
                }
            });
            if (TYPO3.jQuery('#tx-importstudip-makelink-checkbox').prop('checked')) {
                Tx_ImportStudip.enableInput('tx-importstudip-linktext');
                Tx_ImportStudip.enableInput('tx-importstudip-linkformat');
            } else {
                Tx_ImportStudip.disableInput('tx-importstudip-linktext');
                Tx_ImportStudip.disableInput('tx-importstudip-linkformat');
            }
        // No page type set, show default view.
        } else {
            Tx_ImportStudip.disableInput('tx-importstudip-institutes');
            Tx_ImportStudip.disableInput('tx-importstudip-personsearch');
            Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
            Tx_ImportStudip.disableInput('tx-importstudip-coursesearch');
            Tx_ImportStudip.disableInput('tx-importstudip-choose-course-institute');
            Tx_ImportStudip.disableInput('tx-importstudip-filters');
            Tx_ImportStudip.disableInput('tx-importstudip-aggregate');
            Tx_ImportStudip.disableInput('tx-importstudip-statusgroups');
            Tx_ImportStudip.disableInput('tx-importstudip-participating');
            Tx_ImportStudip.disableInput('tx-importstudip-coursetypes');
            Tx_ImportStudip.disableInput('tx-importstudip-subjects');
            Tx_ImportStudip.disableInput('tx-importstudip-smallnews');
            Tx_ImportStudip.disableInput('tx-importstudip-linktext');
            Tx_ImportStudip.disableInput('tx-importstudip-linkformat');
            Tx_ImportStudip.disableInput('tx-importstudip-persondetailtarget');
            Tx_ImportStudip.disableInput('tx-importstudip-coursedetailtarget');
            Tx_ImportStudip.disableInput('tx-importstudip-newsdetailtarget');
            TYPO3.jQuery('div.tx-importstudip-linkingoptions-container').hide();
        }
    },

    adjustLinkOptions: function() {
        if (TYPO3.jQuery('#tx-importstudip-makelink-checkbox').prop('checked')) {
            TYPO3.jQuery('input[data-formengine-input-name*="settings.persondetailtarget"]').closest(Tx_ImportStudip.getElementName('label')).hide();
            TYPO3.jQuery('input[data-formengine-input-name*="settings.coursedetailtarget"]').closest(Tx_ImportStudip.getElementName('label')).hide();
            TYPO3.jQuery('input[data-formengine-input-name*="settings.newsdetailtarget"]').closest(Tx_ImportStudip.getElementName('label')).hide();
            Tx_ImportStudip.enableInput('tx-importstudip-linktext');
            Tx_ImportStudip.enableInput('tx-importstudip-linkformat');
            TYPO3.jQuery('input[data-formengine-input-name*="settings.linktarget"]').closest(Tx_ImportStudip.getElementName('label')).show();
            TYPO3.jQuery('#tx-importstudip-makelink-hidden').val(1);
        } else {
            if (TYPO3.jQuery('#courses:checked').length > 0 || TYPO3.jQuery('#coursedetails:checked').length > 0) {
                TYPO3.jQuery('input[data-formengine-input-name*="settings.persondetailtarget"]').closest(Tx_ImportStudip.getElementName('label')).show();
            }
            if (TYPO3.jQuery('#courses:checked').length > 0 || TYPO3.jQuery('#persondetails:checked').length > 0) {
                TYPO3.jQuery('input[data-formengine-input-name*="settings.coursedetailtarget"]').closest(Tx_ImportStudip.getElementName('label')).show();
            }
            if (TYPO3.jQuery('#news:checked').length > 0) {
                TYPO3.jQuery('input[data-formengine-input-name*="settings.newsdetailtarget"]').closest(Tx_ImportStudip.getElementName('label')).show();
            }
            Tx_ImportStudip.disableInput('tx-importstudip-linktext');
            Tx_ImportStudip.disableInput('tx-importstudip-linkformat');
            TYPO3.jQuery('input[data-formengine-input-name*="settings.linktarget"]').closest(Tx_ImportStudip.getElementName('label')).hide();
            TYPO3.jQuery('#tx-importstudip-makelink-hidden').val(0);
        }
    },

    /**
     * Fetches the list of institutes.
     * @param elementId parent the list should be appended to
     */
    getInstitutes: function(elementId) {
        var div = TYPO3.jQuery('#' + elementId);
        div.parents(Tx_ImportStudip.getElementName('container')).show();
        if (TYPO3.jQuery('#' + elementId).find('input[type="radio"]').length == 0) {
            div.html(Tx_ImportStudip.getSpinner(div.data('loading-text')));
            TYPO3.jQuery.ajax({
                url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                method: 'post',
                data: {
                    action: 'institutes',
                    configtype: TYPO3.jQuery('#tx-importstudip-pagetypes input[type="radio"]:checked').val()
                },
                success: function(data, textStatus, jqXHR) {
                    TYPO3.jQuery.ajax({
                        url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                        method: 'post',
                        data: {
                            action: 'instituteform',
                            institutes: data.tx_importstudip,
                            inputname: div.data('input-name'),
                            selected: div.data('input-value')
                        },
                        success: function(response, textStatus, jqXHR) {
                            div.html(response.tx_importstudip);
                            Tx_ImportStudip.openSelectedParents(elementId);
                            if (div.find('input[type="radio"]:checked').length > 0) {
                                Tx_ImportStudip.getExternConfigurations('radio');
                            }
                        }
                    });
                },
                error: function(data, textStatus, errorThrown) {
                    div.html('Error: '+errorThrown);
                }
            });
        }
    },

    /**
     * Actions to execute if the selected institute is changed.
     */
    changeInstitute: function(selected) {
        var type = TYPO3.jQuery('#tx-importstudip-pagetypes input[type="radio"]:checked').val();
        // Set course types for chosen institute.
        if (type == 'courses') {
            Tx_ImportStudip.getExternConfigurations('radio');
            TYPO3.jQuery.ajax({
                url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                method: 'post',
                data: {
                    action: 'coursetypeform',
                    institute: selected,
                    inputname: TYPO3.jQuery('#tx-importstudip-coursetypes').data('input-name'),
                    selected: TYPO3.jQuery('#tx-importstudip-coursetypes').data('input-value')
                },
                success: function(response, textStatus, jqXHR) {
                    TYPO3.jQuery('#tx-importstudip-coursetypes').html(response.tx_importstudip);
                }
            });
        // Set statusgroups for chosen institute.
        } else if (type == 'persons') {
            Tx_ImportStudip.enableInput('tx-importstudip-aggregate',
                {
                    action: 'aggregationform',
                    inputname: TYPO3.jQuery('#tx-importstudip-aggregate').data('input-name'),
                    value: TYPO3.jQuery('#tx-importstudip-aggregate').data('input-value')
                }
            );
            Tx_ImportStudip.enableInput('tx-importstudip-statusgroups',
                {
                    action: 'statusgroupform',
                    inputname: TYPO3.jQuery('#tx-importstudip-statusgroups').data('input-name'),
                    value: TYPO3.jQuery('#tx-importstudip-statusgroups').data('input-value'),
                    institute: selected,
                }
            );
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
        var div = TYPO3.jQuery('#tx-importstudip-externconfig');
        div.parents(Tx_ImportStudip.getElementName('container')).show();
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
                    source = source.children('option:selected');
                } else {
                    source = source.children('option').first();
                }
                break;
            // We come from single course selection.
            case 'course-select':
                var source = TYPO3.jQuery('#tx-importstudip-choose-course-institute').children('select');
                if (source.children('option:selected').length > 0) {
                    source = source.children('option:selected');
                } else {
                    source = source.children('option').first();
                }
                break;
            // Full institute hierarchy with radio buttons.
            case 'radio':
            default:
                source = TYPO3.jQuery('#tx-importstudip-institutes').find('input[type="radio"]:checked');
                break;
        }
        TYPO3.jQuery.ajax({
            url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
            method: 'post',
            data: {
                action: 'externconfigurations',
                institute: source.val(),
                configtype: TYPO3.jQuery('#tx-importstudip-pagetypes').find('input[type="radio"]:checked').val()
            },
            success: function(data, textStatus, jqXHR) {
                TYPO3.jQuery.ajax({
                    url: TYPO3.settings.ajaxUrls['ImportStudip::AjaxHandler'],
                    method: 'post',
                    data: {
                        action: 'externconfigurationsform',
                        configurations: data.tx_importstudip,
                        inputname: TYPO3.jQuery('#tx-importstudip-externconfig').data('input-name'),
                        selected: TYPO3.jQuery('#tx-importstudip-externconfig').data('input-value')
                    },
                    success: function(response, textStatus, jqXHR) {
                        TYPO3.jQuery('#tx-importstudip-externconfig').html(response.tx_importstudip);
                        Tx_ImportStudip.setModuleName();
                    },
                    error: function(data, textStatus, errorThrown) {
                        TYPO3.jQuery('#tx-importstudip-externconfig').html('Error: '+errorThrown);
                    }
                });
                TYPO3.jQuery('#tx-importstudip-externconfig').html(html);
            },
            error: function(data, textStatus, errorThrown) {
                TYPO3.jQuery('#tx-importstudip-externconfig').html('Error: '+errorThrown);
            }
        });
    },

    /**
     * Which Stud.IP external page module name must be set
     * according to the selected configuration?
     */
    setModuleName: function() {
        var config = TYPO3.jQuery('#tx-importstudip-externconfig').children('select');
        if (config.children('option:selected').length > 0) {
            var module = config.children('option:selected').data('module');
        } else {
            var module = config.children('option').first().data('module');
        }
        TYPO3.jQuery('#tx-importstudip-module').children('input').val(module);
    },

    /**
     * Search for persons in Stud.IP.
     * @returns {boolean}
     */
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
                if (TYPO3.jQuery('#tx-importstudip-personsearch-result select').length > 0) {
                    Tx_ImportStudip.enableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.enableInput('tx-importstudip-externconfig');
                    Tx_ImportStudip.getPersonInstitutes();
                } else {
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-externconfig');
                }
            },
            error: function(data, textStatus, errorThrown) {
                alert('Error: '+errorThrown);
            }
        });
        return false;
    },

    /**
     * Get the institutes the selected person is assigned to.
     * @returns {boolean}
     */
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
                username: user,
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

    /**
     * Search for courses in Stud.IP.
     * @returns {boolean}
     */
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
                if (TYPO3.jQuery('#tx-importstudip-coursesearch-result select').length > 0) {
                    Tx_ImportStudip.enableInput('tx-importstudip-choose-course-institute');
                    Tx_ImportStudip.enableInput('tx-importstudip-externconfig');
                    Tx_ImportStudip.getCourseInstitutes();
                } else {
                    Tx_ImportStudip.disableInput('tx-importstudip-choose-user-institute');
                    Tx_ImportStudip.disableInput('tx-importstudip-externconfig');
                }
            },
            error: function(data, textStatus, errorThrown) {
                alert('Error: '+errorThrown);
            }
        });
        return false;
    },

    /**
     * Get the institutes the selected course is assigned to.
     * @returns {boolean}
     */
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

    /**
     * Get the fields of study list.
     */
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

    /**
     * Open all tree ancestors of the selected element.
     * @param id
     */
    openSelectedParents: function(id) {
        var checked = TYPO3.jQuery('#'+id).find('.tx-importstudip-selector:checked');
        // Open parents of checked element.
        if (checked.length > 0) {
            checked.parents('.tx-importstudip-treebranch').
                children('input.tx-importstudip-treeinput').attr('checked', true);
            checked.parents('.tx-importstudip-treebranch').children('img.tx-importstudip-openclose').each(function() {
                Tx_ImportStudip.swapImages(TYPO3.jQuery(this).attr('id'));
            });
        // No checked element, open only root node.
        } else {
            TYPO3.jQuery('#'+id).find('input.tx-importstudip-treeinput').first().attr('checked', true);
        }
    },

    /**
     * Enables the given input field, e.g. showing it and loading data via AJAX.
     * @param id ID of the input field to show
     * @param parameters needed parameters for AJAX call which fills in data
     */
    enableInput: function(id, parameters) {
        TYPO3.jQuery('#'+id).closest(Tx_ImportStudip.getElementName('container')).show();
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

    /**
     * Hide the given input field.
     * @param id ID of the input field to hide.
     */
    disableInput: function(id) {
        TYPO3.jQuery('#'+id).html();
        TYPO3.jQuery('#'+id).closest(Tx_ImportStudip.getElementName('container')).hide();
    },

    /**
     * Shows a "loading..." icon indicating that some AJAX stuff is happening.
     * @param text Text to show along with the animated icon.
     * @returns {string|*}
     */
    getSpinner: function(text) {
        html = '';
        if (text != '') {
            html += text;
        }
        return html;
    },

    /**
     * Groups several given entries together into one container and add some
     * open/close handling.
     *
     * @param elementId ID of the element that serves as group container
     * @param children array of element IDs that shall be added as group entries
     */
    buildEntryGroup: function(elementId, children) {
        // Find given element in DOM tree.
        var element = TYPO3.jQuery('#' + elementId);
        // Append all given children.
        for (var i = 0 ; i < children.length ; i++) {
            element.append(TYPO3.jQuery('#' + children[i]).closest(Tx_ImportStudip.getElementName('container')));
        }
        // Hide element.
        element.hide();
        var container = element.closest(Tx_ImportStudip.getElementName('container'));
        container.addClass('tx-importstudip-entrygroup');
        container.addClass(elementId + '-container');
        container.children(Tx_ImportStudip.getElementName('label')).prepend(
            '<img src="' + TYPO3.jQuery('#tx-importstudip-pagetypes').data('iconpath') +
            'plus.gif" data-toggle-image="' + TYPO3.jQuery('#tx-importstudip-pagetypes').data('iconpath') +
            'minus.gif"/>');
        container.children(Tx_ImportStudip.getElementName('label')).children('img, label').on('click', function() {
            element.toggle();
            var img = container.children(Tx_ImportStudip.getElementName('label')).children('img');
            var src = img.attr('src');
            var other = img.data('toggle-image');
            img.attr('src', other);
            img.data('toggle-image', src);
        });
    },

    swapImages: function(elementId) {
        var element = TYPO3.jQuery('#' + elementId);
        if (element.length > 0) {
            var img1 = element.attr('src');
            var img2 = element.data('swap-img');
            element.data('swap-img', img1);
            element.attr('src', img2);
        }
        return false;
    },

    /**
     * Gets element names according to TYPO3 version.
     * @param type
     * @returns {string}
     */
    getElementName: function(type) {
        var result = '';

        var t3version = (TYPO3.jQuery('div.t3-form-field-container').length > 0) ? 6 : 7;

        switch (type) {
            case 'container':
                result = (t3version == 6) ? '.t3-form-field-container' : '.form-section';
                break;
            case 'label':
                result = (t3version == 6) ? '.t3-form-field-label' : '.form-group';
                break;
            case 'inputname':
                result = (t3version == 6) ? 'name' : 'data-formengine-input-name';
                break;
        }
        return result;
    }

};

TYPO3.jQuery(function () {
    // Open parent tree structure of selected nodes.
    Tx_ImportStudip.openSelectedParents('tx-importstudip-institutes');
    // build group for additional filter options.
    Tx_ImportStudip.buildEntryGroup('tx-importstudip-filters',
        ['tx-importstudip-aggregate', 'tx-importstudip-participating', 'tx-importstudip-coursetypes',
        'tx-importstudip-subjects', 'tx-importstudip-statusgroups', 'tx-importstudip-smallnews',
        'tx-importstudip-preselectinst']);
    // Build group for linking options.
    Tx_ImportStudip.buildEntryGroup('tx-importstudip-linkingoptions',
        ['tx-importstudip-makelink', 'tx-importstudip-linktext', 'tx-importstudip-linkformat']);
    var container = TYPO3.jQuery('#tx-importstudip-linkingoptions');
    container.append(
        TYPO3.jQuery('select[' + Tx_ImportStudip.getElementName('inputname') + '*="persondetailtarget"]').
            closest(Tx_ImportStudip.getElementName('container')));
    container.append(
        TYPO3.jQuery('select[' + Tx_ImportStudip.getElementName('inputname') + '*="coursedetailtarget"]').
            closest(Tx_ImportStudip.getElementName('container')));
    container.append(
        TYPO3.jQuery('select[' + Tx_ImportStudip.getElementName('inputname') + '*="newsdetailtarget"]').
            closest(Tx_ImportStudip.getElementName('container')));
    container.append(
        TYPO3.jQuery('select[' + Tx_ImportStudip.getElementName('inputname') + '*="linktarget"]').
            closest(Tx_ImportStudip.getElementName('container')));
    Tx_ImportStudip.adjustToPageType();
    Tx_ImportStudip.adjustLinkOptions();

    TYPO3.jQuery('input#tx-importstudip-personsearchterm').on('keypress', function(event) {
        if (event.which == 13) {
            event.preventDefault();
            TYPO3.jQuery('button#tx-importstudip-execute-personsearch').click();
        }
    });
    TYPO3.jQuery('input#tx-importstudip-coursesearchterm').on('keypress', function(event) {
        if (event.which == 13) {
            event.preventDefault();
            TYPO3.jQuery('button#tx-importstudip-execute-coursesearch').click();
        }
    });
});
