<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

?>
<div class="sn-wie-log-page order">
    <div class="sn-wie-header">
        <h2><?php echo( __('View order import export logs', SN_WIE_SLUG) ); ?></h2>
        <p><?php echo( __('This section allows you to view and download all CSV files which were imported or exported', SN_WIE_SLUG) ); ?></p>
    </div>
    <div class="sn-wie-box-section">
        <?php wc_get_template( 'navigation.php', $args, '', SN_WIE_TEMPLATE_PATH .'/order/' );?>
        <div class="log-box">
            <div class="content">
                <div class="table-actions">
                    <select id="log_type" name="log_type" class="form-control custom-select left">
                        <option value>Log Type</option>
                        <option value="IMPORT">Import</option>
                        <option value="EXPORT">Export</option>
                    </select>
                    <a href="<?php echo(admin_url('admin-post.php')) ?>?action=sn_wie_delete_order_log" type="button" class="button button-primary btn-export right"><?php echo( __('Delete All', SN_WIE_SLUG) ); ?></a>
                </div>
                <table class="log-table">
                    <thead>
                        <tr>
                            <th class="name"><?php echo( __('File Name', SN_WIE_SLUG) ); ?></th>
                            <th class="name"><?php echo( __('Type', SN_WIE_SLUG) ); ?></th>
                            <th class="time"><?php echo( __('Date & Time', SN_WIE_SLUG) ); ?></th>
                            <th class="action"><?php echo( __('Action', SN_WIE_SLUG) ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(is_array($file_list) && count($file_list) > 0) {
                                foreach($file_list as $time => $file) {
                                    $extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
                                    $file_detail = explode( '_', $file['name'] );
                                    ?>
                                    <tr class="<?php echo( strtolower( $file_detail[0] ) ); ?>">
                                        <td class="name">
                                            <?php
                                            if(strtolower($file_detail[0]) == 'import') {
                                                echo('<i class="upload-icon">⬆</i>');
                                            }
                                            elseif(strtolower($file_detail[0]) == 'export') {
                                                echo('<i class="download-icon">⬇</i>');
                                            }
                                            echo($file['name']);
                                            ?>
                                        </td>
                                        <td class="type">
                                            <?php
                                            if(strtolower($file_detail[1]) == 'manual') {
                                                echo( __('Manual', SN_WIE_SLUG) );
                                            }
                                            elseif(strtolower($file_detail[1]) == 'schedule') {
                                                echo( __('Schedule', SN_WIE_SLUG) );
                                            }
                                            ?>
                                        </td>
                                        <td class="time"><?php echo(get_date_from_gmt(date('Y-m-d H:i:s', $time), get_option('date_format').' '.get_option('time_format'))) ?></td>
                                        <td class="action"><a href="<?php echo($file['url']) ?>" target="_blank"><?php echo( __('Download', SN_WIE_SLUG) ); ?></a></td>
                                    </tr>
                                    <?php
                                }
                            }
                            else {
                                ?>
                                <tr class="no-data">
                                    <td colspan="4" align="center">
                                        <?php echo( __('No Log Available', SN_WIE_SLUG) ); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
