<?php
/**
 * Markdown Formatter.
 *
 * @package Air_WP_Sync_Pro
 */

namespace Air_WP_Sync_Pro;

/**
 * Class Air_WP_Sync_Markdown_Formatter
 */
class Air_WP_Sync_Markdown_Formatter {
	/**
	 * Parsedown instance
	 *
	 * @var Parsedown
	 */
	protected $parsedown;

	/**
	 * Constructor.
	 *
	 * @param Parsedown $parsedown Parsedown instance.
	 */
	public function __construct( $parsedown ) {
		$this->parsedown = $parsedown;
	}

	/**
	 * Convert Markdown to HTML
	 *
	 * @param string $value Markdown text.
	 *
	 * @return string
	 */
	public function format( $value ) {
		// Fix double backslash escaping.
		$value = str_replace( '\_', '_', $value );
		$html  = $this->parsedown->setBreaksEnabled( true )->text( $value );
		return $html;
	}
}
