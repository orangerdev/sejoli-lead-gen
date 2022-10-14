<?php
if (!function_exists('themehunk_admin_menu')) {
    include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
    define('THEMEHUNK_PURL', plugin_dir_url(__FILE__));
    define('THEMEHUNK_PDIR', plugin_dir_path(__FILE__));
    add_action('admin_menu',  'themehunk_admin_menu');
    add_action( 'admin_enqueue_scripts', 'admin_scripts');

    function themehunk_admin_menu(){
        add_menu_page(__('Lead Forms', 'lead-form-builder'), __('Lead Forms', 'lead-form-builder'), 'manage_options', 'lead-forms', 'lfb_lead_form_page',  THEMEHUNK_PURL . '/th-option/assets/images/icon.png', 59);
    }

    function themehunk_plugins(){
        include_once THEMEHUNK_PDIR . "/th-option/th-option.php";
        $obj = new themehunk_plugin_option();
        $obj->tab_page();
    }

    // function lfb_lead_form_page() {
    //     if (isset($_GET['action']) && isset($_GET['formid'])) {
    //         $form_action = sanitize_text_field($_GET['action']);
    //         $this_form_id = intval($_GET['formid']);
    //         if ($form_action == 'delete') {
    //             $page_id =1;
    //             if (isset($_GET['page_id'])) {
    //             $page_id = intval($_GET['page_id']);
    //             }
    //             $th_edit_del_form = new LFB_EDIT_DEL_FORM();
    //             $th_edit_del_form->lfb_delete_form_content($form_action, $this_form_id,$page_id);
    //         }
    //         if ($form_action == 'show' && isset($_GET['formid'])) {
    //                 $fid = intval($_GET['formid']); 
    //                 echo "<div class='lfb-show'><h1>". esc_html('Lead Form Preview Page')."</h1>";
    //             echo do_shortcode('[lead-form form-id="'.$fid.'" title=Contact Us]');

    //             echo "<div>";
    //         }
    //         if ($form_action == 'today_leads') {
    //             $th_show_today_leads = new LFB_Show_Leads();
    //             $th_show_today_leads->lfb_show_form_leads_datewise($this_form_id,"today_leads");
    //         }
    //         if ($form_action == 'total_leads') {
    //             $th_show_all_leads = new LFB_Show_Leads();
    //             $th_show_all_leads->lfb_show_form_leads_datewise($this_form_id,"total_leads");
    //         }
    //     } else {
    //         $th_show_forms = new LFB_SHOW_FORMS();
    //         $page_id =1;
    //         if (isset($_GET['page_id'])) {
    //         $page_id = intval($_GET['page_id']);
    //         }
    //         $th_show_forms->lfb_show_all_forms($page_id);
    //     }
    // }

    function admin_scripts( $hook ) {
        if ($hook === 'toplevel_page_themehunk-plugins'){
            wp_enqueue_style( 'themehunk-plugin-css', THEMEHUNK_PURL . '/th-option/assets/css/started.css' );
            wp_enqueue_script('themehunk-plugin-js', THEMEHUNK_PURL . '/th-option/assets/js/th-options.js',array( 'jquery', 'updates' ),'1', true);
        }
    }
}