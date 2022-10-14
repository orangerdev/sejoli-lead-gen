<?php sejoli_header(); ?>

<h2 class="ui header"><?php _e('Link Affiliasi', 'sejoli-lead-form'); ?></h2>

<form id="affiliate-link-generator" class="ui form">
    <div class="ui fluid action input">
        <select id="lead_form_id" name="lead_form_id" class="ui fluid dropdown">
            <option value=""><?php _e( 'Tunggu sebentar... kami sedang mengambil semua data form lead', 'sejoli-lead-form' ); ?></option>
        </select>
        <button id="lead-affiliate-link-generator-button" class="ui primary button">
            <?php _e( 'Generate', 'sejoli-lead-form' ); ?>
        </button>
    </div>
</form><br>
<div id="affiliate-link-holder">
    <div class="ui info message"><?php _e( 'Silahkan pilih Form Lead', 'sejoli-lead-form' ); ?></div>
</div>

<script id="affiliate-link-tmpl" type="text/x-jsrender">
{{props data}}
    <div class='field'>
        <label for="aff-link-{{:key}}"><b>{{:prop.label}}</b></label>
        <p>{{:prop.description}}</p>
        <div class="ui fluid action input">
            <input id="aff-link-{{:key}}" name="aff-link-{{:key}}" type="text" value="{{:prop.affiliate_link}}" readonly>
            <button class="ui teal right labeled icon button copy-btn" data-clipboard-target="#aff-link-{{:key}}"><i class="copy icon"></i> <?php _e( 'Copy', 'sejoli-lead-form' ); ?></button>
        </div>
    </div>
{{/props}}
</script>
<script>
(function( $ ) {
    'use strict';
    $(document).ready(function(){
       
        // $(document).on("click",'#affiliate-link-generator-button', function(e){
        //     e.preventDefault();
        //     $('#param-platform').val('').trigger('change');
        //     $('#aff-link-parameter').trigger('reset').hide();
        //     if ( $('#product_id').val() !== '' ) {
                
        //         $.ajax({
        //             url : sejoli_member_area.ajaxurl,
        //             method: 'POST',
        //             dataType : 'json',
        //             data : {
        //                 product_id : $('#product_id').val(),
        //                 action: 'sejoli-product-affiliate-link-list',
        //                 nonce : sejoli_member_area.affiliate.link.nonce,
        //             },
        //             beforeSend : function() {
        //                 sejoli.block();
        //             },
        //             success : function(data) {
        //                 sejoli.unblock();
        //                 if ( !$.isEmptyObject( data ) ) {
        //                     var template = $.templates("#affiliate-link-tmpl");
        //                     var htmlOutput = template.render({'data':data});
        //                     $("#affiliate-link-holder").html(htmlOutput);
        //                     $('#aff-link-parameter').show();
        //                 } else {
        //                     $('#affiliate-link-holder').html('<div class="ui red message">Data not found!</div>');
        //                 }
        //             }
        //         });

        //     } else {
        //         $('#affiliate-link-holder').html('<div class="ui red message">Please select a product</div>');
        //     }
        // });

        // $(document).on('submit','#aff-link-parameter',function(e){
        //     e.preventDefault();

        //     var product_id = $('#product_id').val();

        //     if ( product_id === '' ) {
        //         alert('Silahkan generate produk terlebih dulu');
        //     } else {

        //         var param_platform = sejoli_sanitize_title($('#param-platform').val()),
        //             param_id = sejoli_sanitize_title($('#param-id').val()),
        //             param_coupon = $('#param-coupon').val();

        //         $('#affiliate-link-holder input').each(function(){

        //             var link = $(this).val(),
        //                 link_arr = link.split("?"),
        //                 link_new = link_arr[0];


        //             if( param_platform && param_id ) {

        //                 var separator = link_new.indexOf('?') !== -1 ? '&' : '?';
        //                 link_new += separator + 'utm_source=' + param_platform + '&' + 'utm_media=' + param_id;

        //             }

        //             if( param_coupon) {
        //                 var separator = link_new.indexOf('?') !== -1 ? '&' : '?';
        //                 link_new += separator + 'coupon=' + param_coupon;
        //             }

        //             $(this).val(link_new);

        //         });
        //     }
        // });

    });
})( jQuery );
</script>

<?php sejoli_footer();
