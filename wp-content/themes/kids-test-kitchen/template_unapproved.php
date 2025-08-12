<style>
    .wp-list-table{
        margin-top: 30px;
    }
</style>
<?php
function all_values()
{
    ob_start();
    ?>

    <table class='wp-list-table widefat fixed striped table-view-list posts'>
        <thead>
        <tr>
            <th scope='col' id='title' class='manage-column column-title column-primary sortable desc'>
                <a href='#'>
                    <span>Title</span>
                    <span class='sorting-indicator'></span>
                </a>
            </th>
            <th scope='col' id='taxonomy-crb_class_age' class='manage-column column-taxonomy-crb_class_age'>Age Ranges</th>
            <th scope='col' id='crb-dates-recipes-column' class='manage-column column-crb-dates-recipes-column'>Date (Recipe)</th>
            <th scope='col' id='crb-location-column' class='manage-column column-crb-location-column'>Location</th>
            <th scope='col' class='manage-column column-crb-action-column'>Action</th>
        </tr>
        </thead>
        <tbody id='the-list'>
        <?php
        $today = current_time('Y-m-d');
        $facilitator_id = get_current_user_id();

        if ($facilitator_id == "") {
            $query_args = array(
                'post_type' => 'crb_class',
                'posts_per_page' => -1,
                'meta_key' => '_crb_class_dates_-_start_0',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => '_crb_class_dates_-_start_0', // Only loop through posts when the event date
                        'compare' => '>', // Is greater than $today, ie in the future
                        'value' => $today,
                    ),
                    array(
                        'key' => '_crb_class_schedule_approve',
                        'compare' => '',
                        'value' => ''
                    )
                )
            );
        } else {
            $query_args = array(
                'post_type' => 'crb_class',
                'posts_per_page' => -1,
                'meta_key' => '_crb_class_dates_-_start_0',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                //'orderby'                => 'menu_order',
                'meta_query' => array(
                    /*array(
                        'key' => '-_facilitator_0',
                        'compare_key' => 'LIKE',
                        'value' => $facilitator_id,
                        'compare' => '=='
                    ),*/
                    array(
                        'key' => '_crb_class_dates_-_start_0', // Only loop through posts when the event date
                        'compare' => '>', // Is greater than $today, ie in the future
                        'value' => $today,
                    ),
                    array(
                        'key' => '_crb_class_schedule_approve',
                        'compare' => '',
                        'value' => ''
                    )
                )
            );

        }

        $query = new WP_Query($query_args);
        if ($query->have_posts()) :while ($query->have_posts()) : $query->the_post(); ?>
            <tr id='post-<?php echo get_the_ID(); ?>'
                class='iedit author-other level-0 post-10316 type-crb_class status-publish hentry crb_class_age-elementary crb_class_age-kindergarten'>
                <td class='title column-title has-row-actions column-primary page-title' data-colname='Title'>
                    <strong><span><?php the_title(); ?></span></strong>
                </td>
                <td class='taxonomy-crb_class_age column-taxonomy-crb_class_age' data-colname='Age Ranges'>
                    <?php
                        $initilizeclass = new Crb_Initialize_Class();
                        $dates = $initilizeclass->column_callback_get_dates_order(get_the_ID());

                    ?>
                    <?php
                    $terms = get_the_terms(get_the_ID(), 'crb_class_age');
                    if (!empty($terms)) {
                        $temp_name = '';
                        foreach ($terms as $term){
                            $temp_name.= $term->name.', ';
                        }

                        echo substr($temp_name, 0, -2);
                    }

                    ?>

                </td>

                <td class='crb-dates-recipes-column column-crb-dates-recipes-column' data-colname='Date (Recipe)'>
                    <strong style='color: green;'><code class='recipe-date'>
                        <?php
                            $initilizeclass = new Crb_Initialize_Class();
                            $dates = $initilizeclass->column_callback_get_dates_opportunities(get_the_ID());
                            echo $dates;
                        ?>
                </td>


                <td class='crb-location-column column-crb-location-column' data-colname='Location'>
                    <?php
                        $loc = $initilizeclass->column_callback_location_pdf(get_the_ID());
                        echo $loc;
                    ?>
                </td>
                <td class='crb-location-column column-crb-action-column' data-colname='Action'>
                     <button class="button action btn_send_request" id="<?=get_the_ID(); ?>">Request</button>
                </td>
            </tr>
        <?php endwhile; // end of the loop.
            ?>
            <?php wp_reset_query(); ?>
        <?php else: ?>
        <tr class='iedit author-other level-0 post-10316 type-crb_class status-publish hentry crb_class_age-elementary crb_class_age-kindergarten'>
            <td colspan="5" style="text-align: center;"><strong>No results found.</strong></td>
            <td colspan="5" style="text-align: center;"><strong>No results found.</strong></td>
        </tr>

        <?php endif;
        ?>
        </tbody>

        <tfoot>
        <tr>
            <th scope='col' id='title' class='manage-column column-title column-primary sortable desc'>
                <a href='#'>
                    <span>Title</span>
                    <span class='sorting-indicator'></span>
                </a>
            </th>
            <th scope='col' id='taxonomy-crb_class_age' class='manage-column column-taxonomy-crb_class_age'>Age Ranges</th>
            <th scope='col' id='crb-dates-recipes-column' class='manage-column column-crb-dates-recipes-column'>Date (Recipe)</th>
            <th scope='col' id='crb-location-column' class='manage-column column-crb-location-column'>Location</th>
            <th scope='col' class='manage-column column-crb-action-column'>Action</th>
        </tr>
        </tfoot>
    </table>
    <?php
    $contents = ob_get_contents();
    ob_end_clean();
    echo $contents;
}

all_values();
?>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    jQuery('.btn_send_request').on('click', function () {

       var ajax_url = '<?php echo admin_url( 'admin-ajax.php'); ?>';
        let post_id = jQuery(this).attr('id');
        jQuery(this).prop('disabled', true);
        jQuery.ajax({
            type: 'post',
            url: ajax_url,
            data:
                {
                    action: 'send_email_communication',
                    post_id: post_id
                },
            success:function (res) {
                if (res === 'success'){
                    Swal.fire('The email was sent successfully!');
                }
            }
        })
    })
</script>
