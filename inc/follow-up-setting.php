<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LFB_FollowUpSettingForm{

    /**
     * Register Construct
     * @since   1.0.0
     */
    function __construct($this_form_id){
        global $wpdb;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_9 = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d LIMIT 1", $this_form_id);
        $posts = $th_save_db->lfb_get_form_content($prepare_9);
        if ($posts) {
            $form_title = $posts[0]->form_title;
            $form_status = $posts[0]->form_status;
            $captcha_status = $posts[0]->captcha_status;
            $storeType = $posts[0]->storeType;
            $storedate = $posts[0]->date;
            $followup_setting = maybe_unserialize($posts[0]->followup_setting);
            $form_data = maybe_unserialize($posts[0]->form_data);
        }
    }

    /**
     * Autoresponder Setting Form
     * @since   1.0.0
     */
    function lfb_followup_setting_form($this_form_id, $followup_setting_result){
        $followup_setting_message = '';
        if (!empty($followup_setting_result)) {
            $followup_setting_message = $followup_setting_result;
        }
        $afs_nonce = wp_create_nonce( 'afs-nonce' );

        echo "<div style='margin-top: 1em;'>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="width:90.5%">';
        } else {
            echo "<div class='form-block' style='width: 55%;'>";
        }
        echo "<form id='form-followup-setting' action='' method='post'>
            <div class='inside followup_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('Follow Up Setting', 'sejoli-lead-form') . "</h2><br>
            <table class='form-table'>
                <tbody>
                    <tr>
                        <th scope='row'><label for='followup_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='followup_setting[message]' id='followup_setting_message' rows='10' cols='90' required>" . esc_html($followup_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type='hidden' name='followup_setting[form-id]' required value='" . intval($this_form_id) . "'> 
            <input type='hidden' name='afs_nonce' value='".$afs_nonce."'>
            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 2em 8px 0 8px;'  id='button' value='Save'>
            </p>
            </div><div id='error-message-followup-setting'></div></div></div>
            </form>";
            echo "</div>";
        echo "</div>";

    }

}