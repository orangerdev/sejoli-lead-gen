<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LFB_WhatsAppSettingForm{

    /**
     * Register Construct
     * @since   1.0.0
     */
    function __construct($this_form_id){
        global $wpdb;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_9 =  $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d LIMIT 1", $this_form_id);
        $posts = $th_save_db->lfb_get_form_content($prepare_9);
        if ($posts) {
            $form_title = $posts[0]->form_title;
            $form_status = $posts[0]->form_status;
            $captcha_status = $posts[0]->captcha_status;
            $storeType = $posts[0]->storeType;
            $storedate = $posts[0]->date;
            $wa_setting = maybe_unserialize($posts[0]->wa_setting);
            $userwa_setting = maybe_unserialize($posts[0]->userwa_setting);
            $affiliatewa_setting = maybe_unserialize($posts[0]->affiliatewa_setting);
            $form_data = maybe_unserialize($posts[0]->form_data);
        }
    }

    /**
     * Whatsapp Setting Forms
     * @since   1.0.0
     */
    function lfb_whatsapp_setting_form($this_form_id, $wa_setting_result, $userwa_setting, $affiliatewa_setting){

        $wa_setting_message = '[lf-new-form-data]';
        $multi_wa = "";
        $wa_to = '';
        if (!empty($wa_setting_result)) {
            $wa_setting_result = maybe_unserialize($wa_setting_result);
            $wa_setting_message = $wa_setting_result['whatsapp_setting']['message'];
            $multi_wa = (isset($wa_setting_result['whatsapp_setting']['multiple'])) ? $wa_setting_result['whatsapp_setting']['multiple'] : '';
            $wa_to = $wa_setting_result['whatsapp_setting']['to'];
        }
        $awas_nonce = wp_create_nonce( 'awas-nonce' );

        echo "<div>";
        echo '<div><b>Shortcode</b>: <pre><i><code title="'.__('Shortcode untuk menampilkan semua entri dari form.', 'sejoli-lead-form').'"> [lf-new-form-data]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli-lead-form').'">[affiliate-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli-lead-form').'">[affiliate-phone]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli-lead-form').'">[affiliate-email]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli-lead-form').'">[product-name]</code> </i> <i><code title="'.__('Shortcode untuk menampilkan harga produk.', 'sejoli-lead-form').'">[product-price]</code> </i></pre></br></div>';
        echo "<form id='form-wa-setting' action='' method='post'>
            <div class='inside wa_setting_section'>
            <div class='card'>
            <div class='infobox'>
            <h2>" . esc_html__('Admin WhatsApp Notifications', 'sejoli-lead-form') . "</h2><br>
            <table class='form-table'>
                <tbody>
                    <tr><th scope='row'><label for='whatsapp_setting_to'>Nomor WhatsApp Penerima" . LFB_REQUIRED_SIGN . "</label></th>
                        <td><input name='whatsapp_setting[to]' required type='text' id='whatsapp_setting_to' value='".$wa_to."' class='regular-text'>
                        <p class='description' id='from-description'>" . esc_html__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli-lead-form') . "</p></td>
                    </tr>
                    <tr>
                        <th scope='row'><label for='whatsapp_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='whatsapp_setting[message]' id='whatsapp_setting_message' rows='5' cols='46' required>" . esc_html($wa_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                    <tr>
                        <td><input type='hidden' name='whatsapp_setting[form-id]' required value='" . intval($this_form_id) . "'> 
                        <input type='hidden' name='awas_nonce' value='".$awas_nonce."'>

                        <input type='submit' class='button-primary' id='button' value='Save'></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div><div id='error-message-wa-setting'></div></div></div>
            </form>";
        echo "</div>";

        $userwa_setting_message   = esc_html('Form Submitted Successfully');
        if (!empty($userwa_setting)) {
            $userwa_setting_result = maybe_unserialize($userwa_setting);
            $userwa_setting_message = $userwa_setting_result['user_wa_setting']['message'];
        }
        $uwas_nonce = wp_create_nonce( 'uwas-nonce' );

        echo "<div>";
        echo "<form id='form-user-wa-setting' action='' method='post'>
            <div class='inside wa_setting_section'>
            <div class='card'>
            <div class='infobox'>
            <h2>" . esc_html__('User WhatsApp Notifications', 'sejoli-lead-form') . " </h2><br>
            <table class='form-table'>
                <tbody>
                    <tr>
                        <th scope='row'><label for='user_wa_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='user_wa_setting[message]' id='user_wa_setting_message' rows='5' cols='46' required>" . esc_html($userwa_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                    <tr>
                        <td><input type='hidden' name='user_wa_setting[form-id]' required value='" . $this_form_id . "'> 
                        
                        <input type='hidden' name='uwas_nonce' value='".$uwas_nonce."'>

                        <input type='submit' class='button-primary' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'></p>
                        </td>
                    </tr>
                </tbody>
            </table> 
            </div>
            <div id='error-message-user-wa-setting'></div></div> </div>
            </form>";
        echo "</div>";

        $affiliatewa_setting_message   = esc_html('Form Submitted Successfully');
        if (!empty($affiliatewa_setting)) {
            $affiliatewa_setting_result = maybe_unserialize($affiliatewa_setting);
            $affiliatewa_setting_message = $affiliatewa_setting_result['affiliate_wa_setting']['message'];
        }
        $affwas_nonce = wp_create_nonce( 'affwas-nonce' );

        echo "<div>";
        echo "<form id='form-affiliate-wa-setting' action='' method='post'>
            <div class='inside wa_setting_section'>
            <div class='card'>
            <div class='infobox'>
            <h2>" . esc_html__('Affiliate WhatsApp Notifications', 'sejoli-lead-form') . " </h2><br>
            <table class='form-table'>
                <tbody>
                    <tr>
                        <th scope='row'><label for='affiliate_wa_setting_message'>Message" . LFB_REQUIRED_SIGN . "</th>
                        <td>
                            <textarea name='affiliate_wa_setting[message]' id='affiliate_wa_setting_message' rows='5' cols='46' required>" . esc_html($affiliatewa_setting_message) . "</textarea></label>
                        </td>
                    </tr>
                    <tr>
                        <td><input type='hidden' name='affiliate_wa_setting[form-id]' required value='" . $this_form_id . "'> 
                        
                        <input type='hidden' name='affwas_nonce' value='".$affwas_nonce."'>

                        <input type='submit' class='button-primary' id='button' value='" . esc_html__('Save', 'sejoli-lead-form') . "'></p>
                        </td>
                    </tr>
                </tbody>
            </table> 
            </div>
            <div id='error-message-affiliate-wa-setting'></div></div> </div>
            </form>";
        echo "</div>";
    }

}