<?php

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

Container::make( 'theme_options', __( 'Theme Options', 'crb' ) )
	->add_tab( __( 'General', 'crb' ), array(
		Field::make( 'complex', 'crb_updates_email_recipients', __( 'Daily Classes Update Email Recipients', 'crb' ) )
			->set_layout( 'tabbed-vertical' )
			->add_fields( array(
				Field::make( 'text', 'address', __( 'Email', 'crb' ) )
					->set_required(),
			) )
			->set_header_template( '{{ address }}' ),
		Field::make( 'rich_text', 'crb_site_instructions', __( 'Site Instructions', 'crb' ) ),
        Field::make( 'rich_text', 'crb_site_instructions_session_admin', __( 'Session Admin Site Instructions', 'crb' ) ),
        Field::make( 'rich_text', 'crb_site_instructions_assistant', __( 'Assistant Site Instructions', 'crb' ) ),
        Field::make( 'rich_text', 'crb_site_instructions_facilitator', __( 'Facilitator Site Instructions', 'crb' ) ),

    ) )

	->add_tab( __( 'Misc', 'crb' ), array(
		Field::make( 'header_scripts', 'crb_header_script', __( 'Header Script', 'crb' ) ),
		Field::make( 'footer_scripts', 'crb_footer_script', __( 'Footer Script', 'crb' ) ),
	) );