<?php

class Api_Org {
  /**
   * Grab latest post title by an author!
   *
   * @param array $data Options for the function.
   * @return string|null Post title for the latest, * or null if none.
   */
  static function get_allowed_users_by_org( $data ) {
  
    $fields = ['id','user_login','display_name'];

    $users = ['admins' => Api_Org::get_admin_users($fields),
              'org' => Api_Org::get_org_users($data,$fields)];

    return $users;
  }

  static function get_location_list($data)
  {
			$locations = get_posts( array(
				'post_type'      => 'crb_location',
				'post_status'    => array( 'publish'),
				'posts_per_page' => -1,
				'meta_key'       => '_crb_user_organization',
				'meta_value'     => $data['id']
			) );

      return $locations;
  }

  static function get_org_users($data,$fields) {
    $query_args = [ 'role__in' => [ 'crb_session_admin' ], 
                    'fields' => $fields,
                    'meta_query' => [
                      [
                        'key' => '_crb_user_organization',
                        'value' => $data['id'],
                        'compare' => '='
                      ]
                    ]
                  ];

    $users = get_users($query_args);
    
    if ( empty( $users ) ) {
      return null;
    }

    return $users;
  }

  static function get_admin_users($fields) {
    $query_args = [ 'role__in' => [ 'administrator' ], 
                    'fields' => $fields,
                    'meta_query' => [
                      [
                        'key' => 'kkc_capabilities',
                        'value' => 'administrator',
                        'compare' => 'like'
                      ]
                    ]
                  ];

    $users = get_users($query_args);
    
    if ( empty( $users ) ) {
      return null;
    }

    return $users;
  }
}