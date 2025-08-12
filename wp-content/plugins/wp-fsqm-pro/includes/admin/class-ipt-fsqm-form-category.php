<?php
/**
 * IPT FSQM Category
 *
 * Class for handling the Category page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\FormCategory
 * @codeCoverageIgnore
 */
class IPT_FSQM_Form_Category extends IPT_FSQM_Admin_Base {
	public $table_view;
	public $page_action;

	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_form_category_nonce';

		parent::__construct();

		$this->icon = 'folder-open';

		$this->post_result[4] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully created the category.', 'ipt_fsqm' ),
		);
		$this->post_result[5] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully updated the category.', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the category.', 'ipt_fsqm' ),
		);
		$this->post_result[7] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted selected categories.', 'ipt_fsqm' ),
		);

		add_filter( 'set-screen-option', array( $this, 'table_set_option' ), 10, 3 );
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/
	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'Form Categories', 'ipt_fsqm' ), __( 'Form Category', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_form_category', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		global $ipt_fsqm_info;
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Form Category <a href="admin.php?page=ipt_fsqm_form_category&paction=new_cat" class="add-new-h2">Add New</a>', 'ipt_fsqm' ), false );
		switch( $this->page_action ) {
			case 'new_cat' :
				$this->category_form();
				break;
			case 'edit_cat' :
				$this->category_form( $_GET['cat_id'] );
				break;
			default :
				$this->show_table();
		}
		$this->index_foot();
	}

	public function save_post( $check_referer = true ) {
		parent::save_post();
		$action = $this->post['db_action'];
		if ( $action == 'insert' ) {
			IPT_FSQM_Form_Elements_Static::create_category( $this->post['fsqm_cat']['name'], $this->post['fsqm_cat']['description'] );
			wp_redirect( add_query_arg( array( 'post_result' => 4 ), $_POST['_wp_http_referer'] ) );
		} else {
			IPT_FSQM_Form_Elements_Static::update_category( $this->post['fsqm_cat']['id'], $this->post['fsqm_cat']['name'], $this->post['fsqm_cat']['description'] );
			wp_redirect( add_query_arg( array( 'post_result' => 5 ), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) ) );
		}
		die();
	}

	public function on_load_page() {
		$this->table_view = new IPT_FSQM_Category_Table();
		// fsqm_category_per_page
		$option = 'per_page';
		$args = array(
			'label' => __( 'Categories Per Page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'fsqm_category_per_page',
		);
		add_screen_option( $option, $args );

		$this->page_action = isset( $_GET['paction'] ) ? $_GET['paction'] : 'table_view';
		$action = $this->table_view->current_action();

		if ( $action == 'delete' ) {
			if ( isset( $_GET['cat_id'] ) ) {
				if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_category_delete_' . $_GET['cat_id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				IPT_FSQM_Form_Elements_Static::delete_categories( $_GET['cat_id'] );
				wp_redirect( add_query_arg( array( 'post_result' => 6 ), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) ) );
			} else {
				if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_category_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				IPT_FSQM_Form_Elements_Static::delete_categories( $_GET['cat_ids'] );
				wp_redirect( add_query_arg( array( 'post_result' => 7 ), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) ) );
			}

			die();
		}

		get_current_screen()->add_help_tab( array(
			'id'  => 'overview',
			'title'  => __( 'Overview', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This screen displays all your form categories. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'By default, this screen will show all the categories. You can also create a new category by clicking on the <strong>Add New</strong> Button. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'screen-content',
			'title'  => __( 'Screen Content', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You can sort categories based on name.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can hide/display columns based on your needs and decide how many categories to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can search a particular category by using the Search Form. You can type in just the name.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'new-category-form',
			'title'  => __( 'Add New Category', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'Click on the <strong>Add New</strong> button to get started.' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Category Name</strong>: A short name of the category. This will be shown on admin lists and user portals.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Category Description</strong>: A description of the category. HTML allowed.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Once done, click on the <strong>Create Category</strong> button and it will be added to the list.', 'ipt_fsqm' )
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'action-links',
			'title'  => __( 'Available Actions', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'Hovering over a row in the posts list will display action links that allow you to manage your submissions. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>View Forms</strong> will take you to the all forms page, filtered by this category only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>View Submissions</strong> will take you to the all submissions page, filtered by this category only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Edit</strong> lets you recustomize the category.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Delete</strong> removes your category this list as well as from the database. Any form associated with it will be unassigned. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'bulk-actions',
			'title'  => __( 'Bulk Actions', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked categories from the database. Any form associated with these will be unassigned.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);

		parent::on_load_page();
	}

	protected function show_table() {
		$this->table_view->prepare_items();
		?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Form Categories', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : ?>
				<?php if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
				<?php endif; ?>
			<?php endforeach; ?>
			<?php $this->table_view->search_box( __( 'Search Categories', 'ipt_fsqm' ), 'search_id' ); ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
		<?php
	}

	protected function category_form( $id = '' ) {
		$buttons = array(
			array( __( 'Create Category', 'ipt_fsqm' ), '', 'medium', 'primary', 'normal', array(), 'submit' ),
			array( __( 'Reset', 'ipt_fsqm' ), '', 'medium', 'secondary', 'normal', array(), 'reset' ),
			array( __( 'View All', 'ipt_fsqm' ), '', 'medium', 'secondary', 'normal', array(), 'anchor', array(), array(), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) ),
		);
		if ( $id == '' ) {
			$category = new stdClass();
			$category->name = '';
			$category->description = '';
			$category->id = '';
		} else {
			$category = IPT_FSQM_Form_Elements_Static::get_category( $id );
			if ( $category == null ) {
				$this->ui->msg_error( __( 'Invalid Category ID', 'ipt_fsqm' ) );
				$this->ui->button( __( 'View All', 'ipt_fsqm' ), '', 'medium', 'primary', 'normal', array(), 'anchor', true, array(), array(), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) );
				return;
			}
			$buttons[0][0] = __( 'Update Category', 'ipt_fsqm' );
		}
		?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3>
			<span class="ipt-icomoon-tag2"></span>
			<?php if ( $category->id == '' ) : ?>
			<?php _e( 'Create a new Category', 'ipt_fsqm' ); ?>
			<?php else : ?>
			<?php _e( 'Update Category', 'ipt_fsqm' ); ?>
			<?php endif; ?>
		</h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form method="post" action="admin-post.php" id="<?php echo $this->pagehook; ?>_form_primary">
			<input type="hidden" name="action" value="<?php echo $this->admin_post_action; ?>" />
			<?php wp_nonce_field( $this->action_nonce, $this->action_nonce ); ?>
			<input type="hidden" name="db_action" value="<?php echo ( $category->id == '' ? 'insert' : 'update' ); ?>" />
			<input type="hidden" name="fsqm_cat[id]" value="<?php echo $category->id; ?>" />
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( 'fsqm_cat[name]', __( 'Category Name', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( 'fsqm_cat[name]', $category->name, __( 'Shortname of the category', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( 'fsqm_cat[description]', __( 'Category Description', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( 'fsqm_cat[description]', $category->description, '' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="clear"></div>
			<?php $this->ui->buttons( $buttons ); ?>
		</form>
	</div>
</div>
		<?php
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}
