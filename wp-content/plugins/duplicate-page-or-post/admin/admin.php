<?php

class wpda_duplicate_post_admin_panel{
	private $parametrs;
	function __construct(){
		$this->initial_standart_parametrs();
		$this->admin_filters();		
	}

	/*###################### Admin filters function ##################*/	
		
	private function admin_filters(){
		//hook for admin menu
		add_action( 'admin_menu', array($this,'create_admin_menu') );
		// hook for saving options in databese
		add_action( 'wp_ajax_wpdevart_duplicate_post_parametrs_save_in_db', array($this,'save_in_db'));
		// filters for ading external link on page and post list
		add_filter('post_row_actions', array($this,'duplicate_post_page_link'),10,2);
		add_filter('page_row_actions', array($this,'duplicate_post_page_link'),10,2);
		add_action('admin_action_wpdevart_duplicate_post_page',array($this,"duplicate_post_or_page"));
	}
	
	/*###################### URL function ##################*/	
	
	public function duplicate_post_page_link($actions, $post){
		if ( $post->post_type == "post" || $post->post_type == "page" ) {
			$url = admin_url( 'admin.php' );	 
			if ( current_user_can( 'edit_post', $post->ID ) ) {	 
				// Include a nonce in this link
				$copy_link = wp_nonce_url( add_query_arg( array( 'action' => 'wpdevart_duplicate_post_page','post_id'=>$post->ID ), $url ), 'wpdevart_clone_post_page_nonce' );
		 
				// Add the new Copy quick link.
				$actions = array_merge( $actions, array(
									'copy' => sprintf( '<a href="%1$s">%2$s</a>',
										esc_url( $copy_link ), 
										__( 'Duplicate', 'wpda_duplicate' )
									) 
								 ) 
				);
			}
		}	 
		return $actions;
	}
	//conect admin menu
	public function create_admin_menu(){
		global $submenu;
		/* conect admin pages to wordpress core*/
		$main_page=add_menu_page( __( 'Duplicate Post', 'wpda_duplicate' ), __( 'Duplicate Post', 'wpda_duplicate' ), 'manage_options', 'wpda_duplicate_post_menu', array($this, 'options_page'), wpda_duplicate_post_plugin_url . 'admin/images/menu_icon.png');
		add_submenu_page( 'wpda_duplicate_post_menu', __( 'Duplicate Post', 'wpda_duplicate' ), __( 'Duplicate Post', 'wpda_duplicate' ), 'manage_options','wpda_duplicate_post_menu',array($this, 'options_page'));		
		$featured_page = add_submenu_page( 'wpda_duplicate_post_menu', __( 'Featured Plugins', 'wpda_duplicate' ), __( 'Featured Plugins', 'wpda_duplicate' ), 'manage_options', 'wpda_duplicate_post_feature_plugin',array($this, 'featured_plugins'));
		$featured_theme_page = add_submenu_page( 'wpda_duplicate_post_menu', __( 'Featured Themes', 'wpda_duplicate' ), __( 'Featured Themes', 'wpda_duplicate' ), 'manage_options', 'wpda_duplicate_post_feature_theme',array($this, 'featured_themes'));
		$hire_expert = add_submenu_page( 'wpda_duplicate_post_menu', __( 'Hire an Expert', 'wpda_duplicate' ),  '<span style="color:#00ff66" >'. __('Hire an Expert', 'wpda_duplicate') .'</span>' , 'manage_options', 'wpda_duplicate_post_hire_expert',array($this, 'hire_expert'));
		/*for including page styles and scripts*/
		add_action('admin_print_styles-' .$main_page, array($this,'create_option_page_style_js'));
		add_action('admin_print_styles-' . $featured_page, array($this, 'featured_plugins_js_css'));
        add_action('admin_print_styles-' . $featured_theme_page, array($this, 'featured_themes_js_css'));
        add_action('admin_print_styles-' . $hire_expert, array($this, 'hire_expert_js_css'));	
		if(isset($submenu['wpda_duplicate_post_menu'])){
			add_submenu_page( 'wpda_duplicate_post_menu', __( 'Support or Any Ideas?', 'wpda_duplicate' ), '<span style="color:#00ff66" >'.__( 'Support or Any Ideas?', 'wpda_duplicate' ).'</span>', 'manage_options',"wpdevart_comingsoon_any_ideas",array($this, 'any_ideas'),155);
			$count_pages = count($submenu['wpda_duplicate_post_menu'])-1;
			$submenu['wpda_duplicate_post_menu'][$count_pages][2] = wpdevart_duplicate_post_support_url;
		}
	}
	
