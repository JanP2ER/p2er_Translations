<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<p <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo _p2er_translation('id04',"FR", "Ich wurde automatisch übersetzt."); ?>
	<br/>
	<?php esc_html_e( 'P2ER Translation Example – hello from a dynamic block! yo', 'p2er-example' ); ?>
</p>
