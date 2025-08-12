<?php
/**
 * IPT FSQM Uploader
 * The main class of uploader
 *
 * Expects to deal with only one element at a time
 * Need to pass both the form id and element id to the constructor
 *
 * @todo #474
 *
 * @author Swashata <swashata@wpquark.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Form\Uploader
 * @codeCoverageIgnore
 */
class IPT_FSQM_Form_Elements_Uploader extends IPT_FSQM_Form_Elements_Base {

	private $element_id = null;

	private $element_settings = null;

	private $element = null;

	private $upload_dir = null;

	private $upload_dir_url = null;

	private $original_upload_dir = null;

	public $unallow_files = array();

	public $error_messages = array();

	private $file_object_count = 0;

	private $active_file_object = array(
		'name' => '',
		'size' => 0,
		'type' => '',
		'new_name' => '',
		'current_ext' => '',
	);

	public function __construct( $form_id, $key ) {

		// Init the unallow files variable
		$this->unallow_files = apply_filters( 'ipt_fsqm_files_blacklist', array(
			'php', 'php3', 'php4', 'phtml', 'exe', 'bin', 'sh', 'bash', 'out', 'sql', 'robots.txt', '.htaccess',
		) );

		// Init the error messages
		$this->error_messages = apply_filters( 'ipt_fsqm_files_error_messages', array(
			1 => __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini', 'ipt_fsqm' ),
			2 => __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'ipt_fsqm' ),
			3 => __( 'The uploaded file was only partially uploaded', 'ipt_fsqm' ),
			4 => __( 'No file was uploaded', 'ipt_fsqm' ),
			6 => __( 'Missing a temporary folder', 'ipt_fsqm' ),
			7 => __( 'Failed to write file to disk', 'ipt_fsqm' ),
			8 => __( 'A PHP extension stopped the file upload', 'ipt_fsqm' ),
			'post_max_size' => __( 'The uploaded file exceeds the post_max_size directive in php.ini', 'ipt_fsqm' ),
			'max_file_size' => __( 'File is too big', 'ipt_fsqm' ),
			'min_file_size' => __( 'File is too small', 'ipt_fsqm' ),
			'accept_file_types' => __( 'Filetype not allowed', 'ipt_fsqm' ),
			'unallow_file_types' => __( 'Filetype is considered dangerous and thereby has been blocked', 'ipt_fsqm' ),
			'max_number_of_files' => __( 'Maximum number of files exceeded', 'ipt_fsqm' ),
			'max_width' => __( 'Image exceeds maximum width', 'ipt_fsqm' ),
			'min_width' => __( 'Image requires a minimum width', 'ipt_fsqm' ),
			'max_height' => __( 'Image exceeds maximum height', 'ipt_fsqm' ),
			'min_height' => __( 'Image requires a minimum height', 'ipt_fsqm' ),
			'abort' => __( 'File upload aborted', 'ipt_fsqm' ),
			'image_resize' => __( 'Failed to resize image', 'ipt_fsqm' ),
			'error' => __( 'The file could not be uploaded. Possibly the file is corrupt or the system prevented a file tampering attack.', 'ipt_fsqm' ),
			'zero_size' => __( 'File is empty. Please upload something more substantial', 'ipt_fsqm' ),
			'no_element' => __( 'Invalid configuration.', 'ipt_fsqm' ),
		) );

		// Check for upload dir
		$this->check_upload_dir();

		// Now call the base class with the form id
		parent::__construct( $form_id );

