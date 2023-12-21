<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
Class LFB_Show_Leads {

    /**
     * Set Allowed Tags
     * @since   1.0.0
     */
    function expanded_alowed_tags() {
        $allowed = wp_kses_allowed_html( 'post' );

        // form fields - input
        $allowed['a'] = array(
            'href' => array(),
            'class'    => array(),
            'onclick'  => array(),
        );

        // form fields - input
        $allowed['input'] = array(
            'class' => array(),
            'id'    => array(),
            'name'  => array(),
            'value' => array(),
            'type'  => array(),
            'onclick' => array(),
        );

        $allowed['option'] = array(
            'value'    => array(),
            'selected'   => array(),
        );

        return $allowed;
    }

    /**
     * Convert Number into Phone Number Format
     * @since   1.0.0
     */
    function phone_number_format($nomorhp) {

        // Terlebih dahulu kita trim dl
        $nomorhp = trim($nomorhp);
        // Bersihkan dari karakter yang tidak perlu
        $nomorhp = strip_tags($nomorhp);     
        // Berishkan dari spasi
        $nomorhp= str_replace(" ","",$nomorhp);
        // Bersihkan dari bentuk seperti  (022) 66677788
        $nomorhp= str_replace("(","",$nomorhp);
        // Bersihkan dari format yang ada titik seperti 0811.222.333.4
        $nomorhp= str_replace(".","",$nomorhp); 

        //cek apakah mengandung karakter + dan 0-9
        if(!preg_match('/[^+0-9]/',trim($nomorhp))){
            // cek apakah no hp karakter 1-3 adalah +62
            if(substr(trim($nomorhp), 0, 3) == '+62'){
                $nomorhp= trim($nomorhp);
            }
            // cek apakah no hp karakter 1 adalah 0
            elseif(substr($nomorhp, 0, 1) == '0'){
                $nomorhp= '+62'.substr($nomorhp, 1);
            }
        }

        return $nomorhp;

    }

    /**
     * Show Form Leads
     * @since   1.0.0
    */
    function lfb_show_form_leads() {
        global $wpdb;
        $option_form = '';
        $first_form=0;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_16 = $wpdb->prepare("SELECT * FROM $table_name WHERE form_status = %s ORDER BY id DESC ",'ACTIVE');
        $posts = $th_save_db->lfb_get_form_content($prepare_16);
        if (!empty($posts)) {
            foreach ($posts as $results) {
                $first_form++;
                $form_title = $results->form_title;
                $form_id = $results->id;
                if($first_form==1){
                    $first_form_id = $results->id;
                    if (get_option('lf-remember-me-show-lead') !== false ) {
                        $first_form_id = get_option('lf-remember-me-show-lead');
                        $get_form = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d ORDER BY id DESC ", $first_form_id);
                        $query_forms = $th_save_db->lfb_get_form_content($get_form);
                    }
                }
                $option_form .= '<option ' . ($first_form_id == $form_id ? 'selected="selected"' : "" ) . ' value=' . $form_id . '>' . $form_title . '</option>';
            }
        }
        $rem_nonce = wp_create_nonce( 'rem-nonce' );

        include_once( plugin_dir_path(__FILE__) . 'header.php' );

        $start = 0;
        $user_ID = get_current_user_id(); 
        $getArray = $th_save_db->lfb_get_affiliate_view_leads_db($first_form_id, $user_ID, $start);

        $fieldData  = $getArray['fieldId'];
        $id         = $headcount = 1;
        $fieldIdNew = array();

        echo '<div class="form-block">';
            echo '<div class="wrap"><div class="inside"><div class="card"><table class="form-table"><tbody><tr><th scope="row">
                <label for="select_form_lead">'.esc_html__('Select From','sejoli-lead-form').'</label></th>
                <td><select name="select_form_lead" id="select_form_lead">' . wp_kses($option_form,$this->expanded_alowed_tags()) . '</select>
                <td><input rem_nonce = "'.$rem_nonce.'" type="button" value="'.esc_html__('Show Entries','sejoli-lead-form').'" onclick="remember_this_form_id();" id="remember_this_form_id"></td>
                </tr><tr><td><div id="remember_this_message" ></div></td></tr></tbody></table></div></div></div><div class="wrap" id="form-leads-shows-box">';
            if(wp_is_mobile()){
                echo '<div class="wrap" style="display: inline-block;width: 100%; margin-bottom: 2em !important;">';
                    echo '<h3 style="margin-bottom: 0;float: none;">'.$query_forms[0]->form_title.'</h3>';
                    echo '<div class="export-button"></div><table class="form-table" style="margin-bottom: 1.5em; float: none;width: `00%;clear: inherit; margin: 0;"><tbody><tr><th scope="row" style="width: auto !important; padding: 20px 0; text-align: left;">
                    <label for="filter_lead_entries">'.esc_html__('Filter Data','sejoli-lead-form').'</label></th><td><input type="text" name="filter-lead-entries" id="filter_lead_entries"/><input type="hidden" name="form_id_filter" value="'.$first_form_id.'"/></td></tr></tbody></table>';
                echo '</div>';
            } else {
                echo '<div class="wrap" style="display: inline-block;width: 100%; margin-bottom: 1.5em !important;">';
                    echo '<h3 style="margin-bottom: 3em; margin-top: 1em;float: left;">'.$query_forms[0]->form_title.'</h3>';
                    echo '<div class="export-button"></div><table class="form-table" style="float: right;width: 23%;clear: inherit; margin: 0;"><tbody><tr><th scope="row" style="width: auto !important; padding: 20px 0; text-align: right;">
                    <label for="filter_lead_entries">'.esc_html__('Filter Data','sejoli-lead-form').'</label></th><td><input type="text" name="filter-lead-entries" id="filter_lead_entries"/><input type="hidden" name="form_id_filter" value="'.$first_form_id.'"/></td></tr></tbody></table>';
                echo '</div>';
            }
            $this->lfb_show_leads_first_form($first_form_id);
            echo '<div class="loading" style="display: none;">'.esc_html__('Please Wait...', 'sejoli-lead-form').'</div>';
            echo '</div>';
        echo '</div>';
    }

    /**
     * Show Leads First Form
     * @since   1.0.0
     */
    function lfb_show_leads_first_form($form_id){
        $start = 0;

        $th_save_db = new LFB_SAVE_DB();
        $getArray = $th_save_db->lfb_get_all_view_leads_db($form_id,$start);
        $nonce = wp_create_nonce( 'lfb-nonce-rm' );

        $posts      = $getArray['posts'];
        $rows       = $getArray['rows'];
        $limit      = $getArray['limit'];
        $fieldData  = $getArray['fieldId'];
        $tableHead  = '';
        $sn_counter = 0;
        $headcount  = 1;
        $leadscount = 5;

        foreach ($fieldData as $fieldkey => $fieldvalue) {
            // Html Field removed
            $pos = strpos($fieldkey, 'htmlfield_');
            if ($pos !== false) {
                continue;
            }

            $tableHead  .= '<th class="all">' . $fieldvalue . '</th>';

            $leadscount =  $headcount;

            $fieldIdNew[] = $fieldkey;
            $headcount++;
        }

        if (!empty($posts)) {
            $entry_counter = 0;
            $table_head = '';
            $table_body = '';
            $popupTab   = '';

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
                $lead_date = date("j M Y", strtotime($results->date));
                $get_status = $results->status;

                if ($get_status === "lead") {
                    $status = '<button type="submit" class="button button-small button-status-lead" data-lead-id="'.$lead_id.'">'. __('Lead', 'sejoli-lead-form') .' </button>';
                } else {
                    $status = '<a href="#" class="button button-small button-status-customer">'. __('Customer', 'sejoli-lead-form') .' </a>';
                }

                unset($form_data['hidden_field']);
                unset($form_data['action']);
                unset($form_data['g-recaptcha-response']);
                $entry_counter++;
                $complete_data = '';
                $popup_data_val = '';
                $date_td = '<td><b>'.$lead_date.'</b></td>';

                $returnData = $th_save_db->lfb_lead_form_value($form_data,$fieldIdNew,$fieldData,100);
                $table_row .= $returnData['table_row'];

                $table_row .= "<td>".$product->post_title."</td>";

                $table_row .= "<td>".sejolisa_price_format($product->price)."</td>";

                if($affiliate_id > 0) {
                    $table_row .= "<td>".$affiliate->display_name."</td>";
                } else {
                    $table_row .= "<td>-</td>";
                }

                $table_row .= $date_td;
                $form = $th_save_db->lfb_get_form_data($results->form_id);
                $form_data_result = maybe_unserialize($form[0]->form_data);

                global $wpdb;
                $table_form = LFB_FORM_FIELD_TBL;
                $prepare_9 = $wpdb->prepare("SELECT * FROM $table_form WHERE id = %d LIMIT 1", $results->form_id);
                $posts = $th_save_db->lfb_get_form_content($prepare_9);
                if ($posts) {
                    $followup_text = maybe_unserialize($posts[0]->followup_setting);;
                }

                $text_follow = '';
                foreach ($form_data_result as $results) {
                    $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
                    if ( $type === 'phonenumber' ) {
                        $field_id = $results['field_id'];
                        $phone_number = isset($form_data['phonenumber_'.$field_id]) ? $this->phone_number_format($form_data['phonenumber_'.$field_id]) : '';
                        if ( wp_is_mobile() ) :
                            $table_row .= '<td><a target="_blank" class="lead-followup-wa" href="https://wa.me/'.$phone_number  . '?text='. $followup_text .'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
                        else :
                            $table_row .= '<td><a target="_blank" class="lead-followup-wa" href="https://api.whatsapp.com/send?phone='.$phone_number.'&text='.$followup_text.'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
                        endif;
                        $text_follow = "Follow Up";
                    }
                }

                $table_row .= '<td>'.$status.'</td>';

                $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

                $table_body .= '<tr>'. $table_row .'</tr>';
            }

            if(wp_is_mobile()){
                $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat " style="width: 100%" id="show-leads-table" >
                <thead><tr>'.$tableHead.'<th>Product</th><th>Value</th><th>Affiliate</th><th>Date</th>'.$table_head.'<th>'.$text_follow.'</th><th>Status</th></tr></thead>';
            } else {
                $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat" style="width: 100%" id="show-leads-table" >
                <thead><tr>'.$tableHead.'<th>Product</th><th>Value</th><th>Affiliate</th><th>Date</th>'.$table_head.'<th>'.$text_follow.'</th><th>Status</th></tr></thead>';
            }

            echo wp_kses($thHead. $table_body.'</table>',$this->expanded_alowed_tags());
         } else {
            ?>
            <div class="wrap" id="form-leads-show">
            <?php
                esc_html_e("No leads founds..!","sejoli-lead-form")
            ?>
            </div>
            <?php
        }
    }

    /**
     * Show Leads by Affiliate
     * @since   1.0.0
     */
    function lfb_show_form_leads_by_affiliate() {
        global $wpdb;
        $option_form = '';
        $first_form=0;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_16 = $wpdb->prepare("SELECT * FROM $table_name WHERE form_status = %s ORDER BY id DESC ", 'ACTIVE');
        $posts = $th_save_db->lfb_get_form_content($prepare_16);
        if (!empty($posts)) {
            foreach ($posts as $results) {
                $first_form++;
                $form_title = $results->form_title;
                $form_id = $results->id;
                if($first_form==1){
                    $first_form_id = $results->id;
                    if (get_option('lf-remember-me-show-lead') !== false ) {
                        $first_form_id = get_option('lf-remember-me-show-lead');
                        $get_form = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d ORDER BY id DESC ", $first_form_id);
                        $query_forms = $th_save_db->lfb_get_form_content($get_form);
                    }
                }
                $option_form .= '<option ' . ($first_form_id == $form_id ? 'selected="selected"' : "" ) . ' value=' . $form_id . '>' . $form_title . '</option>';
            }
        }
        $rem_nonce = wp_create_nonce( 'rem-nonce' );

        include_once( plugin_dir_path(__FILE__) . 'header.php' );

        $start = 0;
        $user_ID = get_current_user_id(); 
        $getArray = $th_save_db->lfb_get_affiliate_view_leads_db($first_form_id, $user_ID, $start);

        $fieldData  = $getArray['fieldId'];
        $id         = $headcount = 1;
        $fieldIdNew = array();

        echo '<div class="form-block">';
            echo '<div class="wrap"><div class="inside"><div class="card"><table class="form-table"><tbody><tr><th scope="row">
                <label for="select_form_lead"><b>'.esc_html__('Select From','sejoli-lead-form').'</b></label></th>
                <td><select name="select_form_lead" id="select_form_lead">' . wp_kses($option_form,$this->expanded_alowed_tags()) . '</select>
                <td><input rem_nonce = "'.$rem_nonce.'" type="button" value="'.esc_html__('Show Entries','sejoli-lead-form').'" onclick="remember_this_form_id();" id="remember_this_form_id"></td>
                </tr><tr><td colspan="3"><div id="remember_this_message" ></div></td></tr></tbody></table></div></div></div><div class="wrap" id="form-leads-shows-box">';
            if(wp_is_mobile()){
                echo '<div class="wrap" style="display: inline-block;width: 100%; margin-bottom: 1.5em !important;">';
                    echo '<h3 style="margin-bottom: 0;float: none;">'.$query_forms[0]->form_title.'</h3>';
                    echo '<div class="export-button"></div><table class="form-table" style="margin-bottom: 1em !important; float: none;width: 100%;clear: inherit; margin: 0;"><tbody><tr><th scope="row" style="width: auto !important; padding: 20px 0; text-align: left;">
                    <label for="filter_lead_entries">'.esc_html__('Filter Data','sejoli-lead-form').'</label></th><td><input type="text" name="filter-lead-entries" id="filter_lead_entries"/><input type="hidden" name="form_id_filter" value="'.$first_form_id.'"/></td></tr></tbody></table>';
                echo '</div>';
            } else {
                echo '<div class="wrap" style="display: inline-block;width: 100%; margin-bottom: 0 !important;">';
                    echo '<h3 style="margin-bottom: 3em;float: left; margin-top: 8px;">'.$query_forms[0]->form_title.'</h3>';
                    echo '<div class="export-button"></div><table class="form-table" style="float: right;width: 25%;clear: inherit; margin: 0;"><tbody><tr><th scope="row" style="width: auto !important; padding: 5px 0; text-align: right;">
                    <label for="filter_lead_entries"><b>'.esc_html__('Filter Data','sejoli-lead-form').'</b></label></th><td style="padding-right: 0;"><input type="text" name="filter-lead-entries" id="filter_lead_entries"/><input type="hidden" name="form_id_filter" value="'.$first_form_id.'"/></td></tr></tbody></table>';
                echo '</div>';
            }
            $this->lfb_show_leads_first_form_by_affiliate($first_form_id);
            echo '<div class="loading" style="display: none;">'.esc_html__('Please Wait...', 'sejoli-lead-form').'</div>';
            echo '</div>';
        echo '</div>';
    }

    /**
     * Show First Lead Form by Affiliate
     * @since   1.0.0
     */
    function lfb_show_leads_first_form_by_affiliate($form_id){
        $start = 0;

        $th_save_db = new LFB_SAVE_DB();
        $user_ID = get_current_user_id(); 
        $getArray = $th_save_db->lfb_get_affiliate_view_leads_db($form_id, $user_ID, $start);
        $nonce = wp_create_nonce( 'lfb-nonce-rm' );

        $posts      = $getArray['posts'];
        $rows       = $getArray['rows'];
        $limit      = $getArray['limit'];
        $fieldData  = $getArray['fieldId'];
        $tableHead  = '';
        $sn_counter = 0;
        $headcount  = 1;
        $leadscount = 5;

        foreach ($fieldData as $fieldkey => $fieldvalue) {
            // Html Field removed
            $pos = strpos($fieldkey, 'htmlfield_');
            if ($pos !== false) {
                continue;
            }

            $tableHead  .= '<th class="all">' . $fieldvalue . '</th>';

            $leadscount =  $headcount;

            $fieldIdNew[] = $fieldkey;
            $headcount++;
        }

        if (!empty($posts)) {
            $entry_counter = 0;
            $table_head = '';
            $table_body = '';
            $popupTab   = '';

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
                $lead_date = date("j M Y", strtotime($results->date));

                $get_status = $results->status;
                if ($get_status === "lead") {
                    $status = '<a href="#" class="button button-small button-status-lead">'. __('Lead', 'sejoli-lead-form') .' </a>';
                } else {
                    $status = '<a href="#" class="button button-small button-status-customer">'. __('Customer', 'sejoli-lead-form') .' </a>';
                }

                unset($form_data['hidden_field']);
                unset($form_data['action']);
                unset($form_data['g-recaptcha-response']);
                $entry_counter++;
                $complete_data = '';
                $popup_data_val= '';
                $date_td = '<td><b>'.$lead_date.'</b></td>';

                $returnData = $th_save_db->lfb_lead_form_value($form_data,$fieldIdNew,$fieldData,100);

                $table_row .= $returnData['table_row'];
                
                $table_row .= "<td>".$product->post_title."</td>";

                $table_row .= "<td>".sejolisa_price_format($product->price)."</td>";
                
                if($affiliate_id > 0) {
                    $table_row .= "<td>".$affiliate->display_name."</td>";
                } else {
                    $table_row .= "<td>-</td>";
                }

                $table_row .= $date_td;
                $form = $th_save_db->lfb_get_form_data($results->form_id);
                $form_data_result = maybe_unserialize($form[0]->form_data);

                global $wpdb;
                $table_form = LFB_FORM_FIELD_TBL;
                $prepare_9 = $wpdb->prepare("SELECT * FROM $table_form WHERE id = %d LIMIT 1", $results->form_id);
                $posts = $th_save_db->lfb_get_form_content($prepare_9);
                if ($posts) {
                    $followup_text = maybe_unserialize($posts[0]->followup_setting);;
                }

                $table_row .= '<td>'.$status.'</td>';

                $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

                $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
                    <div class="lfb-popup-leads"><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
                    </div>
                    </div>';

                $table_body .= '<tr>'. $table_row .'</tr>';
            }

            if(wp_is_mobile()){
                $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat " style="width: 100%" id="show-leads-table" >
                <thead><tr>'.$tableHead.'<th>Product</th><th>Value</th><th>Affiliate</th><th>Date</th>'.$table_head.'<th>Status</th></tr></thead>';
            } else {
                $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat " style="width: 100%" id="show-leads-table" >
                <thead><tr>'.$tableHead.'<th>Product</th><th>Value</th><th>Affiliate</th><th>Date</th>'.$table_head.'<th>Status</th></tr></thead>';
            }

            echo wp_kses($thHead. $table_body.'</table>'.$popupTab,$this->expanded_alowed_tags());

         } else {
            ?>
            <div class="wrap" id="form-leads-show">
            <?php
                esc_html_e("No leads founds..!","sejoli-lead-form")
            ?>
            </div>
            <?php
        }
    }

    /**
     * Show All Leads
     * @since   1.0.0
     */
    function lfb_show_form_leads_datewise($form_id,$leadtype){
        $th_save_db = new LFB_SAVE_DB();

        $getArray =  $th_save_db->lfb_get_all_view_date_leads_db($form_id,$leadtype);
        $nonce = wp_create_nonce( 'lfb-nonce-rm' );

        $posts          = $getArray['posts'];
        $rows           = $getArray['rows'];
        $limit          = $getArray['limit'];
        $fieldData       = $getArray['fieldId'];
        $sn_counter     = 0;
        $detail_view    = '';
        $id             = $headcount = 1;
        $fieldIdNew     = array();

        $tableHead  = '';
        foreach ($fieldData as $fieldkey => $fieldvalue) {
            // Html Field removed
            $pos = strpos($fieldkey, 'htmlfield_');
            if ($pos !== false) {
                continue;
            }

            if($headcount < 6){
                $tableHead  .='<th>' . $fieldvalue . '</th>';
            }
            $fieldIdNew[] = $fieldkey;
            $headcount++;
        }

        if (!empty($posts)) {
            $entry_counter = 0;
            $value1 = 0;
            $table_head = '';
            $table_body = '';
            $popupTab   = '';
           
            if($headcount >= 6){
                $table_head .='<th> . . . </th><th> <input type="button" onclick="show_all_leads(' . $id . ',' . $form_id . ')" value="'.esc_html__('Show all Columns','sejoli-lead-form').'"></th>';
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
                $lead_date = date("j M Y", strtotime($results->date));
                $get_status = $results->status;
                if ($get_status === "lead") {
                    $status = '<button type="submit" class="button button-small button-status-lead" data-lead-id="'.$lead_id.'">'. __('Lead', 'sejoli-lead-form') .' </button>';
                } else {
                    $status = '<a href="#" class="button button-small button-status-customer">'. __('Customer', 'sejoli-lead-form') .' </a>';
                }
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

                global $wpdb;
                $table_form = LFB_FORM_FIELD_TBL;
                $prepare_9 = $wpdb->prepare("SELECT * FROM $table_form WHERE id = %d LIMIT 1", $results->form_id);
                $posts = $th_save_db->lfb_get_form_content($prepare_9);
                if ($posts) {
                    $followup_text = maybe_unserialize($posts[0]->followup_setting);;
                }

                $text_follow = '';
                foreach ($form_data_result as $results) {
                    $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
                    if ( $type === 'phonenumber' ) {
                        $field_id = $results['field_id'];
                        $phone_number = isset($form_data['phonenumber_'.$field_id]) ? $this->phone_number_format($form_data['phonenumber_'.$field_id]) : '';
                        if ( wp_is_mobile() ) :
                            $table_row .= '<td><a target="_blank" class="lead-followup-wa" href="https://wa.me/'.$phone_number  . '?text='. $followup_text .'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
                        else :
                            $table_row .= '<td><a target="_blank" class="lead-followup-wa" href="https://api.whatsapp.com/send?phone='.$phone_number.'&text='.$followup_text.'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
                        endif;
                        $text_follow = "Follow Up";
                    }
                }

                $table_row .= '<td>'.$status.'</td>';

                $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

                $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
                    <div class="lfb-popup-leads"><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
                    </div>
                    </div>';

                $table_body .= '<tr><td><span class="lead-count"><a href="#lf-openModal-' . $lead_id . '" title="View Detail">#' . $sn_counter . '</a></td>'. $table_row .'</tr>';
            }

            $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat fixed" id="show-leads-table" >
                <thead><tr><th>ID</th><th>Product</th>'.$tableHead.'<th>Affiliate</th><th>Value</th><th>Date</th>'.$table_head.'<th>'.$text_follow.'</th><th>Status</th></tr></thead>';

            echo wp_kses($thHead. $table_body.'</table>'.$popupTab,$this->expanded_alowed_tags());
        } else {
            ?> <div class="wrap" id="form-leads-show"><?php
            esc_html_e("No leads founds..!","sejoli-lead-form");
            ?> </div> <?php
        }
    }
    
}
