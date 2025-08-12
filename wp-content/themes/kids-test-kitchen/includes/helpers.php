<?php

/**
 * Return Link to Edit Post screen
 */
function crb_get_post_edit_link( $post_id, $custom_name = '' ) {
	$name = !empty( $custom_name ) ? $custom_name : get_the_title( $post_id );

	// get_edit_post_link cannot be used, since during Cron the user is not logged in.
	$link = admin_url( sprintf( 'post.php?post=%d&amp;action=edit', $post_id ) );

	return sprintf( '<a href="%s">%s</a>', $link, $name );
}

/**
 * Return Link to Edit User screen
 */
function crb_get_user_edit_link( $user_id, $custom_name = '' ) {
	if ( empty( $user_id ) ) {
		return;
	}

	$user_obj = get_user_by( 'ID', $user_id );
	if ( empty( $user_obj ) ) {
		return;
	}

	$name = !empty( $custom_name ) ? $custom_name : $user_obj->data->display_name;

	// get_edit_user_link cannot be used, since during Cron the user is not logged in
	$link = admin_url( sprintf( 'user-edit.php?user_id=%s', $user_id ) );

	return sprintf( '<a href="%s">%s</a>', $link, $name );
}

/**
 * Clone of default WP function with proper data validation and skipping invalid entries
 */
function crb_wp_list_pluck( $list, $field, $index_key = null ) {
	if ( ! $index_key ) {
		/*
		 * This is simple. Could at some point wrap array_column()
		 * if we knew we had an array of arrays.
		 */
		foreach ( $list as $key => $value ) {
			if ( is_object( $value ) ) {
				if ( !isset( $value->$field ) ) {
					continue;
				}

				$list[ $key ] = $value->$field;
			} else {
				if ( !isset( $value[ $field ] ) ) {
					continue;
				}

				$list[ $key ] = $value[ $field ];
			}
		}
		return $list;
	}

	/*
	 * When index_key is not set for a particular item, push the value
	 * to the end of the stack. This is how array_column() behaves.
	 */
	$newlist = array();
	foreach ( $list as $value ) {
		if ( is_object( $value ) ) {
			if ( !isset( $value->$field ) ) {
				continue;
			}

			if ( isset( $value->$index_key ) ) {
				$newlist[ $value->$index_key ] = $value->$field;
			} else {
				$newlist[] = $value->$field;
			}
		} else {
			if ( !isset( $value[ $field ] ) ) {
				continue;
			}

			if ( isset( $value[ $index_key ] ) ) {
				$newlist[ $value[ $index_key ] ] = $value[ $field ];
			} else {
				$newlist[] = $value[ $field ];
			}
		}
	}

	return $newlist;
}

function crb_date_sort($a1, $a2) {
	$v1 = strtotime($a1['date']);
	$v2 = strtotime($a2['date']);

	return $v1 - $v2; // $v2 - $v1 to reverse direction
}

function convert_24h_to_12_time( $time_24h ) {
	$time_12h = date( 'h:i A', strtotime( $time_24h . ' 01/01/1970' ) );

	return $time_12h;
}