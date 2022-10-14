<?php sejoli_header(); ?>

<h2 class="ui header"><?php _e('Manajemen Data', 'sejoli'); ?></h2>

<?php
    $th_show_forms = new LFB_Show_Leads();
    $th_show_forms->lfb_show_form_leads_by_affiliate();
?>

<?php sejoli_footer();
