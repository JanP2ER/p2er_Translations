<?php
/**
 * Plugin Name:       P2ER Translation Example
 * Plugin URI:        https://www.p2er.com
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Jan Struck, P2ER GmbH
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       p2er-example
 *
 * @package           p2er
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function p2er_example_p2er_example_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'p2er_example_p2er_example_block_init' );
