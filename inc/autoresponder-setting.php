<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LFB_AutoresponderSettingForm{

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
            $autoresponder_setting = maybe_unserialize($posts[0]->autoresponder_setting);
            $form_data = maybe_unserialize($posts[0]->form_data);
        }
    }

    /**
     * Autoresponder Setting Form
     * @since   1.0.0
     */
    function lfb_autoresponder_setting_form($this_form_id, $autoresponder_setting_result){
        $autoresponder_setting_code = '';
        if (!empty($autoresponder_setting_result)) {
            $autoresponder_setting_code = $autoresponder_setting_result;
        }
        $aaress_nonce = wp_create_nonce( 'aaress-nonce' );

        echo "<div style='margin-top: 1em;'>";
        if(wp_is_mobile()){
            echo '<div class="form-block" style="width:90.5%">';
        } else {
            echo "<div class='form-block' style='width: 55%;'>";
        }
        echo "<form id='form-autoresponder-setting' action='' method='post'>
            <div class='inside autoresponder_setting_section'>
            <div class='cards'>
            <div class='infobox'>
            <h2>" . esc_html__('Autoresponder Setting', 'sejoli-lead-form') . "</h2><br>
            <table class='form-table'>
                <tbody>
                    <tr>
                        <th scope='row'><label for='autoresponder_setting_code'>HTML Code" . LFB_REQUIRED_SIGN . "</th>
                        <td style='width: 490px;'>
                            <textarea name='autoresponder_setting[code]' id='autoresponder_setting_code' rows='10' cols='90' required>" . esc_html($autoresponder_setting_code) . "</textarea></label>
                            <p class='description' id='from-description'>" . esc_html__('Paste kode HTML Form yang anda dapatkan dari autoresponder. Jika anda masih belum mengerti hal, silahkan tanyakan ke autoresponder yang anda gunakan', 'sejoli-lead-form') . "</p></td>
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type='hidden' name='autoresponder_setting[form-id]' required value='" . intval($this_form_id) . "'> 
            <input type='hidden' name='aaress_nonce' value='".$aaress_nonce."'>
            <p style='text-align:right'>
            <input type='submit' class='button-primary' style='background: #ff4545; margin: 0em 8px 0 8px;' id='button' value='Save'>
            </p>
            </div><div id='error-message-autoresponder-setting'></div></div></div>
            </form>";
            echo "</div>";
        echo "</div>";

    }

    /**
     * Parsing given form HTML code to array data to validate subscription form
     * @since   1.0.0
     * @param   string   $code   Autoresponder html code
     * @return  array            Array of response
     */
    function sejoli_lead_form_parsing_form_html_code($code) {

        $response = [
            'valid'    => false,
            'messages' => [],
            'form'     => [],
            'fields'   => []
        ];

        // strip unneccessary tags
        $form   = strip_tags($code, '<form><input><button>');
        preg_match_all("'<(.*?)>'si", $form, $matches);

        if (
            is_array($matches) &&
            isset($matches[0])
        ) :

            $matches    = $matches[0];
            $html       = stripslashes(join('', $matches));
            $html       = str_replace("</input>","",$html);
            $clean_form = htmlspecialchars(str_replace(array('><', '<input'), array(">\n<", "\t<input"), $html), ENT_NOQUOTES);

            $doc        = new DOMDocument();

            $doc->strictErrorChecking = FALSE;
            $doc->loadHTML($html);

            $xml    = simplexml_import_dom($doc);

            if ($xml) :

                $form   = $xml->body->form;

                if ($form) :

                    unset($error);

                    if(!isset($form['action'])) :

                        $response['messages'][] = __('Kode HTML yang diberikan tidak lengkap. Pada tag FORM tidak terdapat attribut ACTION.', 'sejoli');

                    elseif ($form->input) :

                        $response['form'] = [
                            'action'   => $form['action'],
                            'method'   => $form['method']
                        ];

                        $dform  = @json_decode(@json_encode($form),1);

                        foreach ($form->input as $dinput) :

                            $iinput = @json_decode(@json_encode($dinput),1);
                            $input  = $iinput['@attributes'];

                            if ('hidden' === $input['type']) :

                                $type   = 'hidden';
                                $value  = $input['value'];
                                $additional_data[] = array($input['name'], $input['value']);

                            elseif('submit' === $input['type']) :
                                continue;

                            elseif(
                                ( isset($input['id']) && FALSE !== stripos($input['id'], 'email') ) ||
                                FALSE !== stripos($input['name'], 'email')
                            ) :
                                $type   = "email";
                                $value  = "";
                                $email_identifier   = $input['name'];

                            elseif (
                                ( isset($input['id']) && FALSE !== stripos($input['id'], 'name') ) ||
                                FALSE !== stripos($input['name'], 'name')
                            ) :
                                $type   = "name";
                                $value  = "";
                                $name_identifier = $input['name'];
                            else :
                                $type   = $input['type'];
                                $value  = isset($input['value']) ? $input['value'] : '';
                            endif;

                            $response['fields'][]   = array(
                                'name'  => $input['name'],
                                'type'  => $type,
                                'value' => $value,
                            );

                        endforeach;

                        // Correct value's
                        if (!isset($email_identifier)) :
                            $response['messages'][]   = __('Kode HTML form yang anda masukkan tidak memiliki field untuk mengisi alamat email', 'sejoli-lead-form');
                        else :
                            $response['valid'] = true;
                        endif;

                    endif;
                else :
                    $response['messages'][]   = __('Kode HTML form yang anda masukkan tidak valid', 'sejoli-lead-form');
                endif;

            endif;
        endif;

        return $response;

    }

}