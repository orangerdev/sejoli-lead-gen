<?php ob_start();

echo '<div class="leadform-show-form-'.$this_form_id.' leadform-show-form '.$this_form_size.' lf-form-default leadform-lite"><div class="lead-head"></div>'.$captcha_script.'
                <form action="" method="post" class="lead-form-front" id="form_' . $this_form_id . '" enctype="multipart/form-data">
                '. $show_title . $form_elemets . '<div class="lf-form-panel">' . $submit_button . '</div>
                <div class="captcha-field-area" id="captcha-field-area"></div>
                <input type="hidden" class="hidden_field" name="hidden_field" value="' . $this_form_id . '"/>
                <input type="hidden" class="form_title" name="form_title" value="' . $form_title . '"/>
                <input type="hidden" class="product_id" name="product" value="' . $form_product . '"/>
                <input type="hidden" class="affiliate_id" name="affiliate_id" value="' . $affiliate_id . '"/>
                <input type="hidden" class="this_form_captcha_status" value="' . $captcha_status . '"/>
                <div class="leadform-show-loading front-loading leadform-show-message-form-'.$this_form_id.'" >
                </div>
                <div class="lf-loading">
                    <div class="spinner" id="loading_image" style="display: none;" wppb-add-style="display:none;">
                        <div class="bounce1"></div>
                        <div class="bounce2"></div>
                        <div class="bounce3"></div>
                    </div>
                </div>
                </form>
                <p wppb-add-style="display:none;" style="display:none;" class="errormsg_'.intval($this_form_id).' errormsg">'.esc_html($error_msg).'</p>
                <p wppb-add-style="display:none;" style="display:none;" redirect="'.esc_url($redirect_url).'" class="infomsg_'.intval($this_form_id).' infomsg">'.esc_html($success_msg).'</p>
                <p wppb-add-style="display:none;" style="display:none;" redirect="'.esc_url($redirect_url).'" class="successmsg_'.intval($this_form_id).' successmsg">'.esc_html($success_msg).'</p>'.$show_affiliate;

$html = ob_get_contents();
ob_end_clean();
?>  