;(function($, window, document, undefined) {
	var $win = $(window);
	var $doc = $(document);
	var date_fields_selector = '[name^="_crb_class_dates"][name$="[_start]"]';
	var time_start_selector  = '[name^="_crb_class_dates"][name$="[_time_start]"]';
	var time_end_selector    = '[name^="_crb_class_dates"][name$="[_time_end]"]';

	$win.load( function() {
		plugin_time_prepopulate();
		plugin_date_unique_within_Dates_complex();
		plugin_admin_date_change_update_recipe();
		plugin_admin_org_change();
		plugin_admin_org_show_locations();
		plugin_admin_org_default();
		plugin_show_new_user_org();
	} );

	/**
	 * Initialize Complex Group with Time Prepopulated from 1st group entry
	 */
	function plugin_time_prepopulate() {
		var $container = $( '#Classsettings, #ClassDatessettingsAdminonly' );

		$container.find( '.carbon-actions a' ).on( 'click', function() {
			setTimeout( function() {
				var first_start_time = $container.find( '[name="_crb_class_dates[0][_time_start]"]' ).val();
				var first_end_time = $container.find( '[name="_crb_class_dates[0][_time_end]"]' ).val();

				if ( first_start_time.length > 0 ) {
					$container.find('.carbon-group-row:last').find(time_start_selector)
						.val( first_start_time )
						.trigger('crb_change_time_autopush');
				};
				if ( first_end_time.length > 0 ) {
					$container.find('.carbon-group-row:last').find(time_end_selector)
						.val( first_end_time )
						.trigger('crb_change_time_autopush');
				};
			}, 100 );
		} );
	};

	/**
	 * Guarantee Unique Date
	 */
	function plugin_date_unique_within_Dates_complex() {
		var $container = $('#Classsettings, #ClassDatessettingsAdminonly');
		var $date_fields = $container.find(date_fields_selector);

		$container.on( 'change', date_fields_selector, function() {
			var $field = $(this);
			var current_field_value = $field.val();
			var current_field_name = $field.attr( 'name' );

			var $date_fields = $container.find(date_fields_selector);

			$date_fields.each( function() {
				var $looped_field = $(this);
				var value = $looped_field.val();
				var name = $looped_field.attr( 'name' );

				if ( current_field_value == value && current_field_name != name ) {
					// Unset Value of other fields
					$looped_field
						.val( '' )
						.change();
				};
			} );
		} );
	};

	/**
	 * Initialize event trigger "Change" for the Recipes dropdown, when the Date is modified.
	 */
	function plugin_admin_date_change_update_recipe() {
		var $container = $( '#ClassDatessettingsAdminonly' );

		$container.on( 'change', date_fields_selector, function() {
			$(this).closest('.fields-container').find('select[name$="[_recipe]"]').trigger('change');
		} );
	};

	/**
	 * Start of adding Organizational level functionality
	 */
	const ktk_base_api_uri = '/wp-json/ktk/v1/';
    const ktk_base_posts_uri = '/wp-json/wp/v2/posts/';

	function plugin_admin_org_change() {
		if($('#profile-page').length == 0){
			$('select[name="_crb_user_organization"]').on('change', function() {
					reload_authors( this.value );
					reload_locations(this.value);
				
				}
			);

			if(adminpage != 'post-new-php' ){
				$('select[name="_crb_user_organization"]').change();
			}

			var isJustFirstTime = true;
			$('select[name="_crb_class_location"]').on('change', function() {
				    
					if(adminpage == 'post-new-php' || !isJustFirstTime) {
						set_author_by_location();
					}
					isJustFirstTime = false;
				}
			);
		}
	};

	function plugin_admin_org_show_locations() {
		let orgLocations = $('#org-locations-list');
		if(orgLocations.length > 0)
		{
			let org = getUrlParameter('post');
			orgLocations.empty();
			get_org_locations({
				org: `${org}`,
				do: display_org_location_list
			});
		}
	};

	function get_org_locations(props)
	{
		const org_svc = `${ktk_base_api_uri}org/${props.org}/locations`;
			const response = fetch(org_svc).then(resp => {
				resp.json().then(locations => {
					props.do(locations,props.org)
				})});
	}
	
	function plugin_admin_org_default()
	{   let org = getUrlParameter('org_id');
		if(org != null && org.length > 0){
			var option = $(`select[name=_crb_user_organization] option[value=${org}]`)
			if(option.length == 1)
			{
				option.attr('selected','selected');
				$("select[name=_crb_user_organization]").change();
				
			}
		}
	}

	function display_org_location_list(locations,org)
	{      
		   let orgLocations = $('#org-locations-list');
		   orgLocations.append('<h2>Locations:</h2><br/>');
		   if(locations.length>0) {
			   
			   orgLocations.append('<div><ul>');
			   locations.forEach( function(item,index,arr) {
				   orgLocations.append(`<li><a href="/wp-admin/post.php?post=${item.ID}&action=edit" >${item.post_title}</a></li>`);
			   });
			   orgLocations.append('</ul></div>');
		   }
		   else {
			   orgLocations.append('<div><b>No locations for this Organization</b></div>');
		   }
		   orgLocations.append(`<br/><a class="button" href="/wp-admin/post-new.php?post_type=crb_location&org_id=${org}">Add Location</a>`)
	}

	function set_available_locations(locations){
			
		    let select = $('select[name="_crb_class_location"]');
			let oldOption = $('select[name="_crb_class_location"] option[selected="selected"]')

			select.empty();
			locations.forEach(function(item,index,arr){
				if(index == 0){
					select.append($(`<option value="${item.ID}" >${item.post_title}</option>`));
				}
				else{
					select.append($(`<option value="${item.ID}" >${item.post_title}</option>`));
				}
			});
			let option = select.find('option:first');
			if(oldOption.length > 0){
				
			    newOption = select.find(`option[value="${oldOption.val()}"]`);
				if(newOption.length > 0) {
					option = newOption;
				}
			}
			option.attr('selected','selected');
			select.change();
	}

	function set_author_by_location(){
		
		let select = $('select[name="_crb_class_location"]');
        const auth_svc = `${ktk_base_api_uri}common/item/${select.val()}`;
		fetch(auth_svc).then(resp => {
			resp.json().then( author => {
				let newOption = $(`#post_author_override option[value="${author.post_author}"]`)
				if (newOption.length == 1 )
				{
					$('#post_author_override').val(author.post_author);
				}
				$('#post_author_override').change();
			});
		});
	}

	function reload_locations(org) {

		get_org_locations({
			org: `${org}`,
			do: set_available_locations
		});
	}

	function plugin_show_new_user_org() {
		if(adminpage == 'user-new-php'){
			$("#role").trigger('change');
		}
	}

	function reload_authors(org) {
		const org_svc = `${ktk_base_api_uri}org/${org}/users`;
		const response = fetch(org_svc).then(resp => {
			resp.json().then(usrs => {
				var oldValue = null;
				let oldOption = $('#post_author_override option[selected="selected"]')
				if(oldOption.length > 0)
				{
					oldValue = oldOption.val();
				}
				$('#post_author_override').empty();
				var firstOrgUsr = null;
				var selectedAlready = false;
				usrs.admins.forEach(function(item,index,arr){
					if(item.ID == oldValue){
						$('#post_author_override').append($(`<option value="${item.ID}" selected="selected">${item.display_name} (${item.user_login})</option>`));
						selectedAlready = true;
					}
					else {
						$('#post_author_override').append($(`<option value="${item.ID}">${item.display_name} (${item.user_login})</option>`));
					}
				});
				usrs.org.forEach(function(item,index,arr){
			        let prevItem = $(`#post_author_override option[value=${item.ID}]`)
					let exists = prevItem.length > 0;
					if(!selectedAlready && item.ID == oldValue){
						if(!exists) {
						$('#post_author_override').append($(`<option value="${item.ID}" selected="selected">${item.display_name} (${item.user_login})</option>`));
						
						}
						else{
							prevItem.attr('selected','selected');
						}
						selectedAlready = true;
					}
					else {
						if(!exists) {
							let option = $(`<option value="${item.ID}">${item.display_name} (${item.user_login})</option>`);
							$('#post_author_override').append(option);
							if(index == 0)
							{
								firstOrgUsr = option;
							}
						}
						firstOrgUsr = prevItem;
					}

					if(!selectedAlready && firstOrgUsr){
						firstOrgUsr.attr('selected','selected');
					}
				});
				$('#post_author_override').change();
			})
		});
	}

	var getUrlParameter = function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;
	
		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');
	
			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			}
		}
		return false;
	};

})(jQuery, window, document);
