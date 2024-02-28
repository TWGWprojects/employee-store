jQuery(document).ready(function()
{
    var timer;
    var seconds = 0, minutes = 0, hours = 0;
    var $export_box = jQuery('.sn-wie-export-page.product-review .export-box');
    var $import_box = jQuery('.sn-wie-import-page.product-review .import-box');
    var $mapping_box = jQuery('.sn-wie-mapping-page.product-review .mapping-box');
    var $schedule_box = jQuery('.sn-wie-schedule-page.product-review .schedule-box');
    var $schedule_profile_box = jQuery('.sn-wie-schedule-profile-page.product-review .schedule-profile-box');
    var $mapping_profile_box = jQuery('.sn-wie-mapping-profile-page.product-review .mapping-profile-box');
    var $log_box = jQuery('.sn-wie-log-page.product-review .log-box');

    // Apply Custom Select
    $export_box.find('.custom-select').select2({'minimumResultsForSearch': -1});
    $import_box.find('.custom-select').select2({'minimumResultsForSearch': -1});
    $mapping_box.find('.custom-select').select2();
    $schedule_box.find('.custom-select').select2({'minimumResultsForSearch': -1});
    //--------------------

    // Validate Forms
    $export_box.find('.export-form').validate();
    $import_box.find('.import-form').validate();
    $schedule_box.find('.schedule-form').validate();
    //---------------

    // Form Submit Event
    jQuery('.btn-submit').click(function() {
        jQuery('form#'+jQuery(this).data('form')).submit();
    });
    //------------------

    // Toggle Attribute / Meta row when mapping selected
    $export_box.find('#export_columns').change(function() {
        if(jQuery(this).val() == 'all') {
            $export_box.find('.attribute-row').show();
            $export_box.find('.meta-row').show();
        }
        else {
            $export_box.find('.attribute-row').hide();
            $export_box.find('.meta-row').hide();
        }
    });
    //--------------------------------------------------

    // Change Import Type
    $import_box.find('#upload_type').change(function() {
        if(jQuery(this).val() == 'file_upload') {
            jQuery(this).closest('form').find('.upload-file').show();
            jQuery(this).closest('form').find('.import-url').hide();
        }
        else if(jQuery(this).val() == 'from_url') {
            jQuery(this).closest('form').find('.upload-file').hide();
            jQuery(this).closest('form').find('.import-url').show();
        }
    });
    //-------------------

    // Select Meta field
    $mapping_box.on('change', '.data-source', function() {
        if(jQuery(this).val() == 'Meta') {
            jQuery(this).closest('td').addClass('two-column').append('<input type="text" name="meta_key[]" class="meta-key" placeholder="Meta Key" />');
        }
        else {
            jQuery(this).closest('td').removeClass('two-column').find('.meta-key').remove();
        }
    });
    //------------------

    // Add columns for mapping
    $mapping_box.find('.btn-add-column').click(function() {

        var $tr = jQuery('.sn-wie-mapping-page .mapping-box .mapping-table tbody tr:first-child').clone();
        $tr.find('input').val('');
        $tr.find('select').val('');
        $tr.find('.select2').remove();
        $tr.find('.custom-select').select2();
        jQuery('.sn-wie-mapping-page .mapping-box .mapping-table tbody').append($tr);
    });
    //------------------------

    // Delete column from mapping
    $mapping_box.on('click', '.btn-delete-column', function() {

        if(jQuery(this).closest('tbody').find('tr').length > 1) {
            jQuery(this).closest('tr').remove();
        }
    });
    //---------------------------

    // Change Schedule Type
    $schedule_box.find('#schedule_type').change(function() {
        if(jQuery(this).val() == 'export') {
            jQuery(this).closest('form').find('.export-row').show();
            jQuery(this).closest('form').find('.import-row').hide();
        }
        else if(jQuery(this).val() == 'import') {
            jQuery(this).closest('form').find('.import-row').show();
            jQuery(this).closest('form').find('.export-row').hide();
        }
    });
    //---------------------

    // Show Email when email report link selected
    $schedule_box.find('#email_report_detail').change(function() {
        if( jQuery(this).is(':checked') ) {
            jQuery(this).closest('form').find('.email-row').show();
        }
        else {
            jQuery(this).closest('form').find('.email-row').hide();
        }
    });
    //-------------------------------------------

    // Toggle Attribute / Meta row when mapping selected
    $schedule_box.find('#export_columns').change(function() {
        if(jQuery(this).val() == 'all') {
            $schedule_box.find('.attribute-row').show();
            $schedule_box.find('.meta-row').show();
        }
        else {
            $schedule_box.find('.attribute-row').hide();
            $schedule_box.find('.meta-row').hide();
        }
    });
    //--------------------------------------------------

    // Filter Schedule Profile
    $schedule_profile_box.find('.table-actions #schedule_type').change(function() {
        var type = jQuery(this).val().toLowerCase();

        if( type != '' ) {
            $schedule_profile_box.find('.schedule-table tbody tr:not(.no-data)').hide();
            $schedule_profile_box.find('.schedule-table tbody tr.'+type).show();
        }
        else {
            $schedule_profile_box.find('.schedule-table tbody tr').show();
        }
    });
    //------------------------

    // Delete Mapping Profile Confirmation
    $mapping_profile_box.find('.mapping-table .action .delete').click(function() {
        var ans = confirm('Are you sure you want to delete this profile?');
        if( ans != true ) {
            return ( false );
        }
    });
    //------------------------------------

    // Delete Schedule Profile Confirmation
    $schedule_profile_box.find('.schedule-table .action .delete').click(function() {
        var ans = confirm('Are you sure you want to delete this profile?');
        if( ans != true ) {
            return ( false );
        }
    });
    //-------------------------------------

    // Filter Log Type
    $log_box.find('.table-actions #log_type').change(function() {
        var type = jQuery(this).val().toLowerCase();

        if( type != '' ) {
            $log_box.find('.log-table tbody tr:not(.no-data)').hide();
            $log_box.find('.log-table tbody tr.'+type).show();
        }
        else {
            $log_box.find('.log-table tbody tr').show();
        }
    });
    //----------------

    // Export Product CSV File
    $export_box.find('.btn-export').click(function() {

        var $this = jQuery(this);

        seconds = 0;
        minutes = 0;
        hours = 0;

        $export_box.find('.export-form.product-review').ajaxForm({
            beforeSend: function() {
                update_timer();
                $this.text('Exporting...').attr('disabled', true);
                $export_box.find('.export-modal').show();
                $export_box.find('.export-modal .progress-bar-section').show();
                $export_box.find('.export-modal .export-status-section').hide();
                $export_box.find('.export-modal .modal-actions .btn-pause-resume').show();
                $export_box.find('.export-modal .modal-actions .btn-close').hide();
                jQuery('title').data('text', jQuery('title').text());
            },
            success: function(Response) {
                if(Response['status'] == true) {
                    export_csv_file(Response['data']['export_file_name'], Response['data']['total_requests'], 1, Response['data']['export_columns'], Response['data']['field_separator'], Response['data']['download_filename'], []);
                }
                else {
                    alert(Response['message']);
                }
            },
            error: function() {
                stop_timer();
                $export_box.find('.footer .btn-export').text('Export').removeAttr('disabled');
                $export_box.find('.export-modal .progress-bar-section').hide();
                $export_box.find('.export-modal .export-status-section').show();
                $export_box.find('.export-modal .modal-actions .btn-pause-resume').hide();
                $export_box.find('.export-modal .modal-actions .btn-close').show();
            }
        });
        $export_box.find('.export-form.product-review').submit();
    });
    $export_box.find('.export-modal .modal-actions .btn-close').click(function() {
        $export_box.find('.export-modal').hide();
    });
    //------------------------

    // Import Product Category CSV File
    $import_box.find('.btn-import').click(function() {

        var $this = jQuery(this);

        seconds = 0;
        minutes = 0;
        hours = 0;

        $import_box.find('.import-form.product-review').ajaxForm({
            beforeSend: function() {
                update_timer();
                $this.text('Importing...').attr('disabled', true);
                $import_box.find('.import-modal').show();
                $import_box.find('.import-modal .progress-bar-section').show();
                $import_box.find('.import-modal .import-status-section').hide();
                $import_box.find('.import-modal .import-status-section table tbody').empty();
                $import_box.find('.import-modal .modal-actions .btn-pause-resume').show();
                $import_box.find('.import-modal .modal-actions .btn-close').hide();
                jQuery('title').data('text', jQuery('title').text());
            },
            success: function(Response) {
                if(Response['status'] == true) {
                    import_csv_file(Response['data']['file_name'], Response['data']['total_requests'], 0, Response['data']['import_columns'], Response['data']['review_search_type'], Response['data']['review_operation'], Response['data']['field_separator']);
                }
                else {
                    alert(Response['message']);
                    $import_box.find('.import-form').show();
                    $import_box.find('.progress-bar-section .progress-bar .progress-fill').width(0);
                    $import_box.find('.footer').show().find('.btn-import').text('Import').removeAttr('disabled').show();
                    $import_box.find('.progress-bar-section').hide();
                    $import_box.find('.import-modal .import-status-section').show();
                    $import_box.find('.import-modal .modal-actions .btn-pause-resume').hide();
                    $import_box.find('.import-modal .modal-actions .btn-close').show();
                    $import_box.find('.import-modal .modal-actions .btn-view-product-reviews').show();
                }
            },
            error: function() {
                alert('Some error occurred on server, please try again!');
                $import_box.find('.footer .btn-import').text('Import').removeAttr('disabled');
                $import_box.find('.import-modal .progress-bar-section').hide();
                $import_box.find('.import-modal .import-status-section').show();
                $import_box.find('.import-modal .modal-actions .btn-pause-resume').hide();
                $import_box.find('.import-modal .modal-actions .btn-close').show();
                $import_box.find('.import-modal .modal-actions .btn-view-product-reviews').show();
            },
            complete: function(xhr) {
            }
        });
        $import_box.find('.import-form.product-review').submit();
    });
    $import_box.find('.import-modal .modal-actions .btn-close').click(function() {
        $import_box.find('.import-modal').hide();
    });
    //------------------------

    function export_csv_file(file_name, total_requests, request_number, export_columns, field_separator, download_filename, csv_columns) {

        jQuery.ajax({
            url: jQuery('.export-form.product-review').attr('action'),
            data: {'action': 'sn_wie_export_product_review_csv_file', 'file_name': file_name, 'total_requests': total_requests, 'request_number': request_number, 'export_columns': export_columns, 'field_separator': field_separator, 'download_filename': download_filename, 'csv_columns': csv_columns},
            type: 'POST',
            success: function(Response) {
                var export_percentage = Math.round((Response['data']['request_number'] * 100) / Response['data']['total_requests']);
                export_percentage = parseInt(export_percentage>100?100:export_percentage);
                jQuery('title').text(export_percentage + '% - ' + jQuery('title').data('text'));
                $export_box.find('.progress-bar-section .import-export-stats .percentage').text(export_percentage+'%');
                $export_box.find('.progress-bar-section .progress-bar .progress-fill').width(export_percentage+'%');
                if(Response['data']['request_number'] <= Response['data']['total_requests']) {
                    update_export_status(Response['data']);
                    export_csv_file(Response['data']['file_name'], Response['data']['total_requests'], Response['data']['request_number'], export_columns, field_separator, download_filename, Response['data']['csv_columns']);
                }
                else {
                    update_export_status(Response['data']);
                    if(Response['status'] == true) {
                        setTimeout(function() {
                            stop_timer();
                            jQuery('title').text(jQuery('title').data('text'));
                            $export_box.find('.progress-bar-section .progress-bar .progress-fill').width(0);
                            $export_box.find('.footer .btn-export').text('Export').removeAttr('disabled');
                            $export_box.find('.export-modal .progress-bar-section').hide();
                            $export_box.find('.export-modal .export-status-section').show();
                            $export_box.find('.export-modal .modal-actions .btn-pause-resume').hide();
                            $export_box.find('.export-modal .modal-actions .btn-close').show();
                            jQuery('#ifrm_download').remove();
                            jQuery('body').append('<iframe id="ifrm_download" src="'+sn_wie_admin_url+'admin-post.php?action=sn_wie_download_product_review_export_file&file_name='+file_name+'&download_filename='+download_filename+'" style="display:none;"></iframe>');

                        }, 500);
                    }
                    else {
                        alert(Response['message']);
                    }
                }
            },
            error: function() {
                stop_timer();
                $export_box.find('.export-modal .progress-bar-section').hide();
                $export_box.find('.export-modal .export-status-section').show();
                $export_box.find('.export-modal .modal-actions .btn-pause-resume').hide();
                $export_box.find('.export-modal .modal-actions .btn-close').show();
            }
        });
    }

    function import_csv_file(file_name, total_requests, request_number, import_columns, review_search_type, review_operation, field_separator) {

        jQuery.ajax({
            url: jQuery('.import-form').attr('action'),
            data: {'action': 'sn_wie_import_product_review_csv_file', 'file_name': file_name, 'total_requests': total_requests, 'request_number': request_number, 'import_columns': import_columns, 'review_search_type': review_search_type, 'review_operation': review_operation, 'field_separator': field_separator},
            type: 'POST',
            success: function(Response) {
                var import_percentage = Math.round((Response['data']['request_number'] * 100) / Response['data']['total_requests']);
                import_percentage = parseInt(import_percentage>100?100:import_percentage);
                jQuery('title').text(import_percentage + '% - ' + jQuery('title').data('text'));
                $import_box.find('.progress-bar-section .import-export-stats .percentage').text(import_percentage+'%');
                $import_box.find('.progress-bar-section .progress-bar .progress-fill').width(import_percentage+'%');
                if(Response['data']['request_number'] < Response['data']['total_requests']) {
                    update_import_status(Response['data']);
                    import_csv_file(Response['data']['file_name'], Response['data']['total_requests'],Response['data']['request_number'], import_columns, review_search_type, review_operation, field_separator);
                }
                else {
                    setTimeout(function() {
                        update_import_status(Response['data']);
                        stop_timer();
                        jQuery('title').text(jQuery('title').data('text'));
                        $import_box.find('.progress-bar-section .progress-bar .progress-fill').width(0);
                        $import_box.find('.footer .btn-import').text('Import').removeAttr('disabled');
                        $import_box.find('.import-modal .progress-bar-section').hide();
                        $import_box.find('.import-modal .import-status-section').show();
                        $import_box.find('.import-modal .modal-actions .btn-pause-resume').hide();
                        $import_box.find('.import-modal .modal-actions .btn-close').show();
                        $import_box.find('.import-modal .modal-actions .btn-view-product-reviews').show();
                    }, 500);
                }
            },
            error: function() {
                stop_timer();
                $import_box.find('.import-modal .progress-bar-section').hide();
                $import_box.find('.import-modal .import-status-section').show();
                $import_box.find('.import-modal .modal-actions .btn-pause-resume').hide();
                $import_box.find('.import-modal .modal-actions .btn-close').show();
                $import_box.find('.import-modal .modal-actions .btn-view-product-reviews').show();
            }
        });
    }

    function update_export_status(data) {

        var percentage = $export_box.find('.progress-bar-section .import-export-stats .percentage').text();
        var time = jQuery('.progress-bar-section .import-export-stats .timer').text();
        var file_name = data['file_name'];
        var export_count = parseInt($export_box.find('.export-status-section .status-box .stat.export-count .value').text());

        export_count += data['product_review_export_count'];

        $export_box.find('.export-status-section .status-box .stat.percentage .value').text(percentage);
        $export_box.find('.export-status-section .status-box .stat.time .value').text(time);
        $export_box.find('.export-status-section .status-box .stat.export-count .value').text(export_count);
        $export_box.find('.export-status-section .status-box .stat.file-name .value').text(file_name);
    }

    function update_import_status(data) {
        var response = '';
        var percentage = $import_box.find('.progress-bar-section .import-export-stats .percentage').text();
        var time = jQuery('.progress-bar-section .import-export-stats .timer').text();

        var update_count = parseInt($import_box.find('.import-status-section .status-box .stat.update-count .value').text());
        var insert_count = parseInt($import_box.find('.import-status-section .status-box .stat.insert-count .value').text());
        var skip_count = parseInt($import_box.find('.import-status-section .status-box .stat.skip-count .value').text());
        var fail_count = parseInt($import_box.find('.import-status-section .status-box .stat.fail-count .value').text());

        update_count += data['product_review_update_count'];
        insert_count += data['product_review_insert_count'];
        skip_count += data['product_review_skip_count'];
        fail_count += data['product_review_fail_count'];

        $import_box.find('.import-status-section .status-box .stat.percentage .value').text(percentage);
        $import_box.find('.import-status-section .status-box .stat.time .value').text(time);
        $import_box.find('.import-status-section .status-box .stat.update-count .value').text(update_count);
        $import_box.find('.import-status-section .status-box .stat.insert-count .value').text(insert_count);
        $import_box.find('.import-status-section .status-box .stat.skip-count .value').text(skip_count);
        $import_box.find('.import-status-section .status-box .stat.fail-count .value').text(fail_count);

        for(var p=0;p<data['product_review_status'].length;p++) {
            response += '<tr><td class="id">'+data['product_review_status'][p]['id']+'</td><td class="message">'+data['product_review_status'][p]['message']+'</td><td class="status">'+(data['product_review_status'][p]['status']==true?'<span class="success">Success</span>':'<span class="error">Error</span>')+'</td></tr>';
        }
        $import_box.find('.import-status-section .response-table tbody').append(response).show();
    }

    function update_timer() {
        timer = setTimeout(function() {
            seconds++;
            if (seconds >= 60) {
                seconds = 0;
                minutes++;
                if (minutes >= 60) {
                    minutes = 0;
                    hours++;
                }
            }
            var time = (hours ? (hours > 9 ? hours : "0" + hours + ":") : "") + (minutes ? (minutes > 9 ? minutes : "0" + minutes) : "00") + ":" + (seconds > 9 ? seconds : "0" + seconds);
            jQuery('.progress-bar-section .import-export-stats .timer').text(time);
            update_timer();
        }, 1000);
    }

    function stop_timer() {
        clearTimeout(timer);
        jQuery('.progress-bar-section .import-export-stats .percentage').text('0%');
        jQuery('.progress-bar-section .import-export-stats .timer').text('00:00');
    }
});
