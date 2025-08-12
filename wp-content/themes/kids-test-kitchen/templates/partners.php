<?php
/*
 Template Name: Шаблон partners
 */

    if(is_page()){
        get_header();
    }

$args = array(
    'role'    => (new Crb_User_Initialize_Session_Admin())->role,
    'orderby' => 'display_name',
    'order'   => 'ASC'
);
$users = get_users( $args );

$partnerList = [];
$soonList = [];
foreach ($users as $user) {
    $locations = get_posts(array(
        'post_type' => 'crb_location',
        'author' => $user->ID,
    ));

    if (empty($locations)){
        $soonList[] = $user->display_name;
    }
    foreach ($locations as $location){
        $partnerList[$user->display_name][] = $location->post_title;
    }
}


?>
<style>
    .table-responsive{
        margin-top: 30px;
    }
    .table-responsive table{
        width: 100%;
        border-collapse:collapse;
        border-spacing:0
    }
    .table-responsive ul{
        margin-left: 1rem;
    }
    .table-responsive li{
        margin-left: 1rem;
    }
    .table-responsive table, .table-responsive td, .table-responsive th{
        border: 1px solid #595959;
    }
    .table-responsive td, .table-responsive th{
        padding: 3px;
        width: 50%;
        height: 25px;
        font-size: 14px;
    }
    .table-responsive thead th{
        font-size: 16px;
    }
    .table-responsive tbody tr th{
        text-transform: uppercase;
    }
</style>

<div class="table-responsive">
    <table>
        <thead>
        <tr>
            <th>Partner</th>
            <th>Location</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($partnerList as $displayName => $locationTitles) { ?>
        <tr>
            <th><?php echo $displayName?></th>
            <td>
                <ul>
                    <?php foreach ($locationTitles as $title){ ?>
                    <li><?php echo $title?></li>
                    <?php } ?>
                </ul>
            </td>
        </tr>
        <?php } ?>

        <tr>
            <td style="text-align: center" colspan="2">COMING SOON!</td>
        </tr>
        <?php foreach ($soonList as $displayName) { ?>
        <tr>
            <th><?php echo $displayName?></th>
            <td></td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>