<?php
if ( empty( $name ) || empty( $site_name ) || empty( $due_date ) || empty( $page_list_table ) ) {
	return false;
}
?>
<p>
	<?php printf( __( 'Greetings, %s!', 'cornell/governance' ), $name ) ?>
</p>
<p>
	<?php printf( __( 'You have upcoming page review tasks to complete for the %s website.', 'cornell/governance' ), $site_name ) ?>
</p>
<p>
	<?php printf( __( 'Following is a list of the pages that need to be reviewed before %s:', 'cornell/governance' ), $due_date ) ?>
</p>

<?php
	echo $page_list_table;
?>

<p>
	<?php _e( 'Thank you for your attention to this matter.', 'cornell/governance' ) ?>
</p>
