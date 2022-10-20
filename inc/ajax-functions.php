<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Check User Permission
 * Hooked via action admin_menu
 * @since   1.0.0
 */
function lfb_user_permission_check(){

    $user = get_userdata(get_current_user_id());

    $user = wp_get_current_user();
    $allowed_roles = array('editor', 'administrator', 'lfb_role');
    return array_intersect($allowed_roles, $user->roles);

}

/**
 * File Upload Dir
 * @since   1.0.0
 */
function lfb_upload_dir($dirs){

    $dirs['subdir'] = '/lfb_uploads';
    $dirs['path'] = $dirs['basedir'] . '/lfb_uploads';
    $dirs['url'] = $dirs['baseurl'] . '/lfb_uploads';

    return $dirs;

}

/**
 * File Upload Process
 * Hooked via action wp_ajax_fileupload
 * @since   1.0.0
 */
function lfb_fileupload(){

    add_filter('upload_dir', 'lfb_upload_dir');

    $fileErrors = array(
        0 => __("There is no error, the file uploaded with success", "sejoli-lead-form"),
        1 => __("The uploaded file exceeds the upload_max_files in server settings", "sejoli-lead-form"),
        2 => __("The uploaded file exceeds the MAX_FILE_SIZE from html form", "sejoli-lead-form"),
        3 => __("The uploaded file uploaded only partially", "sejoli-lead-form"),
        4 => __("No file was uploaded", "sejoli-lead-form"),
        6 => __("Missing a temporary folder", "sejoli-lead-form"),
        7 => __("Failed to write file to disk", "sejoli-lead-form"),
        8 => __("A PHP extension stoped file to upload", "sejoli-lead-form")
    );

    $file_data = isset($_FILES) ? $_FILES : array();
    $overrides = array('test_form' => false);
    $response = array();

    foreach ($file_data as $key => $file) {
        $uploaded_file = wp_handle_upload($file, $overrides);

        if ($uploaded_file && !isset($uploaded_file['error'])) {
            $response[$key]['response'] = __('SUCCESS', 'sejoli-lead-form');
            $response[$key]['filename'] = basename($uploaded_file['url']);
            $response[$key]['url'] = $uploaded_file['url'];
            $response[$key]['type'] = $uploaded_file['type'];
        } else {
            $response[$key]['response'] = esc_html__("ERROR", 'sejoli-lead-form');
            $response[$key]['error'] = $uploaded_file['error'];
        }
    }

    $parse = http_build_query($response);

    remove_filter('upload_dir', 'lfb_upload_dir');

    die();

}
add_action('wp_ajax_fileupload', 'lfb_fileupload');
add_action('wp_ajax_nopriv_fileupload', 'lfb_fileupload');

/**
 * Save Lead collecting method
 * Hooked via action wp_ajax_SaveLeadSettings
 * @since   1.0.0
 */