	    /*###################### Standard parameters function ##################*/	
		
	private function initial_standart_parametrs(){
		$this->parametrs["title_prefix"]="";
		$this->parametrs["copy_title"]="1";
		$this->parametrs["title_sufix"]="";
		$this->parametrs["copy_content"]="1";
		$this->parametrs["copy_excerpt"]="1";
		$this->parametrs["copy_date"]="1";
		$this->parametrs["copy_status"]="1";
		$this->parametrs["copy_featured_image"]="1";
		$this->parametrs["copy_template"]="1";
		$this->parametrs["copy_format"]="1";
		$this->parametrs["copy_author"]="1";
		$this->parametrs["copy_password"]="1";
		$this->parametrs["copy_attachments"]="1";
		$this->parametrs["copy_comments"]="1";
		$this->parametrs["copy_categories"]="1";
		$this->parametrs["copy_tags"]="1";
		if(is_array($params=get_option("wpdevart_duplicate_post_admin_parametrs","no_params"))){			
			foreach($this->parametrs as $key => $value){
				if(isset($params[$key])){
					$this->parametrs[$key]=$params[$key];
				}
			}
		}
	}
	
    /*###################### Database function ##################*/		
	
	public function save_in_db(){
		if(check_ajax_referer("wpdevart_duplicate_post_save_paramas_nonce","wpdevart_duplicate_post_save_paramas_nonce",false) === false){
			wp_die(__( 'Security error.', 'wpda_duplicate' ));
		}
        if(!current_user_can( 'edit_posts' )){
            wp_die(__( 'Security error.', 'wpda_duplicate' ));
        }
		foreach($this->parametrs as $key => $value){
			if(isset($_POST[$key])){
				$this->parametrs[$key]=sanitize_text_field($_POST[$key]);
			}
		}
		update_option("wpdevart_duplicate_post_admin_parametrs",$this->parametrs);
		wp_die('1');
		
	}

