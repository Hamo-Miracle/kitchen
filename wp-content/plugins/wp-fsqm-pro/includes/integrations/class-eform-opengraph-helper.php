<?php
/**
 * Helper class for handling opengraph related stuff
 *
 * @package eForm
 * @subpackage Integration\OpenGraph
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_OpenGraph_Helper {
	/**
	 * Print settings for the admin panel
	 *
	 * @param      string               $name_prefix  The name prefix
	 * @param      IPT_Plugin_UIF_Admin $ui           The user interface
	 * @param      array                $op           The options
	 * @codeCoverageIgnore
	 */
	public static function admin_settings( $name_prefix, $ui, $op ) {
		// Items
		$items = array();
		// Open Graph Items
		$items[] = array(
			'name' => $name_prefix . '[title]',
			'label' => __( 'OpenGraph Title', 'ipt_fsqm' ),
			'description' => __( 'output for <code>&lt;og:title&gt;</code>', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[title]', $op['title'], __( 'Page title', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the og:title value. %NAME% is replaced by form name.', 'ipt_fsqm' ),
		);
		$items[] = array(
			'name' => $name_prefix . '[type]',
			'label' => __( 'OpenGraph Type', 'ipt_fsqm' ),
			'description' => __( 'output for <code>&lt;og:type&gt;</code>', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[type]', $op['type'], __( 'Page type', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the og:type value.', 'ipt_fsqm' ),
		);
		$items[] = array(
			'name' => $name_prefix . '[image]',
			'label' => __( 'OpenGraph Image', 'ipt_fsqm' ),
			'ui' => 'upload',
			'param' => array( $name_prefix . '[image]', $op['image'] ),
			'help' => __( 'Set the feature image for open graph. Recommended size is 1200X613 pixels.', 'ipt_fsqm' ),
		);
		$items[] = array(
			'name' => $name_prefix . '[url]',
			'label' => __( 'OpenGraph Canonical URL', 'ipt_fsqm' ),
			'description' => __( 'output for <code>&lt;og:url&gt;</code>', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[url]', $op['url'], __( 'Page url', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the og:url value. %SELF% is replaced by current URL.', 'ipt_fsqm' ),
		);
		$items[] = array(
			'name' => $name_prefix . '[description]',
			'label' => __( 'OpenGraph Description', 'ipt_fsqm' ),
			'description' => __( 'output for <code>&lt;og:description&gt;</code>', 'ipt_fsqm' ),
			'ui' => 'textarea',
			'param' => array( $name_prefix . '[description]', $op['description'], __( 'Page description', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the og:description value.', 'ipt_fsqm' ),
		);
		$items[] = array(
			'name' => $name_prefix . '[site_name]',
			'label' => __( 'OpenGraph Site Name', 'ipt_fsqm' ),
			'description' => __( 'output for <code>&lt;og:site_name&gt;</code>', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[site_name]', $op['site_name'], __( 'Page site_name', 'ipt_fsqm' ) ),
			'help' => __( 'Enter the og:site_name value.', 'ipt_fsqm' ),
		);
		// Facebook app id
		$items[] = [
			'name' => $name_prefix . '[fb_app_id]',
			'label' => __( 'Facebook App ID', 'ipt_fsqm' ),
			'description' => __( 'output for <code>&lt;meta property=&quot;fb:app_id&quot;&gt;', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => [ $name_prefix . '[fb_app_id]', $op['fb_app_id'], __( 'fb app id', 'ipt_fsqm' ) ],
			'help' => __( 'Facebook App ID provides access to insights for the specified app.', 'ipt_fsqm' ),
		];
		// Twitter specific items
		$items[] = array(
			'name' => $name_prefix . '[twitter][card]',
			'label' => __( 'Twitter Card Type', 'ipt_fsqm' ),
			'description' => __( 'output for <code>twitter:card</code>', 'ipt_fsqm' ),
			'ui' => 'select',
			'param' => array( $name_prefix . '[twitter][card]', self::available_twitter_cards(), $op['twitter']['card'] ),
			'help' => __( 'Enter twitter card type for this page.', 'ipt_fsqm' ),
		);
		$items[] = array(
			'name' => $name_prefix . '[twitter][site]',
			'label' => __( 'Twitter Website Creator @username', 'ipt_fsqm' ),
			'description' => __( 'write with <code>@</code>. For example <code>@WPQuark</code>.', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[twitter][site]', $op['twitter']['site'], __( '@username', 'ipt_fsqm' ) ),
			'help' => __( 'Enter twitter card type for this page.', 'ipt_fsqm' ),
		);
		$items[] = array(
			'name' => $name_prefix . '[twitter][creator]',
			'label' => __( 'Twitter Content Creator @username', 'ipt_fsqm' ),
			'description' => __( 'write with <code>@</code>. For example <code>@WPQuark</code>.', 'ipt_fsqm' ),
			'ui' => 'text',
			'param' => array( $name_prefix . '[twitter][creator]', $op['twitter']['creator'], __( '@username', 'ipt_fsqm' ) ),
			'help' => __( 'Enter twitter content creator for this page.', 'ipt_fsqm' ),
		);
		// Fire
		$ui->form_table( $items );
	}

	/**
	 * Get available twitter cards and value
	 *
	 * @return     array  Available twitter cards
	 */
	public static function available_twitter_cards() {
		return array(
			'summary' => __( 'Summary', 'ipt_fsqm' ),
			'summary_large_image' => __( 'Summary with large image', 'ipt_fsqm' ),
			'app' => __( 'Application', 'ipt_fsqm' ),
			'player' => __( 'Player', 'ipt_fsqm' ),
		);
	}

	/**
	 * Output opengraph and twitter meta tags to be used in the standalone form
	 *
	 * @param      IPT_FSQM_Form_Elements_Front $form   The form
	 */
	public static function standalone_output( $form ) {
		// Get default ones
		$default_ogs = array(
			'title',
			'type',
			'image',
			'url',
			'description',
			'site_name',
		);
		$default_twitters = array(
			'card',
			'site',
			'creator',
		);
		// Get the parsed options
		$op = self::parse_template( $form );
		// Print them if present in $op
		?>
<!-- OpenGraph Optimization by - eForm https://eform.live -->
		<?php foreach ( $default_ogs as $og_key ) : ?>
			<?php if ( isset( $op[ $og_key ] ) ) : ?>
				<meta property="og:<?php echo esc_attr( $og_key ); ?>" content="<?php echo esc_attr( $op[ $og_key ] ); ?>" />
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( isset( $op['fb_app_id'] ) && '' !== $op['fb_app_id'] ) : ?>
			<meta property="fb:app_id" content="<?php echo esc_attr( $op['fb_app_id'] ); ?>" />
		<?php endif; ?>
		<?php foreach ( $default_twitters as $tw_key ) : ?>
			<?php if ( isset( $op['twitter'][ $tw_key ] ) ) : ?>
				<meta name="twitter:<?php echo esc_attr( $tw_key ); ?>" content="<?php echo esc_attr( $op['twitter'][ $tw_key ] ); ?>" />
			<?php endif; ?>
		<?php endforeach; ?>
<!-- #end OpenGraph Optimization by - eForm -->
		<?php
	}

	/**
	 * Parse the opengraph settings from a form and normalize the templates
	 *
	 * This replaces %NAME% and %SELF% with corresponding values
	 *
	 * @param      IPT_FSQM_Form_Elements_Front $form   The form
	 *
	 * @return     array                         Associative array of opengraph option parsed from the form
	 */
	public static function parse_template( $form ) {
		$replacements = array(
			'%NAME%' => $form->name,
			'%SELF%' => IPT_FSQM_Form_Elements_Static::get_current_url(),
		);
		return self::replace_template( $form->settings['opengraph'], $replacements );
	}

	/**
	 * Recursively replace the template tags from an array
	 *
	 * @param      array $array         The array
	 * @param      array $replacements  The replacements
	 *
	 * @return     array  Parsed array with replacements
	 */
	protected static function replace_template( array $array, $replacements ) {
		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) ) {
				$array[ $key ] = self::replace_template( $val, $replacements );
			} else {
				$array[ $key ] = str_replace( array_keys( $replacements ), array_values( $replacements ), $val );
			}
		}
		return $array;
	}
}
