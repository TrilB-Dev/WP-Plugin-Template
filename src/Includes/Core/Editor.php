<?php
/**
 * Editor class for managing the editing and saving of PluginName pages.
 *
 * @package PluginName\Includes\Core
 */
namespace PluginName\Includes\Core;

use PluginName\Includes\Functions\Helpers\SanitizationHelper;
use PluginName\Includes\Functions\Helpers\FormFieldHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Editor {
	public static function save_page( int $page_id, ?\WP_Post $page = null ): bool {
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) || 'save_pluginname_page' !== ( $_POST['pluginname_action'] ?? '' ) || ! check_admin_referer( 'pluginname_save_pluginname_page', 'pluginname_save_pluginname_page_nonce' ) ) {
			return false;
		}
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page_id && ! current_user_can( 'pluginname_page_create' ) ) {
			return false;
		}
		if ( $page_id && ( ! $page || PostType::PLUGINNAME !== $page->post_type || ! current_user_can( 'pluginname_page_edit' ) || ( (int) $page->post_author !== get_current_user_id() && ! current_user_can( 'pluginname_page_edit_others' ) ) || ( 'publish' === $page->post_status && ! current_user_can( 'pluginname_page_edit_published' ) ) ) ) {
			return false;
		}

		$input = wp_unslash( $_POST['pluginname_page'] ?? array() );
		$input = is_array( $input ) ? $input : array();
		$title = SanitizationHelper::text( $input['title'] ?? '' );
		if ( '' === $title ) {
			return false;
		}

		if ( ! current_user_can( 'pluginname_page_publish' ) ) {
			return false;
		}

		$post_id = wp_insert_post(
			array(
				'ID'           => $page_id,
				'post_type'    => PostType::PLUGINNAME,
				'post_title'   => $title,
				'post_content' => wp_kses_post( (string) ( $input['content'] ?? '' ) ),
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		update_post_meta( $post_id, '_pluginname_page_id', $page_id );
		return true;
	}

	public static function render_pluginname_page_form( ?\WP_Post $page = null ): void {
		?>
		<form method="post" class="card shadow-sm">
			<?php wp_nonce_field( 'pluginname_save_pluginname_page', 'pluginname_save_pluginname_page_nonce' ); ?>
			<input type="hidden" name="pluginname_action" value="save_pluginname_page">
			<div class="card-body"><div class="mb-3"><label class="form-label" for="pluginname-page-title"><?php esc_html_e( 'Page Title', 'pluginname' ); ?></label><input class="form-control" id="pluginname-page-title" name="pluginname_page[title]" value="<?php echo esc_attr( $page ? $page->post_title : '' ); ?>" required></div><?php FormFieldHelper::tinymce( 'pluginname-page-content', 'pluginname_page[content]', __( 'Page Content', 'pluginname' ), $page ? $page->post_content : '', 14, true ); ?></div>
			<div class="card-footer d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname-manage' ) ); ?>"><?php esc_html_e( 'Cancel', 'pluginname' ); ?></a><button class="btn btn-primary" type="submit"><?php echo esc_html( $page ? __( 'Save Page', 'pluginname' ) : __( 'Create Page', 'pluginname' ) ); ?></button></div>
		</form>
		<?php
	}
}



