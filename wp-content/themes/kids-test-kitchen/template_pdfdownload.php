<input class="button button-primary subit_value" type="button" value="Download PDF">
<?php
//require("includes/init/Crb_Initialize_Class.php");


//print_r($dates);
?>
<input type="hidden" id="main_check_values">

<script>

    jQuery(document).ready(function ($) {
        $(function () {
            $('.subit_value').attr('disabled', 'disabled');
            $('#cb-select-all-1,input[name="post_id"]').click(function () {
                if ($(this).is(':checked')) {
                    $('.subit_value').removeAttr('disabled');
                } else {
                    $('.subit_value').attr('disabled', 'disabled');
                }
            });
        });
    });


    jQuery(document).ready(function ($) {
        var main_check_values = $('#main_check_values');
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const user_id = urlParams.get('user_id')
        $('#cb-select-all-1').change(function (e) {
            main_check_values.val(getSelectedFruits());
        });
        $('input[name="post_id"]').change(function (e) {
            main_check_values.val(getSelectedFruits());
        });

        function getSelectedFruits() {
            var main_check_valuess = "";
            $('input[name="post_id').each(function (i, cb) {
                if ($(this).is(":checked")) {
                    main_check_valuess += $(this).val() + "";
                }
            });
            return main_check_valuess;
        }

        $(".subit_value").click(function (event) {
            window.open('<?php bloginfo('stylesheet_directory')?>/generate_pdf.php?post_ids=' + $("#main_check_values").val() + '&user_id=' + user_id);
        });


    });
</script>
<style>
    .subit_value.button.button-primary {
        margin: 30px 0;

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
            <td id='cb' class='manage-column column-cb check-column'><label class='screen-reader-text'
                                                                            for='cb-select-all-1'>Select All</label>
                <input id='cb-select-all-1' type='checkbox' value=""></td>
            <th scope='col' id='title' class='manage-column column-title column-primary sortable desc'><a
                        href='https://localhost/kidstestkitchen/wp-admin/edit.php?post_type=crb_class&amp;orderby=title&amp;order=asc'><span>Title</span><span
                            class='sorting-indicator'></span></a></th>
            <th scope='col' id='taxonomy-crb_class_age' class='manage-column column-taxonomy-crb_class_age'>Age Ranges
            </th>
            <th scope='col' id='crb-dates-recipes-column' class='manage-column column-crb-dates-recipes-column'>Date
                (Recipe)
            </th>
            <th scope='col' id='crb-location-column' class='manage-column column-crb-location-column'>Location</th>
        </tr>
        </thead>
        <tbody id='the-list'>
        <?php
        $today = current_time('Y-m-d');
        $facilitator_id = $_GET['user_id'];
        if ($facilitator_id == "") {
            $query_args = array(
                'post_type' => 'crb_class',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_crb_class_dates_-_start_0', // Only loop through posts when the event date
                        'compare' => '>', // Is greater than $today, ie in the future
                        'value' => $today,
                    )
                ),
                'meta_key' => '_crb_class_dates_-_start_0',
                'orderby' => 'meta_value',
                'order' => 'ASC'
            );
        } else {
            $query_args = array(
                'post_type' => 'crb_class',
                'posts_per_page' => -1,
                'meta_key' => '_crb_class_dates_-_start_0',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                'meta_query' => array(
                    'relation' => 'AND',
                    [
                        'relation' => 'OR',
                        [
                            'key' => '_crb_class_facilitator',
                            'compare' => '=',
                            'value' => $facilitator_id
                        ],
                        [
                            'key' => '_crb_class_dates_-_facilitator_',
                            'compare_key' => 'LIKE',
                            'value' => $facilitator_id,
                            'compare' => '='
                        ]
                    ],
                    [
                        [
                            'key' => '_crb_class_dates_-_start_',
                            'compare_key' => 'LIKE',
                            'value' => $today,
                            'compare' => '>'
                        ]
                    ]
                )
            );

        }

        $query = new WP_Query($query_args);
        if ($query->have_posts()) :while ($query->have_posts()) : $query->the_post(); ?>
            <tr id='post-10316'
                class='iedit author-other level-0 post-10316 type-crb_class status-publish hentry crb_class_age-elementary crb_class_age-kindergarten'>
                <th scope="row" class="check-column">
                    <input id="cb-select-10316" type="checkbox" name="post_id" value="<?php echo get_the_ID() ?>,">
                    <?php //echo get_the_ID() ?>
                </th>
                <td class='title column-title has-row-actions column-primary page-title' data-colname='Title'>
                    <strong><span>
        <?php the_title(); ?>
        </span></strong></td>
                <td class='taxonomy-crb_class_age column-taxonomy-crb_class_age' data-colname='Age Ranges'>
                    <?php
                    // echo $metaq = get_post_meta(get_the_ID(),"_crb_class_dates_-_start_0",true).'<br>';
                    // echo $today = current_time('Y-m-d');
                    $initilizeclass = new Crb_Initialize_Class();
                    $dates = $initilizeclass->column_callback_get_dates_order(get_the_ID());

                    ?>
                    <?php
                    $terms = get_the_terms(get_the_ID(), 'crb_class_age');
                     foreach($terms as $wcatTerm) : 
					   ?>
					  <?php echo $wcatTerm->name.',&nbsp;'; ?>
					  <?php 
					   endforeach; 
					?>

                </td>

                <td class='crb-dates-recipes-column column-crb-dates-recipes-column' data-colname='Date (Recipe)'>
                    <strong style='color: green;'><code class='recipe-date'>

                            <?php
                            $initilizeclass = new Crb_Initialize_Class();
                            $dates = $initilizeclass->column_callback_get_dates_for_download(get_the_ID(), $facilitator_id);
                            echo $dates;
                            ?>
                </td>


                <td class='crb-location-column column-crb-location-column' data-colname='Location'><?php
                    $loc = $initilizeclass->column_callback_location_pdf(get_the_ID());
                    echo $loc;
                    ?></td>

            </tr>
        <?php endwhile; // end of the loop.
            ?>
            <?php wp_reset_query(); ?>
        <?php else: ?>
            <tr id='post-10316'
                class='iedit author-other level-0 post-10316 type-crb_class status-publish hentry crb_class_age-elementary crb_class_age-kindergarten'>
                <td colspan="5" style="text-align: center;"><strong>No results found.</strong></td>
            </tr>
        <?php endif;
        ?>
        </tbody>

        <tfoot>
        <tr>
            <td class='manage-column column-cb check-column'><label class='screen-reader-text' for='cb-select-all-2'>Select
                    All</label>
                <input id='cb-select-all-2' type='checkbox' value=""></td>
            <th scope='col' class='manage-column column-title column-primary sortable desc'><a
                        href='https://localhost/kidstestkitchen/wp-admin/edit.php?post_type=crb_class&amp;orderby=title&amp;order=asc'><span>Title</span><span
                            class='sorting-indicator'></span></a></th>
            <th scope='col' class='manage-column column-taxonomy-crb_class_age'>Age Ranges</th>
            <th scope='col' class='manage-column column-crb-dates-recipes-column'>Date (Recipe)</th>
            <th scope='col' class='manage-column column-crb-location-column'>Location</th>
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