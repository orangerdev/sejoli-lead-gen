<div class=" lfb-header"> 
	<?php $pm = $fl = $pro = $anf = ""; ?>
	<?php if(isset($_GET['page']) && $_GET['page'] == 'lead-forms'){
		echo '<h2>'.esc_html__('Lead Forms Builder','sejoli-lead-form').' <a href="' . esc_url($lfb_admin_url . 'admin.php?page=add-new-form&_wpnonce='.$this->lfb_show_form_nonce()).'" class="add-new-h2">'.esc_html__("Add New","sejoli-lead-form").'</a></h2>';
		$pm = 'active';
	}elseif(isset($_GET['page']) && $_GET['page'] == 'all-form-entries'){
		echo '<h2>'.esc_html__('Entries','sejoli-lead-form').'</h2>';
		$fl = 'active';

	}elseif(isset($_GET['page']) && $_GET['page'] == 'pro-form-leads'){
		echo '<h2>'.esc_html__('Premium Plugin & Themes','sejoli-lead-form').'</h2>';
		$pro = 'active';

	}elseif(isset($_GET['page']) && $_GET['page'] == 'add-new-form'){
		echo '<h2>'.esc_html__('Form Settings','sejoli-lead-form').'</h2>';
		$anf = 'active';

	}
	?>
	<div class="lfb-cmn-nav">
		<div class="lfb-cmn-nav-item">
			<a class="lfb_icon_button <?php echo $pm; ?>" href="<?php echo admin_url( 'admin.php?page=lead-forms'); ?>">
				<span><?php _e('Form List','sejoli-lead-form'); ?></span>
			</a>
			<?php if(isset($_GET['page']) && $_GET['page'] == 'add-new-form'){ ?>
				<a class="lfb_icon_button <?php echo $anf; ?>" href="<?php echo admin_url( 'admin.php?page=lead-forms'); ?>">
					<span><?php _e('Form Settings','sejoli-lead-form'); ?></span>
				</a> 
			<?php } ?>
			<a class="lfb_icon_button <?php echo $fl; ?>" href="<?php echo admin_url( 'admin.php?page=all-form-entries'); ?>">
				<span><?php _e('Entries','sejoli-lead-form'); ?></span>
			</a>
		</div>	
	</div>
</div>