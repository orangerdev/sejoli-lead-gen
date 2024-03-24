<?php ob_start();

foreach ($fieldData as $fieldkey => $fieldvalue) {
    // Html Field removed
    $pos = strpos($fieldkey, 'htmlfield_');
    if ($pos !== false) {
        continue;
    }

    $tableHead  .= '<th>' . $fieldvalue . '</th>';

    $leadscount =  $headcount;

    $fieldIdNew[] = $fieldkey;
    $headcount++;

    // } else{ break; }
}

if (!empty($posts)) {
    $entry_counter = 0;
    $table_body = '';
    $table_head = '';
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
            $status = '<button type="submit" class="button button-small button-status-lead">'. __('Lead', 'sejoli-lead-form') .' </button>';
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

        if(wp_get_referer() !== home_url('member-area/lead-entries/')) {
            $text_follow = '';
            foreach ($form_data_result as $results) {
                $type = isset($results['field_type']['type']) ? $results['field_type']['type'] : '';
                if ( $type === 'phonenumber' ) {
                    $field_id = $results['field_id'];
                    $phone_number = isset($form_data['phonenumber_'.$field_id]) ? phone_number_format($form_data['phonenumber_'.$field_id]) : '';
                    if ( wp_is_mobile() ) :
                        $table_row .= '<td><a target="_blank" class="lead-followup-wa" href="https://wa.me/'.$phone_number  . '?text='. $followup_text .'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
                    else :
                        $table_row .= '<td><a target="_blank" class="lead-followup-wa" href="https://api.whatsapp.com/send?phone='.$phone_number.'&text='.$followup_text.'"><i class="fa fa-whatsapp" aria-hidden="true" title="Follow Up via WhatsApp"></i></a></td>';
                    endif;
                    $text_follow = "Follow Up";
                }
            }
        }

        $table_row .= '<td>'.$status.'</td>';

        $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

        $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
            <div class="lfb-popup-leads"><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
            </div>
            </div>';

        $table_body .= '<tr>'. $table_row .'</tr>';
    }

    if(wp_get_referer() === home_url('member-area/lead-entries/')) {
        if(wp_is_mobile()){
            $thHead = '<thead><tr>'.$tableHead.'<th>Product</th><th>Value</th><th>Affiliate</th><th>Date</th>'.$table_head.'<th>Status</th></tr></thead>';
        } else {
            $thHead = '<thead><tr>'.$tableHead.'<th>Product</th><th>Value</th><th>Affiliate</th><th>Date</th>'.$table_head.'<th>Status</th></tr></thead>';
        }
    } else {
        if(wp_is_mobile()){
            $thHead = '<thead><tr>'.$tableHead.'<th>Product</th><th>Value</th><th>Affiliate</th><th>Date</th>'.$table_head.'<th>'.$text_follow.'</th><th>Status</th></tr></thead>';
        } else {
            $thHead = '<thead><tr>'.$tableHead.'<th>Product</th><th>Value</th><th>Affiliate</th><th>Date</th>'.$table_head.'<th>'.$text_follow.'</th><th>Status</th></tr></thead>';
        }
    }
    echo wp_kses($thHead . $table_body . '</table>', $showLeadsObj->expanded_alowed_tags());

    
} else {
    // esc_html_e('No leads founds..!', 'sejoli-lead-form');
}

$html = ob_get_contents();
ob_end_clean();
?>  