<?php
if ( empty( $name ) || empty( $site_name ) || empty( $due_date ) || empty( $page_list_table ) ) {
	return false;
}
?>
	<?php printf( __( 'Greetings, %s!', 'cornell/governance' ), $name ) ?>

	<?php printf( __( 'You have upcoming page review tasks to complete for the %s website.', 'cornell/governance' ), $site_name ) ?>

	<?php printf( __( 'Following is a list of the pages that need to be reviewed before %s:', 'cornell/governance' ), $due_date ) ?>


<?php
echo $page_list_table;
?>

	<?php _e( 'Thank you for your attention to this matter.', 'cornell/governance' ) ?>