	public function create_option_page_style_js(){		
		//scripts
		wp_enqueue_script('jquery');
		wp_enqueue_style('wpdevart_duplicate_post_admin_menu_css',wpda_duplicate_post_plugin_url.'admin/css/duplicate_post_menu.css');	
		wp_enqueue_script('wpdevart_duplicate_post_admin_menu_js',wpda_duplicate_post_plugin_url.'admin/js/duplicate_post_menu.js');
		wp_localize_script( 'wpdevart_duplicate_post_admin_menu_js', 'wpdevart_js_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );		
	}

	public function featured_plugins_js_css() {
        wp_enqueue_style('wpda_duplicate_post_featured_page_css', wpda_duplicate_post_plugin_url . 'admin/css/featured_plugins_css.css');
    }

    public function featured_themes_js_css() {
        wp_enqueue_style('wpda_duplicate_post_featured_themes_page_css', wpda_duplicate_post_plugin_url . 'admin/css/featured_themes_css.css');
    }

    public function hire_expert_js_css() {
        wp_enqueue_style('wpda_duplicate_post_hire_expert_css', wpda_duplicate_post_plugin_url . 'admin/css/hire_expert.css');
    }

	/*###################### Options page function ##################*/	
	
	public function options_page(){
		?>
		<h1><?php echo __( 'Duplicate Page or Post', 'wpda_duplicate' ) ?></h1><br>
		<div class="main_parametrs_group_div">
			<div class="head_panel_div" title="Click to toggle">
            	<span class="title_parametrs_image">
					
				</span>
				<span class="title_parametrs_group"><?php echo __( 'Duplicate Page or Post Settings', 'wpda_duplicate' ); ?></span>
				<span class="enabled_or_disabled_parametr"></span>        
			</div>
			<div class="inside_information_div" style="display: block;">
				<table id="wpdevart_parametrs_table" class="wp-list-table widefat fixed posts section_parametrs_table"> 
					<tbody>				
						<tr>
							<td><?php echo __( 'Title prefix (prefix is the custom text before the title)', 'wpda_duplicate' ); ?></td>
							<td>
								<input type="text" id="title_prefix" value="<?php echo stripslashes(esc_attr($this->parametrs["title_prefix"])); ?>" >
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy title from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_title">
									<option <?php selected($this->parametrs["copy_title"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_title"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Title suffix (suffix is the custom text after the title)', 'wpda_duplicate' ); ?></td>
							<td>
								<input type="text" id="title_sufix" value="<?php echo stripslashes(esc_attr($this->parametrs["title_sufix"])); ?>" >
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Content from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_content">
									<option <?php selected($this->parametrs["copy_content"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_content"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Excerpt from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_excerpt">
									<option <?php selected($this->parametrs["copy_excerpt"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_excerpt"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						
						<tr>
							<td><?php echo __( 'Copy Date from the duplicated page/post?', 'wpda_duplicate' ) ?></td>
							<td>
								<select id="copy_date">
									<option <?php selected($this->parametrs["copy_date"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_date"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Status from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_status">
									<option <?php selected($this->parametrs["copy_status"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_status"],"0"); ?> value="0"><?php echo __( 'Copy and set as draft', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Featured Image from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_featured_image">
									<option <?php selected($this->parametrs["copy_featured_image"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_featured_image"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Template from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_template">
									<option <?php selected($this->parametrs["copy_template"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_template"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Format from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_format">
									<option <?php selected($this->parametrs["copy_format"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_format"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Author(page/post author) from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_author">
									<option <?php selected($this->parametrs["copy_author"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_author"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Password(page/post password) from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_password">
									<option <?php selected($this->parametrs["copy_password"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_password"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Comments from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_comments">
									<option <?php selected($this->parametrs["copy_comments"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_comments"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Categories from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_categories">
									<option <?php selected($this->parametrs["copy_categories"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_categories"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo __( 'Copy Tags from the duplicated page/post?', 'wpda_duplicate' ); ?></td>
							<td>
								<select id="copy_tags">
									<option <?php selected($this->parametrs["copy_tags"],"1"); ?> value="1"><?php echo __( 'Yes', 'wpda_duplicate' ); ?></option>
									<option <?php selected($this->parametrs["copy_tags"],"0"); ?> value="0"><?php echo __( 'No', 'wpda_duplicate' ); ?></option>
								</select>
							</td>
						</tr>
						<tr style="display:none">
							<td><input type="hidden" id="wpdevart_duplicate_post_save_paramas_nonce" value="<?php echo wp_create_nonce( "wpdevart_duplicate_post_save_paramas_nonce" ); ?>"></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2" width="100%"><button data-clickabel="yes" type="button" id="wpdevart_save_in_databese" class="save_section_parametrs button button-primary"><span class="save_button_span"><?php echo __( 'Save Settings', 'wpda_duplicate' ); ?></span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
						</tr>
					</tfoot>       
				</table>
			</div>     
		</div>
		<?php
	}
	

	public function duplicate_post_or_page(){
		//current user have a preveligies to edit post or page
		if(!current_user_can( 'edit_posts' )){
			wp_die("you don't have a permission");
		}
		// check user come from right place
		check_admin_referer('wpdevart_clone_post_page_nonce');	
		// if we don't have post id we can't continue copy post
		if (!(isset($_GET['post_id']) && $_GET['post_id']!='')) {
			wp_die(__( 'not detected post', 'wpda_duplicate' ));
		}
		$post_id=sanitize_text_field($_GET['post_id']);
		$original_post=get_post($post_id);
		$pos_arr = array(		
			'post_title' => $this->get_copied_post_title($original_post),
			'post_type' => $original_post->post_type,
			'post_name' => $original_post->post_name,
			'post_content' => $this->get_post_content($original_post),
			'post_excerpt' =>$this->get_post_excerpt($original_post),
			'post_date' => $this->get_post_date($original_post),
			'post_status' => $this->get_post_status($original_post),
			'post_author' =>  $this->get_author($original_post),
			'post_password' => $this->get_password($original_post),			
			'comment_status' => $original_post->comment_status,
			'ping_status' => $original_post->ping_status,			
			'post_content_filtered' =>$original_post->post_content_filtered,				
			'post_mime_type' => $original_post->post_mime_type,
			'post_parent' =>$original_post->post_parent,
		);
		$post_id=wp_insert_post($pos_arr);
		if($post_id==0){
			wp_die(__( "Error copy post", 'wpda_duplicate' ));
		}
		$new_post=get_post($post_id);
		$this->duplicate_meta_dates($original_post,$new_post);
		$this->duplicate_post_format($original_post,$new_post);
		$this->duplicate_comments($original_post,$new_post);
		$this->duplicate_categories($original_post,$new_post);
		$this->duplicate_tags($original_post,$new_post);
		$this->redirect_url($original_post);

	}
	/******************************HELPER FUNCTIONS********************************/
	private function get_copied_post_title($post){
		
		$title=$this->parametrs["title_prefix"].(($this->parametrs["copy_title"]=="1")?$post->post_title:"").$this->parametrs["title_sufix"];
		if($title==""){
			return __( "Untitled", 'wpda_duplicate' );
		}
		return $title;
		
	}
	private function get_post_content($post){
		if($this->parametrs["copy_content"]=="1"){
			return $post->post_content;
		}
		return "";		
	}
	private function get_post_excerpt($post){
		if($this->parametrs["copy_excerpt"]=="1"){
			return $post->post_excerpt;
		}
		return "";		
	}
	private function get_post_date($post){
		if($this->parametrs["copy_date"]=="1"){
			return $post->post_date;
		}
		return "";		
	}
	
	private function get_author($post){
		$current_user=wp_get_current_user();
		if($this->parametrs["copy_author"]=="1"){
			return $post->post_author;
		}
		return $current_user->ID;		
	}
	private function get_password($post){
		$current_user=wp_get_current_user();
		if($this->parametrs["copy_password"]=="1"){
			return $post->post_password;
		}
		return "";		
	}
	private function get_post_status($post){
		$current_user=wp_get_current_user();
		if($this->parametrs["copy_status"]=="1"){
			return get_post_status($post->ID);
		}
		return "draft";		
	}
	private function duplicate_meta_dates($original_post,$new_post){
		$original_post_metas=get_post_meta($original_post->ID);
		foreach($original_post_metas as $original_key=>$original_post_meta){
			if($original_key=="_thumbnail_id" && $this->parametrs["copy_featured_image"]=="0"){
				continue;
			}
			if($original_key=="_wp_page_template" && $this->parametrs["copy_template"]=="0"){
				continue;
			}
			if(count($original_post_meta)>1){
				update_post_meta($new_post->ID,$original_key,$original_post_meta);
			}else{
				if(is_array($original_post_meta)){
					update_post_meta($new_post->ID,$original_key,$original_post_meta[0]);
				}else{
					update_post_meta($new_post->ID,$original_key,$original_post_meta);
				}				
			}
			
		}
	}
	private function duplicate_comments($original_post,$new_post){
		if(!$this->parametrs["copy_comments"]=="1"){
			return 1;
		}
		//get original post comments
		$comments = get_comments(array(
			'post_id' => $original_post->ID,
			'order' => 'ASC',
			'orderby' => 'comment_date_gmt'
		));
		// reserve all old ids in keys and value new id
		$old_id_to_new_id = array();
		foreach ($comments as $comment){
			$commentdata = array(
				'comment_post_ID' => $new_post->ID,
				'comment_author' => $comment->comment_author,
				'comment_author_email' => $comment->comment_author_email,
				'comment_author_url' => $comment->comment_author_url,
				'comment_content' => $comment->comment_content,
				'comment_type' => '', 
				'comment_parent' => isset($old_id_to_new_id[$comment->comment_parent])?$old_id_to_new_id[$comment->comment_parent]:0,
				'user_id' => $comment->user_id,
				'comment_author_IP' => $comment->comment_author_IP,
				'comment_agent' => $comment->comment_agent,
				'comment_karma' => $comment->comment_karma,
				'comment_approved' => $comment->comment_approved,
				'comment_date'		=> $comment->comment_date,
				'comment_date_gmt'	=> get_gmt_from_date($comment->comment_date),
			);
			$new_comment_id = wp_insert_comment($commentdata);
			$old_id_to_new_id[$comment->comment_ID] = $new_comment_id;
		}
	}
	
	    /*###################### Categories duplicate function ##################*/	
	
	private function duplicate_categories($original_post,$new_post){
		if(!$this->parametrs["copy_categories"]=="1"){
			return 1;
		}
		$original_categories=wp_get_post_categories( $original_post->ID);
		wp_set_post_categories($new_post->ID,$original_categories);
	}
	private function duplicate_tags($original_post,$new_post){
		if(!$this->parametrs["copy_tags"]=="1"){
			return 1;
		}
		$original_tags_terms=wp_get_post_tags( $original_post->ID);
		$original_tags=array();
		foreach($original_tags_terms as $original_tags_term){
			array_push($original_tags,$original_tags_term->name);
		}
		wp_set_post_tags($new_post->ID,$original_tags);
	}
	
	private function duplicate_post_format($original_post,$new_post){
		$original_post_format=get_post_format($original_post);
		if($this->parametrs["copy_format"]=="1" && $original_post_format){
			set_post_format($new_post,$original_post_format);
		}
		return 1;
	}
	
	private function redirect_url($post){
		switch($post->post_type){
			case "post":
				wp_redirect(admin_url( 'edit.php' ));
			break;
			case "page":
				wp_redirect(admin_url( 'edit.php?post_type=page' ));
			break;
		}		
	}

	/*############################### Featured plugins function ########################################*/
	


	public function hire_expert() {
        $plugins_array = array(
            'custom_site_dev' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/hire_expert/1.png',
                'title' => 'Custom WordPress Development',
                'description' => 'Hire a WordPress expert and make any custom development for your WordPress website.',
            ),
            'custom_plug_dev' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/hire_expert/2.png',
                'title' => 'WordPress Plugin Development',
                'description' => 'Our developers can create any WordPress plugin from zero. Also, they can customize any plugin and add any functionality.',
            ),
            'custom_theme_dev' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/hire_expert/3.png',
                'title' => 'WordPress Theme Development',
                'description' => 'If you need an unique theme or any customizations for a ready theme, then our developers are ready.',
            ),
            'custom_theme_inst' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/hire_expert/4.png',
                'title' => 'WordPress Theme Installation and Customization',
                'description' => 'If you need a theme installation and configuration, then just let us know, our experts configure it.',
            ),
            'gen_wp_speed' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/hire_expert/5.png',
                'title' => 'General WordPress Support',
                'description' => 'Our developers can provide general support. If you have any problem with your website, then our experts are ready to help.',
            ),
            'speed_op' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/hire_expert/6.png',
                'title' => 'WordPress Speed Optimization',
                'description' => 'Hire an expert from WpDevArt and let him take care of your website speed optimization.',
            ),
            'mig_serv' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/hire_expert/7.png',
                'title' => 'WordPress Migration Services',
                'description' => 'Our developers can migrate websites from any platform to WordPress.',
            ),
            'page_seo' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/hire_expert/8.png',
                'title' => 'WordPress SEO',
                'description' => 'SEO is an important part of any website. Hire an expert and he will organize the SEO of your website.',
            ),
        );
        $content = '';
        $content .= '<h1 class="wpda_hire_exp_h1"> Hire an Expert from WpDevArt </h1>';
        $content .= '<div class="hire_expert_main">';
        foreach ($plugins_array as $key => $plugin) {
            $content .= '<div class="wpdevart_hire_main"><a target="_blank" class="wpda_hire_buklet" href="https://wpdevart.com/hire-a-wordpress-developer-online-submit-form/">';
            $content .= '<div class="wpdevart_hire_image"><img src="' . $plugin["image_url"] . '"></div>';
            $content .= '<div class="wpdevart_hire_information">';
            $content .= '<div class="wpdevart_hire_title">' . $plugin["title"] . '</div>';
            $content .= '<p class="wpdevart_hire_description">' . $plugin["description"] . '</p>';
            $content .= '</div></a></div>';
        }
        $content .= '<div><a target="_blank" class="wpda_hire_button" href="https://wpdevart.com/hire-a-wordpress-developer-online-submit-form/">Hire an Expert</a></div>';
        $content .= '</div>';
        echo $content;
    }

    public function featured_plugins() {
        $plugins_array = array(
            'gallery_album' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/gallery-album-icon.png',
                'site_url' => 'http://wpdevart.com/wordpress-gallery-plugin',
                'title' => 'WordPress Gallery plugin',
                'description' => 'Gallery plugin is an useful tool that will help you to create Galleries and Albums. Try our nice Gallery views and awesome animations.',
            ),
            'coming_soon' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/coming_soon.png',
                'site_url' => 'http://wpdevart.com/wordpress-coming-soon-plugin/',
                'title' => 'Coming soon and Maintenance mode',
                'description' => 'Coming soon and Maintenance mode plugin is an awesome tool to show your visitors that you are working on your website to make it better.',
            ),
            'Contact forms' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/contact_forms.png',
                'site_url' => 'http://wpdevart.com/wordpress-contact-form-plugin/',
                'title' => 'Contact Form Builder',
                'description' => 'Contact Form Builder plugin is an handy tool for creating different types of contact forms on your WordPress websites.',
            ),
            'Booking Calendar' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/Booking_calendar_featured.png',
                'site_url' => 'http://wpdevart.com/wordpress-booking-calendar-plugin/',
                'title' => 'WordPress Booking Calendar',
                'description' => 'WordPress Booking Calendar plugin is an awesome tool to create a booking system for your website. Create booking calendars in a few minutes.',
            ),
			'chart'=>array(
				'image_url'		=>	wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/chart-featured.png',
				'site_url'		=>	'https://wpdevart.com/wordpress-organization-chart-plugin/',
				'title'			=>	__( 'WordPress Organization Chart', 'wpda_duplicate' ),
				'description'	=>	__( 'WordPress organization chart plugin is a great tool for adding organizational charts to your WordPress websites.', 'wpda_duplicate' )
			),
            'Pricing Table' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/Pricing-table.png',
                'site_url' => 'https://wpdevart.com/wordpress-pricing-table-plugin/',
                'title' => 'WordPress Pricing Table',
                'description' => 'WordPress Pricing Table plugin is a nice tool for creating beautiful pricing tables. Use WpDevArt pricing table themes and create tables just in a few minutes.',
            ),
            'youtube' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/youtube.png',
                'site_url' => 'http://wpdevart.com/wordpress-youtube-embed-plugin',
                'title' => 'WordPress YouTube Embed',
                'description' => 'YouTube Embed plugin is an convenient tool for adding videos to your website. Use YouTube Embed plugin for adding YouTube videos in posts/pages, widgets.',
            ),
            'facebook-comments' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/facebook-comments-icon.png',
                'site_url' => 'http://wpdevart.com/wordpress-facebook-comments-plugin/',
                'title' => 'Wpdevart Social comments',
                'description' => 'WordPress Facebook comments plugin will help you to display Facebook Comments on your website. You can use Facebook Comments on your pages/posts.',
            ),
            'countdown' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/countdown.jpg',
                'site_url' => 'http://wpdevart.com/wordpress-countdown-plugin/',
                'title' => 'WordPress Countdown plugin',
                'description' => 'WordPress Countdown plugin is an nice tool for creating countdown timers for your website posts/pages and widgets.',
            ),
            'lightbox' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/lightbox.png',
                'site_url' => 'http://wpdevart.com/wordpress-lightbox-plugin',
                'title' => 'WordPress Lightbox plugin',
                'description' => 'WordPress Lightbox Popup is an high customizable and responsive plugin for displaying images and videos in popup.',
            ),
            'facebook' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/facebook.png',
                'site_url' => 'http://wpdevart.com/wordpress-facebook-like-box-plugin',
                'title' => 'Social Like Box',
                'description' => 'Facebook like box plugin will help you to display Facebook like box on your website, just add Facebook Like box widget to sidebar or insert it into posts/pages and use it.',
            ),
            'poll' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/poll.png',
                'site_url' => 'http://wpdevart.com/wordpress-polls-plugin',
                'title' => 'WordPress Polls system',
                'description' => 'WordPress Polls system is an handy tool for creating polls and survey forms for your visitors. You can use our polls on widgets, posts and pages.',
            ),
            'poll' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_plugins/vertical-menu.png',
                'site_url' => 'http://wpdevart.com/wordpress-polls-plugin',
                'title' => 'WordPress Vertical Menu',
                'description' => 'WordPress Vertical Menu is a handy tool for adding nice vertical menus. You can add icons for your website vertical menus using our plugin.',
            ),

        );
        $html = '';
        $html .= '<h1 class="wpda_featured_plugins_title">Featured Plugins</h1>';
        foreach ($plugins_array as $plugin) {
            $html .= '<div class="featured_plugin_main">';
            $html .= '<div class="featured_plugin_image"><a target="_blank" href="' . $plugin['site_url'] . '"><img src="' . $plugin['image_url'] . '"></a></div>';
            $html .= '<div class="featured_plugin_information">';
            $html .= '<div class="featured_plugin_title">';
            $html .= '<h4><a target="_blank" href="' . $plugin['site_url'] . '">' . $plugin['title'] . '</a></h4>';
            $html .= '</div>';
            $html .= '<p class="featured_plugin_description">' . $plugin['description'] . '</p>';
            $html .= '<a target="_blank" href="' . $plugin['site_url'] . '" class="blue_button">Check The Plugin</a>';
            $html .= '</div>';
            $html .= '<div style="clear:both"></div>';
            $html .= '</div>';
        }
        echo $html;
    }

    public function featured_themes() {
        $themes_array = array(
            'tistore' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/tistore.jpg',
                'site_url' => 'https://wpdevart.com/tistore-best-ecommerce-theme-for-wordpress/',
                'title' => 'TiStore',
                'description' => 'TiStore is one of the best eCommerce WordPress themes that is fully integrated with WooCommerce.',
            ),
            'megastore' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/megastore.jpg',
                'site_url' => 'https://wpdevart.com/megastore-best-woocommerce-theme-for-wordpress/',
                'title' => 'MegaStore',
                'description' => 'MegaStore is one of the best WooCommerce themes available for WordPress.',
            ),
            'jevstore' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/jevstore.jpg',
                'site_url' => 'https://wpdevart.com/jewstore-best-wordpress-jewelry-store-theme/',
                'title' => 'JewStore',
                'description' => 'JewStore is a WordPress WooCommerce theme designed for jewelry stores and blogs.',
            ),
            'cakeshop' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/cakeshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-cake-shop-theme/',
                'title' => 'Cake Shop',
                'description' => 'WordPress Cake Shop is a multi-purpose WooCommerce-ready theme.',
            ),
            'flowershop' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/flowershop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-flower-shop-theme/',
                'title' => 'Flower Shop',
                'description' => 'WordPress Flower Shop is a responsive and WooCommerce-ready theme developed by our team.',
            ),
            'coffeeshop' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/coffeeshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-coffee-shop-cafe-theme/',
                'title' => 'Coffee Shop',
                'description' => 'It is a responsive and user-friendly theme designed specifically for coffee shop or cafe websites.',
            ),
            'weddingplanner' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/weddingplanner.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-wedding-planner-theme/',
                'title' => 'Wedding Planner',
                'description' => 'Wedding Planner is a responsive WordPress theme that is fully integrated with WooCommerce.',
            ),
            'Amberd' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/Amberd.jpg',
                'site_url' => 'https://wpdevart.com/amberd-wordpress-online-store-theme/',
                'title' => 'AmBerd',
                'description' => 'AmBerd has all the necessary features and functionality to create a beautiful WordPress website.',
            ),
            'bookshop' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/bookshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-book-shop-theme/',
                'title' => 'Book Shop',
                'description' => 'The Book Shop WordPress theme is a fresh and well-designed theme for creating bookstores or book blogs.',
            ),
            'ecommercemodernstore' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/ecommercemodernstore.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-ecommerce-modern-store-theme/',
                'title' => 'Ecommerce Modern Store',
                'description' => 'WordPress Ecommerce Modern Store theme is one of the best solutions if you want to create an online store.',
            ),
            'electrostore' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/electrostore.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-electronics-store-electro-theme/',
                'title' => 'ElectroStore',
                'description' => 'This is a responsive and WooCommerce-ready electronic store theme.',
            ),
            'jewelryshop' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/jewelryshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-jewelry-shop-theme/',
                'title' => 'Jewelry Shop',
                'description' => 'WordPress Jewelry Shop theme is designed specifically for jewelry websites, but of course, you can use this theme for other types of websites as well.',
            ),
            'fashionshop' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/fashionshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-fashion-shop-theme/',
                'title' => 'Fashion Shop',
                'description' => 'The Fashion Shop is one of the best responsive WordPress WooCommerce themes for creating a fashion store website.',
            ),
            'barbershop' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/barbershop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-barbershop-theme/',
                'title' => 'Barbershop',
                'description' => 'WordPress Barbershop is another responsive and functional theme developed by our team.',
            ),
            'furniturestore' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/furniturestore.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-furniture-store-theme/',
                'title' => 'Furniture Store',
                'description' => 'This is a great option to quickly create an online store using our theme and the WooCommerce plugin. Our theme is fully integrated with WooCommerce.',
            ),
            'clothing' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/clothing.jpg',
                'site_url' => 'https://wpdevart.com/tistore-best-ecommerce-theme-for-wordpress/',
                'title' => 'Clothing',
                'description' => 'The Clothing WordPress theme is one of the best responsive eCommerce themes available for WordPress.',
            ),
            'weddingphotography' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/weddingphotography.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-wedding-photography-theme/',
                'title' => 'Wedding Photography',
                'description' => 'WordPress Wedding Photography theme is one of the best themes specially designed for wedding photographers or photography companies.',
            ),
            'petshop' => array(
                'image_url' => wpda_duplicate_post_plugin_url.'admin/images/featured_themes/petshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-pet-shop-theme/',
                'title' => 'Pet Shop',
                'description' => 'Pet Shop is a powerful and well-designed WooCommerce WordPress theme.',
            ),

        );
        $html = '';
        $html .= '<div class="wpdevart_main"><h1 class="wpda_featured_themes_title">Featured Themes</h1>';

        $html .= '<div class="div-container">';
        foreach ($themes_array as $theme) {
            $html .= '<div class="theme" data-slug="tistore"><div class="theme-img">';                
            $html .= ' <img src="'.$theme['image_url'].'" alt="' . $theme['title'] . '">';
            $html .= '</div>';
            $html .= '<div class="theme-description">' . $theme['description'] . '</div>';
            $html .= '<div class="theme-name-container">'; 
            $html .= '<h2 class="theme-name">' . $theme['title'] . '</h2>';
            $html .= '<div class="theme-actions">';
            $html .= '<a target="_blank" aria-label="Check theme" class="button button-primary load-customize" href="' . $theme['site_url'] . '">Check Theme</a>';
            $html .= '</div></div></div>';
            
            
        }
        $html .= '</div></div>';
        echo $html;
    }
}
	?>