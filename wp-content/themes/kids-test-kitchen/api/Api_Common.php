<?php

class Api_Common {
  /**
   * Grab latest post title by an author!
   *
   * @param array $data Options for the function.
   * @return string|null Post title for the latest, * or null if none.
   */
  
  static function get_item($data)
  {
			$item = get_post( $data['id']);

      return $item;
  }

  
  static function test_daily_email($data)
  {
    /*
    $Crb_Mail = new Crb_Mail();
    
    $Crb_Mail->send_email_daily_update();
		$Crb_Mail->send_staff_daily_update();
    */
    return (object)[];

    
  }
  

}