		if ( null !== $this->form_id ) {
			// Check for a valid element id
			$layout_element = array(
				'm_type' => 'freetype',
				'key' => $key,
			);

			$element = $this->get_element_from_layout( $layout_element );

			if ( ! empty( $element ) ) {
				$this->element_id = $key;
				$this->element = $this->merge_elements( $element, $this->get_element_structure( 'upload' ), true );
				$this->element_settings = $this->element['settings'];
			}
		}
	}

	public function is_valid_element() {
		return $this->element_id !== null;
	}

	public function get_element_settings() {
		return $this->element_settings;
	}

	public function get_uploads( $data_id, $delete = false ) {
		global $wpdb, $ipt_fsqm_info;
		$uploads = array();
		$db_results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['file_table']} WHERE form_id = %d AND element_id = %d AND data_id = %d", $this->form_id, $this->element_id, $data_id ) );

		if ( $db_results && ! empty( $db_results ) ) {
			foreach ( $db_results as $file ) {
				$upload = array(
					'id' => $file->id,
					'form_id' => $file->form_id,
					'data_id' => $file->data_id,
					'element_id' => $file->element_id,
					'media_id' => $file->media_id,
					'name' => $file->name,
					'mime_type' => $file->mime_type,
					'date' => $file->date,
					'filename' => $file->filename,
					'size' => $file->size,
				);

				// Create the delete URL if necessary
				if ( $delete === true ) {
					$upload['delete'] = $this->create_delete_url( $file->id, $file->filename );
				}

				// We are going to reconstruct guid, path and thumb_url
				// So that even if the server location changes, the upload will work as expected
				$uploads[] = array_merge( $upload, $this->get_file_locations( $file->filename ) );
			}
		}

		return $uploads;
	}

	private function get_file_locations( $filename ) {
		$locations = array(
			'guid' => '',
			'path' => '',
			'thumb_url' => '',
			'ext' => '',
		);

		// Check if the file exists
		// If it does, update the guid (url) and path
		$possible_file_path = $this->upload_dir . '/' . $filename;
		if ( file_exists( $possible_file_path ) ) {
			$locations['path'] = $possible_file_path;
			$locations['guid'] = $this->upload_dir_url . '/' . $filename;
			$locations['size'] = filesize( $possible_file_path );
		}

		// Get the extensions and just the file name
		$name_array = explode( '.', $filename );
		$ext = array_pop( $name_array );
		$just_file_name = implode( '.', $name_array );

		// Set the extensions
		$locations['ext'] = $ext;

		// Check for thumbnail
		$possible_thumb_path = $this->upload_dir . '/' . $just_file_name . '-ipt_fsqm_thumb.' . $ext;
		if ( file_exists( $possible_thumb_path ) ) {
			$locations['thumb_url'] = $this->upload_dir_url . '/' . $just_file_name . '-ipt_fsqm_thumb.' . $ext;
		}

		// All checked, now return
		return $locations;
	}

	public function check_upload_dir() {
		if ( $this->upload_dir !== null ) {
			return is_string( $this->upload_dir );
		}

		// The upload directory resides at wp-contents/uploads/fsqm-files
		$wp_upload_dir = wp_upload_dir();
		$fsqm_files_dir = $wp_upload_dir['basedir'] . '/fsqm-files';

		// Sanity check
		if ( ! wp_mkdir_p( $fsqm_files_dir ) ) {
			return false;
		}

		// Set the upload directory
		$this->upload_dir = $fsqm_files_dir;

		// Set the upload directory URL
		$this->upload_dir_url = $wp_upload_dir['baseurl'] . '/fsqm-files';

		// Set the original directory information
		$this->original_upload_dir = $wp_upload_dir;

		return true;
	}

	public function process_file_uploads( $param ) {
		$upload = isset( $_FILES[$param] ) ? $_FILES[ $param ] : null;
		$files = array();

		// Now loop through and try to upload all files
		if ( $upload && is_array( $upload['tmp_name'] ) ) {
			// $param is an array indentifier & $_FILES is multi-dimensional array
			foreach ( $upload['tmp_name'] as $index => $value ) {
				$files[] = $this->process_upload( array(
					'name' => $upload['name'][ $index ],
					'type' => $upload['type'][ $index ],
					'tmp_name' => $upload['tmp_name'][ $index ],
					'error' => $upload['error'][ $index ],
					'size' => $upload['size'][ $index ],
				) );
			}
		} else {
			// $_FILES is an one-dimensional array as $param is a single object indentifier
			$files[] = $this->process_upload( $upload );
		}

		return $files;
	}

	public function process_upload( $file ) {
		$error = $this->validate_upload( $file );

		if ( ! empty( $error ) ) {
			return array(
				'name' => $file['name'],
				'size' => $file['size'],
				'error' => $error,
			);
		}

		$result = $this->move_upload( $file );

		$db_data = $this->finalize_upload( $result );

		if ( is_string( $db_data ) ) {
			return array(
				'name' => $file['name'],
				'size' => $file['size'],
				'error' => $db_data,
			);
		}

		$file_info = wp_check_filetype_and_ext( $db_data['path'], $db_data['filename'] );

		$valid_audio = in_array( strtolower( $file_info['ext'] ), array( 'mp3', 'wav', 'ogg' ) ) ? true : false;
		$valid_video = strtolower( $file_info['ext'] ) == 'mp4' ? true : false;

		return array(
			'id' => $db_data['file_id'],
			'name' => $db_data['name'],
			'size' => $db_data['size'],
			'url' => $db_data['guid'],
			'thumbnailUrl' => $db_data['thumb_url'],
			'deleteUrl' => $this->create_delete_url( $db_data['file_id'], $db_data['filename'] ),
			'deleteType' => 'DELETE',
			'validAudio' => $valid_audio,
			'validVideo' => $valid_video,
			'type' => $db_data['mime_type'],
		);
	}

	private function create_delete_url( $file_id, $filename ) {
		$url = admin_url( 'admin-ajax.php?action=ipt_fsqm_fu_delete&file_id=' . $file_id . '&element_id=' . $this->element_id . '&form_id=' . $this->form_id . '&_wpnonce=' . wp_create_nonce( 'ipt_fsqm_fu_delete_file_' . $file_id ) . '&file=' . $filename );
		return $url;
	}

	private function validate_upload( $file ) {
		// First reset the file object
		$this->active_file_object = array(
			'name' => '',
			'size' => 0,
			'type' => '',
			'new_name' => '',
			'current_ext' => '',
		);

		// Check for generic errors
		if ( $this->element_id === null ) {
			return $this->error_messages['no_element'];
		}
		if ( ! empty( $file['error'] ) ) {
			return $this->error_messages[$file['error']];
		}

		// Check for PHPINI server limitation
		$content_length = $this->fix_integer_overflow( intval(
			$this->get_server_var( 'CONTENT_LENGTH' )
		) );
		$post_max_size = $this->get_config_bytes( ini_get( 'post_max_size' ) );
		if ( $post_max_size && ( $content_length > $post_max_size ) ) {
			return $this->error_messages['post_max_size'];
		}

		// Check the size
		if ( is_uploaded_file( $file['tmp_name'] ) ) {
			$file_size = $this->get_file_size( $file['tmp_name'] );
		} else {
			$file_size = $content_length;
		}

		// Check for settings file size limits
		if ( $file_size == 0 ) {
			return $this->error_message['zero_size'];
		}
		if ( $this->element_settings['max_file_size'] && $file_size > $this->element_settings['max_file_size'] ) {
			return $this->error_messages['max_file_size'];
		}
		if ( $this->element_settings['min_file_size'] && $file_size < $this->element_settings['min_file_size'] ) {
			return $this->error_messages['min_file_size'];
		}

		// Check for maximum number of files
		if ( $this->element_settings['max_number_of_files'] > 0 && $this->file_object_count > $this->element_settings['max_number_of_files'] ) {
			return $this->error_messages['max_number_of_files'];
		}

		// Now check for filetype and extension
		$file_type = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );

		$accepted_file_types = explode( ',', $this->element_settings['accept_file_types'] );
		$accepted_file_types = array_map( 'strtolower', $accepted_file_types );

		if ( ! in_array( strtolower( $file_type['ext'] ), $accepted_file_types ) ) {
			return $this->error_messages['accept_file_types'];
		}
		if ( in_array( $file_type['ext'], $this->unallow_files ) ) {
			return $this->error_messages['unallow_file_types'];
		}

		// At this point everything is working fine
		$name_array = explode( '.', $file['name'] );
		$current_ext = array_pop( $name_array );
		$new_name = implode( '.', $name_array );
		$this->active_file_object = array(
			'name' => $file['name'],
			'type' => $file_type['type'],
			'size' => $file_size,
			'new_name' => $new_name,
			'current_ext' => $current_ext,
		);
		return 0;
	}

	private function move_upload( $file ) {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		// Filter the wp_upload_dir
		add_filter( 'upload_dir', array( $this, 'filter_wp_upload_dir' ), 99 );

		// Sanitize the filename, after prefixing it with a random string to avoid conflict and avoid being guessed.
		$file['name'] = sanitize_file_name( IPT_FSQM_Form_Elements_Static::generate_random_string() . $file['name'] );

		// Move the files using WP API
		$uploaded_file = wp_handle_upload( $file, array(
			'test_form' => FALSE,
		) );

		// Remove the filter for wp_upload
		remove_filter( 'upload_dir', array( $this, 'filter_wp_upload_dir' ), 99 );

		// Add the modified name to the active_file_object
		if ( is_array( $uploaded_file ) && isset( $uploaded_file['file'] ) ) {
			$new_path = explode( '/', $uploaded_file['file'] );
			$new_name_with_ext = $new_path[ count( $new_path ) - 1 ];
			$new_name_array = explode( '.', $new_name_with_ext );

			$current_ext = array_pop( $new_name_array );
			$new_name = implode( '.', $new_name_array );

			$this->active_file_object['new_name'] = $new_name;
			$this->active_file_object['current_ext'] = $current_ext;
		}

		// Return the result
		return $uploaded_file;
	}

	private function finalize_upload( $data ) {
		// Check for sanity
		if ( $data === false ) {
			return $this->error_messages['error'];
		}

		if ( is_array( $data ) && isset( $data['error'] ) ) {
			return $data['error'];
		}

		// Everything should be good at this point

		// Create thumbnail
		$thumb_url = $this->create_thumbnail( $data );

		// Upload as attachment
		$media_id = $this->wp_media_integration( $data );

		// Init global variables for db operation
		global $wpdb, $ipt_fsqm_info;

		// Insert at the database
		$db_data = array(
			'form_id' => $this->form_id,
			'element_id' => $this->element_id,
			'media_id' => $media_id,
			// Store the original file name, but sanitize it before storing.
			'name' => sanitize_file_name( $this->active_file_object['name'] ),
			'filename' => $this->active_file_object['new_name'] . '.' . $this->active_file_object['current_ext'],
			'mime_type' => $data['type'],
			'size' => $this->active_file_object['size'],
			'guid' => $data['url'],
			'path' => $data['file'],
			'thumb_url' => $thumb_url,
			'date' => current_time( 'mysql' ),
		);
		$wpdb->insert( $ipt_fsqm_info['file_table'], $db_data, '%s' );

		$file_id = $wpdb->insert_id;

		$db_data['file_id'] = $file_id;

		$this->file_object_count++;

		return $db_data;
	}

	private function wp_media_integration( $data ) {
		if ( $this->element_settings['wp_media_integration'] != true ) {
			return 0;
		}

		$filename = $data['file'];
		$attachment = array(
			'post_mime_type' => $data['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit',
			'guid' => $data['url'],
		);

		$attach_id = wp_insert_attachment( $attachment, $data['file'] );

		// Update attachment metadata for images
		if ( in_array( $this->active_file_object['current_ext'], array( 'jpg', 'jpeg', 'png', 'gif' ) ) ) {
			if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
			}
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );
		}

		return $attach_id;
	}

	private function create_thumbnail( $data ) {
		$thumbnail_path = $this->upload_dir . '/' . $this->active_file_object['new_name'] . '-ipt_fsqm_thumb.' . $this->active_file_object['current_ext'];
		$thumbnail_url = $this->original_upload_dir['baseurl'] . '/fsqm-files/' .  $this->active_file_object['new_name'] . '-ipt_fsqm_thumb.' . $this->active_file_object['current_ext'];

		if ( file_exists( $thumbnail_path ) ) {
			return $thumbnail_url;
		}

		$image = wp_get_image_editor( $data['file'] );
		if ( is_wp_error( $image ) ) {
			return '';
		}
		$image->resize( 150, 100, false );
		$image->save( $thumbnail_path );

		return $thumbnail_url;
	}

	public function delete_file( $file_id ) {
		// Init the global vars
		global $wpdb, $ipt_fsqm_info;
		// First get the file_id
		$file_info = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['file_table']} WHERE id = %d AND element_id = %d", $file_id, $this->element_id ) );

		// Check if exists on database
		if ( ! $file_info ) {
			return false;
		}

		// File found, now delete it

		// first remove the wp_media attachment
		if ( $file_info->media_id != 0 ) {
			wp_delete_attachment( $file_info->media_id, true );
		}

		// Now delete the file if it exists
		if ( file_exists( $file_info->path ) ) {
			@unlink( $file_info->path );
		}

		// Delete the thumbnail, if any
		$name_array = explode( '.', $file_info->filename );
		$current_ext = array_pop( $name_array );
		$basename = implode( '.', $name_array );
		$thumbnail_path = $this->upload_dir . '/' . $basename . '-ipt_fsqm_thumb.' . $current_ext;
		if ( file_exists( $thumbnail_path ) ) {
			@unlink( $thumbnail_path );
		}

		// Now remove from the database
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$ipt_fsqm_info['file_table']} WHERE id = %d", $file_id ) );

		return true;
	}


	// Fix for overflowing signed 32 bit integers,
	// works for sizes up to 2^32-1 bytes (4 GiB - 1):
	protected function fix_integer_overflow( $size ) {
		if ( $size < 0 ) {
			$size += 2.0 * (PHP_INT_MAX + 1);
		}
		return $size;
	}

	protected function get_file_size( $file_path, $clear_stat_cache = false ) {
		if ( $clear_stat_cache ) {
			if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
				clearstatcache( true, $file_path );
			} else {
				clearstatcache();
			}
		}
		return $this->fix_integer_overflow( filesize( $file_path ) );
	}

	protected function get_server_var( $id ) {
		return isset( $_SERVER[$id] ) ? $_SERVER[$id] : '';
	}

	protected function get_config_bytes( $val ) {
		$val = trim( $val );
		$last = strtolower( $val[strlen($val)-1] );
		switch( $last ) {
			case 'g':
				$val = floatval( $val ) * 1024;
			case 'm':
				$val = floatval( $val ) * 1024;
			case 'k':
				$val = floatval( $val ) * 1024;
		}
		return $this->fix_integer_overflow( $val );
	}

	public function filter_wp_upload_dir( $upload_dir ) {
		$upload_dir['path'] = $this->upload_dir;
		$upload_dir['url'] = $this->original_upload_dir['baseurl'] . '/fsqm-files';
		$upload_dir['subdir'] = '/fsqm-files';
		return $upload_dir;
	}
}