function lfb_save_lead_settings(){

    $nonce = $_REQUEST['lrv_nonce_verify'];
    // Get all the user roles as an array.
    if (isset($_POST['action-lead-setting'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'lrv-nonce')) {

        $data_recieve_method = intval($_POST['data-recieve-method']);
        $this_form_id = intval($_POST['action-lead-setting']);
        global $wpdb;
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set storeType='" . $data_recieve_method . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveLeadSettings', 'lfb_save_lead_settings');

/**
 * Save Email Settings
 * Hooked via action wp_ajax_SaveEmailSettings
 * @since   1.0.0
 */
function lfb_save_email_settings(){

    $nonce = $_REQUEST['aes_nonce'];
    // Get all the user roles as an array.
    if (isset($_POST['email_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'aes-nonce')) {

        global $wpdb;
        $email_setting = array();
        $this_form_id = intval($_POST['email_setting']['form-id']);
        $email_setting['email_setting'] = isset($_POST['email_setting']) ? $_POST['email_setting'] : '';
        $serialize = maybe_serialize($email_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set mail_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }
        die();
    }

}
add_action('wp_ajax_SaveEmailSettings', 'lfb_save_email_settings');

/**
 * Save User Email Settings
 * Hooked via action wp_ajax_SaveUserEmailSettings
 * @since   1.0.0
 */
function lfb_SaveUserEmailSettings(){

    unset($_POST['action']);
    $mailArr = array();

    $nonce = $_REQUEST['ues_nonce'];
    // Get all the user roles as an array.
    if (isset($_POST['user_email_setting'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'ues-nonce')) {

        $mailArr['user_email_setting'] = lfb_emailsettings_sanitize($_POST['user_email_setting']);

        $email_setting = maybe_serialize($mailArr);
        $this_form_id = intval($_POST['user_email_setting']['form-id']);
        global $wpdb;
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set usermail_setting='" . $email_setting . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        error_log(print_r($email_setting, true));
        if ($update_leads) {
            echo esc_html("updated");
        }
    }

    die();

}
add_action('wp_ajax_SaveUserEmailSettings', 'lfb_SaveUserEmailSettings');

/**
 * Save Affiliate Email Settings
 * Hooked via action wp_ajax_SaveAffiliateEmailSettings
 * @since   1.0.0
 */
function lfb_SaveAffiliateEmailSettings(){

    unset($_POST['action']);
    $mailArr = array();

    $nonce = $_REQUEST['affes_nonce'];
    // Get all the user roles as an array.
    if (isset($_POST['affiliate_email_setting'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'affes-nonce')) {

        $mailArr['affiliate_email_setting'] = lfb_emailsettings_sanitize($_POST['affiliate_email_setting']);

        $email_setting = maybe_serialize($mailArr);
        $this_form_id = intval($_POST['affiliate_email_setting']['form-id']);
        global $wpdb;
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set affiliatemail_setting='" . $email_setting . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        if ($update_leads) {
            echo esc_html("updated");
        }
    }

    die();

}
add_action('wp_ajax_SaveAffiliateEmailSettings', 'lfb_SaveAffiliateEmailSettings');

/**
 * Save Admin WhatsApp Settings
 * Hooked via action wp_ajax_SaveWaSettings
 * @since   1.0.0
 */
function lfb_save_wa_settings(){

    $nonce = $_REQUEST['awas_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['whatsapp_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'awas-nonce')) {

        global $wpdb;
        $whatsapp_setting = array();
        $this_form_id = intval($_POST['whatsapp_setting']['form-id']);
        $whatsapp_setting['whatsapp_setting'] = isset($_POST['whatsapp_setting']) ? $_POST['whatsapp_setting'] : '';
        $serialize = maybe_serialize($whatsapp_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set wa_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveWaSettings', 'lfb_save_wa_settings');

/**
 * Save User WhatsApp Settings
 * Hooked via action wp_ajax_SaveUserWaSettings
 * @since   1.0.0
 */
function lfb_save_user_wa_settings(){

    $nonce = $_REQUEST['uwas_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['user_wa_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'uwas-nonce')) {

        global $wpdb;

        $user_wa_setting = array();
        $this_form_id = intval($_POST['user_wa_setting']['form-id']);
        $user_wa_setting['user_wa_setting'] = isset($_POST['user_wa_setting']) ? $_POST['user_wa_setting'] : '';
        $serialize = maybe_serialize($user_wa_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set userwa_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveUserWaSettings', 'lfb_save_user_wa_settings');

/**
 * Save Affiliate WhatsApp Settings
 * Hooked via action wp_ajax_SaveAffiliateWaSettings
 * @since   1.0.0
 */
function lfb_save_affiliate_wa_settings(){

    $nonce = $_REQUEST['affwas_nonce'];
    // Get all the user roles as an array.
    if (isset($_POST['affiliate_wa_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'affwas-nonce')) {

        global $wpdb;
        $affiliate_wa_setting = array();
        $this_form_id = intval($_POST['affiliate_wa_setting']['form-id']);
        $affiliate_wa_setting['affiliate_wa_setting'] = isset($_POST['affiliate_wa_setting']) ? $_POST['affiliate_wa_setting'] : '';
        $serialize = maybe_serialize($affiliate_wa_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set affiliatewa_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveAffiliateWaSettings', 'lfb_save_affiliate_wa_settings');

/**
 * Save Admin SMS Settings
 * Hooked via action wp_ajax_SaveSMSSettings
 * @since   1.0.0
 */
function lfb_save_sms_settings(){

    $nonce = $_REQUEST['asmss_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['sms_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'asmss-nonce')) {

        global $wpdb;
        $sms_setting = array();
        $this_form_id = intval($_POST['sms_setting']['form-id']);
        $sms_setting['sms_setting'] = isset($_POST['sms_setting']) ? $_POST['sms_setting'] : '';
        $serialize = maybe_serialize($sms_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set sms_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveSMSSettings', 'lfb_save_sms_settings');

/**
 * Save User SMS Settings
 * Hooked via action wp_ajax_SaveUserSMSSettings
 * @since   1.0.0
 */
function lfb_save_user_sms_settings(){

    $nonce = $_REQUEST['usmss_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['user_sms_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'usmss-nonce')) {

        global $wpdb;
        $user_sms_setting = array();
        $this_form_id = intval($_POST['user_sms_setting']['form-id']);
        $user_sms_setting['user_sms_setting'] = isset($_POST['user_sms_setting']) ? $_POST['user_sms_setting'] : '';
        $serialize = maybe_serialize($user_sms_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set usersms_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveUserSMSSettings', 'lfb_save_user_sms_settings');

/**
 * Save Affiliate SMS Settings
 * Hooked via action wp_ajax_SaveAffiliateSMSSettings
 * @since   1.0.0
 */
function lfb_save_affiliate_sms_settings(){

    $nonce = $_REQUEST['affsmss_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['affiliate_sms_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'affsmss-nonce')) {

        global $wpdb;
        $affiliate_sms_setting = array();
        $this_form_id = intval($_POST['affiliate_sms_setting']['form-id']);
        $affiliate_sms_setting['affiliate_sms_setting'] = isset($_POST['affiliate_sms_setting']) ? $_POST['affiliate_sms_setting'] : '';
        $serialize = maybe_serialize($affiliate_sms_setting);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set affiliatesms_setting='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveAffiliateSMSSettings', 'lfb_save_affiliate_sms_settings');

/**
 * Save Captcha Keys
 * Hooked via action wp_ajax_SaveCaptchaSettings
 * @since   1.0.0
 */
function lfb_save_captcha_settings(){

    $nonce = $_POST['captcha_nonce'];

    if (isset($_POST['captcha-keys'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'captcha-nonce')) {

        $captcha_setting_sitekey = sanitize_text_field($_POST['captcha-setting-sitekey']);
        $captcha_setting_secret = sanitize_text_field($_POST['captcha-setting-secret']);

        if (get_option('captcha-setting-sitekey') !== false) {
            update_option('captcha-setting-sitekey', $captcha_setting_sitekey);
            update_option('captcha-setting-secret', $captcha_setting_secret);
        } else {
            add_option('captcha-setting-sitekey', $captcha_setting_sitekey);
            add_option('captcha-setting-secret', $captcha_setting_secret);
        }
    }

    die();

}
add_action('wp_ajax_SaveCaptchaSettings', 'lfb_save_captcha_settings');

/**
 * Save Autoresponder Settings
 * Hooked via action wp_ajax_SaveAutoresponderSettings
 * @since   1.0.0
 */
function lfb_save_autoresponder_settings(){

    $nonce = $_REQUEST['aaress_nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['autoresponder_setting']['form-id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'aaress-nonce')) {

        global $wpdb;
        $autoresponder_setting = isset($_POST['autoresponder_setting']['code']) ? $_POST['autoresponder_setting']['code'] : '';
        $this_form_id = intval($_POST['autoresponder_setting']['form-id']);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set autoresponder_setting='" . $autoresponder_setting . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveAutoresponderSettings', 'lfb_save_autoresponder_settings');

/**
 * Delete Leads from Backend
 * Hooked via action wp_ajax_delete_leads_backend
 * @since   1.0.0
 */
function lfb_delete_leads_backend(){

    $nonce = $_REQUEST['_lfbnonce'];
    // Get all the user roles as an array.

    $check = false;
    if (isset($_POST['lead_id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'lfb-nonce-rm')) {
        $check = true;

        $this_lead_id = intval($_POST['lead_id']);
        global $wpdb;
        $table_name = LFB_FORM_DATA_TBL;

        $update_query = $wpdb->prepare(" DELETE FROM $table_name WHERE id = %d ", $this_lead_id);

        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_delete_form($update_query);
        echo esc_html($update_leads);
    }

    echo $check;

}
add_action('wp_ajax_delete_leads_backend', 'lfb_delete_leads_backend');

/**
 * Save captcha status for form ON/OFF
 * Hooked via action wp_ajax_SaveCaptchaOption
 * @since   1.0.0
 */
function lfb_save_captcha_option(){

    $nonce = $_POST['captcha_nonce'];
    if (isset($_POST['captcha_on_off_form_id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'captcha-nonce')) {

        $captcha_option = sanitize_text_field($_POST['captcha-on-off-setting']);
        $this_form_id = intval($_POST['captcha_on_off_form_id']);
        global $wpdb;
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set captcha_status='" . $captcha_option . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);
        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }
    }

    die();

}
add_action('wp_ajax_SaveCaptchaOption', 'lfb_save_captcha_option');

/**
 * Save Thankyou Settings
 * Hooked via action wp_ajax_SaveThankyouSettings
 * @since   1.0.0
 */
function lfb_save_thankyou_settings(){

    $nonce = $_REQUEST['thankyou-nonce'];

    // Get all the user roles as an array.
    if (isset($_POST['thankyou_settings']['form-id']) && wp_verify_nonce($nonce, 'thankyou-nonce')) {

        global $wpdb;
        $thankyou_settings = array();
        $this_form_id = intval($_POST['thankyou_settings']['form-id']);
        $thankyou_settings['thankyou_settings'] = isset($_POST['thankyou_settings']) ? $_POST['thankyou_settings'] : '';
        $serialize = maybe_serialize($thankyou_settings);
        $table_name = LFB_FORM_FIELD_TBL;
        $update_query = "update " . LFB_FORM_FIELD_TBL . " set multiData='" . $serialize . "' where id='" . $this_form_id . "'";
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $update_leads = $th_save_db->lfb_update_form_data($update_query);

        error_log(print_r($nonce, true));

        if ($update_leads) {
            esc_html_e('updated', 'sejoli-lead-form');
        }

        die();

    }

}
add_action('wp_ajax_SaveThankyouSettings', 'lfb_save_thankyou_settings');

/**
 * Show all Leads column on Lead Page Based on form selection
 * Hooked via action wp_ajax_ShowAllLeadThisForm
 * @since   1.0.0
 */
function lfb_ShowAllLeadThisForm(){

    if ((isset($_POST['form_id']) && ($_POST['form_id'] != '')) || (isset($_GET['form_id']) && ($_GET['form_id'] != ''))) {
        global $wpdb, $wp;
        $table_name = LFB_FORM_DATA_TBL;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $nonce = wp_create_nonce('lfb-nonce-rm');
        $showLeadsObj = new LFB_Show_Leads();
        $start = 0;
        $limit = 10;
        $detail_view  = '';
        $slectleads = false;

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $start = ($id - 1) * $limit;
            $form_id = intval($_GET['form_id']);
            $sn_counter = $start;
        } else {
            $id = 1;
            $form_id = intval($_POST['form_id']);
            $sn_counter = 0;
        }
        if (isset($_GET['detailview'])) {
            $detail_view = sanitize_text_field($_GET['detailview']);
        }

        if (isset($_POST['slectleads'])) {
            $slectleads = sanitize_text_field($_POST['slectleads']);
        }

        $user_ID = get_current_user_id(); 
        if(wp_get_referer() === home_url('member-area/lead-entries/')) {
            $getArray = $th_save_db->lfb_get_affiliate_view_leads_db($form_id, $user_ID, $start);
        } else {
            $getArray = $th_save_db->lfb_get_all_view_leads_db($form_id, $start);
        }
        $posts          = $getArray['posts'];
        $rows           = $getArray['rows'];
        $limit          = $getArray['limit'];
        $fieldData       = $getArray['fieldId'];
        $tableHead  = '';
        $headcount = 1;
        $leadscount = 5;

        foreach ($fieldData as $fieldkey => $fieldvalue) {
            // Html Field removed
            $pos = strpos($fieldkey, 'htmlfield_');
            if ($pos !== false) {
                continue;
            }

            if ($headcount < 6 && $slectleads) {
                $tableHead  .= '<th>' . $fieldvalue . '</th>';
            } elseif (!$slectleads) {

                $tableHead  .= '<th>' . $fieldvalue . '</th>';

                $leadscount =  $headcount;
            }
            $fieldIdNew[] = $fieldkey;
            $headcount++;

            // } else{ break; }
        }

        if (!empty($posts)) {
            $entry_counter = 0;
            $table_body = '';
            $table_head = '';
            $popupTab   = '';

            if ($headcount >= 6 && $leadscount == 5) {
                $table_head .= '<th></th><th> . . . </th><th><input type="button" onclick="show_all_leads(' . intval($id) . ',' . intval($form_id) . ')" value="Show all Columns"></th>';
            }

            foreach ($posts as $results) {
                $table_row = '';
                $sn_counter++;
                $row_size_limit = 0;
                $form_data = $results->form_data;
                $lead_id = $results->id;
                $product_id = $results->product;
                $product    = sejolisa_get_product($product_id);
                $affiliate_id = $results->affiliate;
                $affiliate    = sejolisa_get_user($affiliate_id);
                $form_data = maybe_unserialize($form_data);
                $lead_date = date("jS F Y", strtotime($results->date));
                unset($form_data['hidden_field']);
                unset($form_data['action']);
                unset($form_data['g-recaptcha-response']);
                $entry_counter++;
                $complete_data = '';
                $popup_data_val= '';
                $date_td = '<td><b>'.$lead_date.'</b></td>';

                $returnData = $th_save_db->lfb_lead_form_value($form_data,$fieldIdNew,$fieldData,5);

                $table_row .= "<td>".$product->post_title."</td>";
                $table_row .= $returnData['table_row'];
                if($affiliate_id > 0) {
                    $table_row .= "<td>".$affiliate->display_name."</td>";
                } else {
                    $table_row .= "<td>-</td>";
                }
                $table_row .= "<td>".sejolisa_price_format($product->price)."</td>";
                $table_row .= $date_td;
                $form = $th_save_db->lfb_get_form_data($results->form_id);
                $form_data_result = maybe_unserialize($form[0]->form_data);

                $text_follow = '';
                foreach ($form_data_result as $results) {
                    $default_phonenumber = isset($results['default_phonenumber']) ? $results['default_phonenumber'] : 0;
                    if ( $default_phonenumber !== 0 ) {
                        $field_id = $results['field_id'];
                        $table_row .= '<td><a class="lead-followup-wa" href="https://wa.me/'.$form_data['text_'.$field_id].'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
                        $text_follow = "Follow Up";
                    }
                }

                // $table_row .= '<td></span><a class="lead-followup-wa"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></span></span><a class="lead-remove" onclick="delete_this_lead(' . $lead_id . ',\''.$nonce.'\')"><i class="fa fa-trash" aria-hidden="true" title="Hapus"></i></a></span></td>';
         
                foreach ($form_data as $form_data_key => $form_data_value) {
                    $row_size_limit++;

                    if (($detail_view != 1) && ($row_size_limit == 6)) {
                        $table_row .= '<td> . . . </td><td><a href="#lf-openModal-' . $lead_id . '" value="view">view</a></td>';
                    }
                }
                $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

                $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
                    <div class="lfb-popup-leads"><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
                    </div>
                    </div>';

                $table_body .= '<tbody id="lead-id-' . $lead_id . '">';
                $table_body .= '<tr><td><span class="lead-count"><a href="#lf-openModal-' . $lead_id . '" title="View Detail">#' . $sn_counter . '</a></td>'. $table_row .'</tr>';
            }

            $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat fixed" id="show-leads-table" >
                <thead><tr><th>ID</th><th>Product</th>'.$tableHead.'<th>Affiliate</th><th>Value</th><th>Date</th>'.$table_head.'<th>'.$text_follow.'</th></tr></thead>';

            echo wp_kses($thHead . $table_body . '</tbody></table>' . $popupTab, $showLeadsObj->expanded_alowed_tags());

            $total = ceil($rows / $limit);
            if ($headcount >= 6 && $leadscount == 5) {

                if ($id > 1) {
                    echo "<a href=''  onclick='lead_pagi_view(" . intval($id - 1) . "," . intval($form_id) . ")' class='button'><i class='fa fa-chevron-right'></i></a>";
                }
                if ($id != $total) {
                    echo "<a href='' onclick='lead_pagi_view(" . intval($id + 1) . "," . intval($form_id) . ")' class='button'><i class='fa fa-chevron-left'></i></a>";
                }
                echo "<ul class='page'>";
                for ($i = 1; $i <= $total; $i++) {
                    if ($i == $id) {
                        echo "<li class='lf-current'><a href='#'>" . intval($i) . "</a></li>";
                    } else {
                        echo "<li><a href='' onclick='lead_pagi_view(" . intval($i) . "," . intval($form_id) . ")'>" . intval($i) . "</a></li>";
                    }
                }
                echo '</ul>';
            } else {

                if ($id > 1) {
                    echo "<a href=''  onclick='lead_pagination(" . intval($id - 1) . "," . intval($form_id) . ")' class='button'><i class='fa fa-chevron-right'></i></a>";
                }
                if ($id != $total) {
                    echo "<a href='' onclick='lead_pagination(" . intval($id + 1) . "," . intval($form_id) . ")' class='button'><i class='fa fa-chevron-left'></i></a>";
                }
                echo "<ul class='page'>";
                for ($i = 1; $i <= $total; $i++) {
                    if ($i == $id) {
                        echo "<li class='lf-current'><a href='#'>" . intval($i) . "</a></li>";
                    } else {
                        echo "<li><a href='' onclick='lead_pagination(" . intval($i) . "," . intval($form_id) . ")'>" . intval($i) . "</a></li>";
                    }
                }
                echo '</ul>';
            }
        } else {
            esc_html_e('No leads..!', 'sejoli-lead-form');
        }

        die();
    }

}
add_action('wp_ajax_ShowAllLeadThisForm', 'lfb_ShowAllLeadThisForm');

/**
 * Show Leads Page
 * Hooked via action wp_ajax_ShowALeadPagi
 * @since   1.0.0
 */
function lfb_ShowLeadPagi(){

    if ((isset($_POST['form_id']) && ($_POST['form_id'] != '')) || (isset($_GET['form_id']) && ($_GET['form_id'] != ''))) {
        global $wpdb;
        $table_name = LFB_FORM_DATA_TBL;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $showLeadsObj = new LFB_Show_Leads();
        $nonce = wp_create_nonce('lfb-nonce-rm');
        $start = 0;
        $limit = 10;
        $detail_view = '';

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $start = ($id - 1) * $limit;
            $form_id = intval($_GET['form_id']);
            $sn_counter = $start;
        } else {
            $id = 1;
            $form_id = intval($_POST['form_id']);
            $sn_counter = 0;
        }
        if (isset($_GET['detailview'])) {
            $detail_view = isset($_GET['detailview']);
        }

        $getArray = $th_save_db->lfb_get_all_view_leads_db($form_id, $start);
        $posts          = $getArray['posts'];
        $rows           = $getArray['rows'];
        $limit          = $getArray['limit'];
        $fieldData       = $getArray['fieldId'];
        $tableHead  = '';
        $headcount = 1;

        foreach ($fieldData as $fieldkey => $fieldvalue) {
            if ($headcount < 6) {
                $tableHead  .= '<th>' . $fieldvalue . '</th>';
            }
            $fieldIdNew[] = $fieldkey;
            // } else{ break; }
            $headcount++;
        }
        if (!empty($posts)) {
            $entry_counter = 0;
            $table_body = '';
            $table_head = '';
            $popupTab   = '';

            if ($headcount >= 6) {
                $table_head .= '<th> . . . </th><th><input type="button" onclick="show_all_leads(' . $id . ',' . $form_id . ')" value="Show all Columns"></th>';
            }

            foreach ($posts as $results) {
                $table_row = '';
                $sn_counter++;
                $row_size_limit = 0;
                $form_data = $results->form_data;
                $lead_id = $results->id;
                $product_id = $results->product;
                $product    = sejolisa_get_product($product_id);
                $affiliate_id = $results->affiliate;
                $affiliate    = sejolisa_get_user($affiliate_id);
                $form_data = maybe_unserialize($form_data);
                $lead_date = date("jS F Y", strtotime($results->date));
                unset($form_data['hidden_field']);
                unset($form_data['action']);
                unset($form_data['g-recaptcha-response']);
                $entry_counter++;
                $complete_data = '';
                $popup_data_val= '';
                $date_td = '<td><b>'.$lead_date.'</b></td>';

                $returnData = $th_save_db->lfb_lead_form_value($form_data,$fieldIdNew,$fieldData,5);

                $table_row .= "<td>".$product->post_title."</td>";
                $table_row .= $returnData['table_row'];
                if($affiliate_id > 0) {
                    $table_row .= "<td>".$affiliate->display_name."</td>";
                } else {
                    $table_row .= "<td>-</td>";
                }
                $table_row .= "<td>".sejolisa_price_format($product->price)."</td>";
                $table_row .= $date_td;
                $form = $th_save_db->lfb_get_form_data($results->form_id);
                $form_data_result = maybe_unserialize($form[0]->form_data);

                $text_follow = '';
                foreach ($form_data_result as $results) {
                    $default_phonenumber = isset($results['default_phonenumber']) ? $results['default_phonenumber'] : 0;
                    if ( $default_phonenumber !== 0 ) {
                        $field_id = $results['field_id'];
                        $table_row .= '<td><a class="lead-followup-wa" href="https://wa.me/'.$form_data['text_'.$field_id].'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
                        $text_follow = "Follow Up";
                    }
                }

                // $table_row .= '<td></span><a class="lead-followup-wa"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></span></span><a class="lead-remove" onclick="delete_this_lead(' . $lead_id . ',\''.$nonce.'\')"><i class="fa fa-trash" aria-hidden="true" title="Hapus"></i></a></span></td>';
         
                foreach ($form_data as $form_data_key => $form_data_value) {
                    $row_size_limit++;

                    if (($detail_view != 1) && ($row_size_limit == 6)) {
                        $table_row .= '<td> . . . </td><td><a href="#lf-openModal-' . $lead_id . '" value="view">view</a></td>';
                    }
                }
                $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

                $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
                    <div class="lfb-popup-leads"><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
                    </div>
                    </div>';

                $table_body .= '<tbody id="lead-id-' . $lead_id . '">';
                $table_body .= '<tr><td><span class="lead-count"><a href="#lf-openModal-' . $lead_id . '" title="View Detail">#' . $sn_counter . '</a></td>'. $table_row .'</tr>';
            }

            $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat fixed" id="show-leads-table" >
                <thead><tr><th>ID</th><th>Product</th>'.$tableHead.'<th>Affiliate</th><th>Value</th><th>Date</th>'.$table_head.'<th>'.$text_follow.'</th></tr></thead>';

            echo wp_kses($thHead . $table_body . '</tbody></table>' . $popupTab, $showLeadsObj->expanded_alowed_tags());

            $total = ceil($rows / $limit);
            if ($id > 1) {
                echo "<a href=''  onclick='lead_pagi_view(" . intval($id - 1) . "," . intval($form_id) . ")' class='button'><i class='fa fa-chevron-right'></i></a>";
            }
            if ($id != $total) {
                echo "<a href='' onclick='lead_pagi_view(" . intval($id + 1) . "," . intval($form_id) . ")' class='button'><i class='fa fa-chevron-left'></i></a>";
            }
            echo "<ul class='page'>";
            for ($i = 1; $i <= $total; $i++) {
                if ($i == $id) {
                    echo "<li class='lf-current'><a href='#'>" . intval($i) . "</a></li>";
                } else {
                    echo "<li><a href='' onclick='lead_pagi_view(" . intval($i) . "," . intval($form_id) . ")'>" . intval($i) . "</a></li>";
                }
            }
            echo '</ul>';
        } else {
            esc_html_e('No leads..!', 'sejoli-lead-form');
        }
        die();
    }

}
add_action('wp_ajax_ShowLeadPagi', 'lfb_ShowLeadPagi');

/**
 * Show Leads on Lead Page Based on form selection
 * Hooked via action wp_ajax_ShowAllLeadThisFormDate
 * @since   1.0.0
 */
function lfb_ShowAllLeadThisFormDate(){
   
    if ((isset($_POST['form_id']) && ($_POST['form_id'] != '')) || (isset($_GET['form_id']) && ($_GET['form_id'] != ''))) {
        global $wpdb;
        $nonce = wp_create_nonce('lfb-nonce-rm');
        $table_name = LFB_FORM_DATA_TBL;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $showLeadsObj = new LFB_Show_Leads();
        $start = 0;
        $limit = 10;
        $detail_view = '';

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $datewise = sanitize_text_field($_GET['datewise']);
            $start = ($id - 1) * $limit;
            $form_id = intval($_GET['form_id']);
            $sn_counter = $start;
        } else {
            $id = 1;
            $datewise = '';
            $sn_counter = 0;
        }
        if (isset($_GET['detailview'])) {
            $detail_view = sanitize_text_field($_GET['detailview']);
        }
        $getArray =  $th_save_db->lfb_get_all_view_date_leads_db($form_id, $datewise, $start);

        $posts          = $getArray['posts'];
        $rows           = $getArray['rows'];
        $limit          = $getArray['limit'];
        $fieldData       = $getArray['fieldId'];
        $fieldIdNew     = array();
        $headcount = 1;

        $tableHead  = '';

        foreach ($fieldData as $fieldkey => $fieldvalue) {
            if ($headcount < 6) {
                $tableHead  .= '<th>' . $fieldvalue . '</th>';
            }
            $fieldIdNew[] = $fieldkey;
            // } else{ break; }
            $headcount++;
        }

        if (!empty($posts)) {
            $entry_counter = 0;
            $value1 = 0;
            $table_body = '';
            $table_head = '';
            $popupTab   = '';

            if ($headcount >= 6) {
                $table_head .= '<th><input type="button" onclick="show_all_leads(' . $id . ',' . $form_id . ')" value="Show all fields"></th>';
            }

            foreach ($posts as $results) {
                $table_row = '';
                $sn_counter++;
                $row_size_limit = 0;
                $form_data = $results->form_data;
                $lead_id = $results->id;
                $product_id = $results->product;
                $product    = sejolisa_get_product($product_id);
                $affiliate_id = $results->affiliate;
                $affiliate    = sejolisa_get_user($affiliate_id);
                $form_data = maybe_unserialize($form_data);
                $lead_date = date("jS F Y", strtotime($results->date));
                unset($form_data['hidden_field']);
                unset($form_data['action']);
                unset($form_data['g-recaptcha-response']);
                $entry_counter++;
                $complete_data = '';
                $popup_data_val= '';
                $date_td = '<td><b>'.$lead_date.'</b></td>';

                $returnData = $th_save_db->lfb_lead_form_value($form_data,$fieldIdNew,$fieldData,5);
                $table_row .= "<td>".$product->post_title."</td>";
                $table_row .= $returnData['table_row'];
                if($affiliate->display_name) {
                    $table_row .= "<td>".$affiliate->display_name."</td>";
                } else {
                    $table_row .= "<td>-</td>";
                }
                $table_row .= $date_td;
                $table_row .= '<td></span><a class="lead-followup-wa"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></span></span><a class="lead-remove" onclick="delete_this_lead(' . $lead_id . ',\''.$nonce.'\')"><i class="fa fa-trash" aria-hidden="true" title="Hapus"></i></a></span></td>';

                foreach ($form_data as $form_data_key => $form_data_value) {
                    $row_size_limit++;

                    if (($detail_view != 1) && ($row_size_limit == 6)) {
                        $table_row .= '<td> . . . </td><td><a href="#lf-openModal-' . $lead_id . '" value="view">view</a></td>';
                    }
                }
                $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

                $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
                <div class="lfb-popup-leads"><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
                </div>
                </div>';

                $table_body .= '<tbody id="lead-id-' . $lead_id . '">';
                $table_body .= '<tr><td><span class="lead-count"><a href="#lf-openModal-' . $lead_id . '" title="View Detail">#' . $sn_counter . '</a></td>'. $table_row .'</tr>';
            }

            echo wp_kses($thHead . $table_body . '</tbody></table>' . $popupTab, $showLeadsObj->expanded_alowed_tags());

            $rows = count($rows);
            $total = ceil($rows / $limit);
            if ($id > 1) {
                echo "<a href=''  onclick='lead_pagination_datewise(" . intval($id - 1) . "," . intval($form_id) . ",\"" . $datewise . "\");' class='button'><i class='fa fa-chevron-right'></i></a>";
            }
            if ($id != $total) {
                echo "<a href='' onclick='lead_pagination_datewise(" . intval($id + 1) . "," . intval($form_id) . ",\"" . $datewise . "\");' class='button'><i class='fa fa-chevron-left'></i></a>";
            }
            echo "<ul class='page'>";
            for ($i = 1; $i <= $total; $i++) {
                if ($i == $id) {
                    echo "<li class='lf-current'><a>" . intval($i) . "</a></li>";
                } else {
                    echo "<li><a href='' onclick='lead_pagination_datewise(" . intval($i) . "," . intval($form_id) . ",\"" . $datewise . "\");'>" . intval($i) . "</a></li>";
                }
            }
            echo '</ul>';
        } else {
            esc_html_e('No leads..!', 'sejoli-lead-form');
        }
        die();
    }

}
add_action('wp_ajax_ShowAllLeadThisFormDate', 'lfb_ShowAllLeadThisFormDate');

/**
 * Save from Data from front-end
 * @since   1.0.0
 */
function lfb_form_name_email_filter($form_data){
   
    $name_email = array();
    $e = false;
    $n = false;
    foreach ($form_data as $key => $value) {
        $email = strpos($key, 'email_');
        $name = strpos($key, 'name_');
        if ($email !== false) {
            $name_email['email'] = $value;
            $e = true;
        } elseif ($name !== false) {
            $name_email['name'] = $value;
            $n = true;
        }
        if ($e === true && $n === true) {
            break;
        }
    }

    return $name_email;

}

/**
 * Show Leads Sanitize
 * @since   1.0.0
 */
function lfb_lead_sanitize($leads){

    if (is_array($leads)) {

        foreach ($leads as $key => $value) {
            $rKey = preg_replace("/[^a-zA-Z]+/", "", $key);
            if ($rKey === 'name' || $rKey === 'text' || $rKey === 'radio' || $rKey === 'option') {
                $leads[$key] = sanitize_text_field($value);
            } elseif ($rKey === 'email') {
                $leads[$key] = sanitize_email($value);
            } elseif ($rKey === 'number') {
                $leads[$key] = intval($value);
            } elseif ($rKey === 'message' || $rKey === 'textarea') {
                $leads[$key] = sanitize_textarea_field($value);
            } elseif ($rKey === 'date' || $rKey === 'dob') {
                $leads[$key] = sanitize_text_field($value);
            } elseif ($rKey === 'url') {
                $leads[$key] = esc_url_raw($value);
            } elseif ($rKey === 'checkbox') {
                foreach ($value as $ckey => $cvalue) {
                    $value[$ckey] = sanitize_text_field($cvalue);
                }
                $leads[$key] = $value;
            }
        } // end foreach

        return $leads;

    }

}

/**
 * Save Form Data
 * Hooked via action wp_ajax_Save_Form_Data, wp_ajax_nopriv_Save_Form_Data
 * @since   1.0.0
 */
function lfb_Save_Form_Data(){

    if (isset($_POST['fdata'])) {

        wp_parse_str($_POST['fdata'], $fromData);

        $form_id = intval($fromData['hidden_field']);
        $form_product = intval($fromData['product']);
        $form_affiliate = intval($fromData['affiliate_id']);
        unset($fromData['g-recaptcha-response']);
        unset($fromData['action']);
        unset($fromData['hidden_field']);
        unset($fromData['product']);
        unset($fromData['affiliate_id']);

        $en = lfb_form_name_email_filter($fromData);


        if ((isset($en['email'])) && ($en['email'] != '')) {
            $user_emailid = sanitize_email($en['email']);
        } else {
            $user_emailid = esc_html__('invalid_email', 'sejoli-lead-form');
        }
        $sanitize_leads =  lfb_lead_sanitize($fromData);
        $form_data = maybe_serialize($sanitize_leads);

        $lf_store   = new LFB_LeadStoreType();
        $th_save_db = new LFB_SAVE_DB();

        $lf_store->lfb_mail_type($form_id, $form_product, $form_affiliate, $form_data, $th_save_db, $user_emailid);
        
        lfb_register_autoresponder($form_id, $form_product, $form_data);

    }

    die();

}
add_action('wp_ajax_Save_Form_Data', 'lfb_Save_Form_Data');
add_action('wp_ajax_nopriv_Save_Form_Data', 'lfb_Save_Form_Data');

/**
 * Register buyer to selected autoresponder setup.
 * @param  array  $order_data [description]
 * @return [type]             [description]
 */
function lfb_register_autoresponder($form_id, $product, $form_data) {

    $product = sejolisa_get_product( intval($product) );

    $th_save_db = new LFB_SAVE_DB();
    $posts = $th_save_db->lfb_get_form_data($form_id);

    $code          = $posts[0]->autoresponder_setting;
    $autoresponder = sejolisa_parsing_form_html_code( $code );

    if( false !== $autoresponder['valid'] ) :

        $body_fields = [];

        $form = $th_save_db->lfb_get_form_data($form_id);
        $form_datas = maybe_unserialize($form[0]->form_data);

        $form_field = $th_save_db->lfb_admin_email_send($form_id);
        $form_data = maybe_unserialize($form_data);

        foreach ($form_datas as $results) {

            $type = $results['field_type']['type'];
            $field_id = $results['field_id'];

            $default_phonenumber = isset($results['default_phonenumber']) ? $results['default_phonenumber'] : 0;

            if ($type === 'email') { 
                $data_email = $form_data['email_'.$field_id];
            } elseif($type === 'number') { 
                $data_phone = '+62'.$form_data['number_'.$field_id];
            } elseif($type === 'name') { 
                $data_name = $form_data['name_'.$field_id];
            }

            if ( $default_phonenumber !== 0 ) {
                if ($type === 'text') { 
                    $data_phone = $form_data['text_'.$field_id];
                } else {
                    $data_phone = '+62'.$form_data['number_'.$field_id];
                }
            }

        }

        foreach($autoresponder['fields'] as $field) :

            if('email' === $field['type']) :
                $body_fields[$field['name']] = 'asd@gmail.com';//$data_email;
            elseif('name' === $field['type']) :
                $body_fields[$field['name']] = $data_name;
            else :
                $body_fields[$field['name']] = $field['value'];
            endif;

        endforeach;

        $response = wp_remote_post( $autoresponder['form']['action'][0], [
            'method'  => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Referer'    => site_url(),
                'User-Agent' => $_SERVER['HTTP_USER_AGENT']
            ),
            'body'    => $body_fields
        ]);

        do_action('sejoli/log/write', 'response autoresponder subscription', [
            'url'         => $autoresponder['form']['action'][0],
            'body_fields' => $body_fields,
            'response'    => strip_tags(wp_remote_retrieve_body($response))
        ]);

    endif;

}

/**
 * Verify Form Captcha
 * Hooked via action wp_ajax_verifyFormCaptcha, wp_ajax_nopriv_verifyFormCaptcha
 * @since   1.0.0
 */
function lfb_verifyFormCaptcha(){
    
    if ((isset($_POST['captcha_res'])) && (!empty($_POST['captcha_res']))) {
        $captcha = stripslashes($_POST['captcha_res']);
        $secret_key = get_option('captcha-setting-secret');
        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            array(
                'method' => 'POST',
                'body' => array(
                    'secret' => $secret_key,
                    'response' => $captcha
                )
            )
        );
        
        $reply_obj = json_decode(wp_remote_retrieve_body($response));
        if (isset($reply_obj->success) && $reply_obj->success == 1) {
            esc_html_e('Yes', 'sejoli-lead-form');
        } else {
            esc_html_e('No', 'sejoli-lead-form');
        }
    } else {
        esc_html_e('Invalid', 'sejoli-lead-form');
    }

    die();

}
add_action('wp_ajax_verifyFormCaptcha', 'lfb_verifyFormCaptcha');
add_action('wp_ajax_nopriv_verifyFormCaptcha', 'lfb_verifyFormCaptcha');

/**
 * Remember This Form
 * Hooked via action wp_ajax_RememberMeThisForm
 * @since   1.0.0
 */
function lfb_RememberMeThisForm(){

    $nonce = $_POST['rem_nonce'];

    if (isset($_POST['form_id'])  && lfb_user_permission_check() && wp_verify_nonce($nonce, 'rem-nonce')) {

        $remember_me = intval($_POST['form_id']);

        if (get_option('lf-remember-me-show-lead') !== false) {
            update_option('lf-remember-me-show-lead', $remember_me);
        } else {
            add_option('lf-remember-me-show-lead', $remember_me);
        }
        echo esc_html(get_option('lf-remember-me-show-lead'));

        die();
    }

}
add_action('wp_ajax_RememberMeThisForm', 'lfb_RememberMeThisForm');

/**
 * Save Email Settings
 * @since   1.0.0
 */
function lfb_emailsettings_sanitize($email_settings){

    $email_settings['from'] = sanitize_email($email_settings['from']);
    $email_settings['header'] = sanitize_text_field($email_settings['header']);
    $email_settings['subject'] = sanitize_text_field($email_settings['subject']);
    $email_settings['message'] = sanitize_textarea_field($email_settings['message']);
    $email_settings['user-email-setting-option'] = sanitize_text_field($email_settings['user-email-setting-option']);
    $email_settings['affiliate-email-setting-option'] = sanitize_text_field($email_settings['affiliate-email-setting-option']);
    $email_settings['form-id'] = intval($email_settings['form-id']);

    return $email_settings;

}