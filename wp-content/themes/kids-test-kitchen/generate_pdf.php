<?php
// Include autoloader 
require_once 'dompdf/autoload.inc.php'; 
 
// Reference the Dompdf namespace 
use Dompdf\Dompdf; 
 
// Instantiate and use the dompdf class 
$dompdf = new Dompdf();
  $parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
  require_once( $parse_uri[0] . 'wp-load.php' );
	
 	$print_pdf = '';
	$print_pdf .='
	<style>
.wp-list-table {
	width: 100%;
	text-align: center;
	font-family: Arial, Helvetica, sans-serif;
}
.wp-list-table th, .wp-list-table td {
	border: 1px solid #58bee8;
	text-align: left;
	padding: 15px;
	font-family: Arial, Helvetica, sans-serif;
	vertical-align:top;
}
.wp-list-table th{
	background:#58bee8;
	color:#fff;
	border: 1px solid #58bee8;
	vertical-align:middle;
}

</style>
	';
if (isset($_GET['user_id'])){
    $Crb_User = new Crb_User( $_GET['user_id'] );
    $user = $Crb_User->get_user();
    $display_name = '<h1>' .  $user->data->display_name  . '</h1>';
    $print_pdf .= $display_name;
}
	$print_pdf .= '
<table class="wp-list-table" cellpadding="0" cellspacing="0">
  <thead>
    <tr>
      <th style="width:18%;">Title</th>
      <th style="width:10%;">Age Ranges</th>
      <th style="width:32%;">Date (Recipe)</th>
      <th style="width:40%;">Location</th>
    </tr>
  </thead>
  <tbody id="the-list" style="position:absoluted">';
    //$post_ids_get_value = $_GET["post_ids"];	
    $val_ids_fetched = $_GET["post_ids"];
	$str = explode(",",$val_ids_fetched);
	$query_args = array(
		'post_type'              => 'crb_class',
		'post__in' 				 => $str,
		'meta_key'				 => '_crb_class_dates_-_start_0',
	    'orderby'				 => 'meta_value',
	    'order'					 => 'ASC',
		'posts_per_page' 		 => -1,        
    );
   $query = new WP_Query($query_args);
    if ( $query->have_posts() ) :while ( $query->have_posts() ) : $query->the_post();
   $print_pdf .= ' <tr>
      <td>';
	 $print_pdf .=   get_the_title();
	 $print_pdf .= ' </td>';
     $print_pdf .= ' <td>';
	 $ptitle = 
	 
	 $terms = get_the_terms( get_the_ID(), "crb_class_age" );
			 foreach($terms as $wcatTerm) :
				// get the first term
				$print_pdf .= $wcatTerm->name.',&nbsp;';
			 endforeach; 	
	 $print_pdf .='</td>';
     $print_pdf .= '<td>';
	 $initilizeclass = new Crb_Initialize_Class();
        $dates = $initilizeclass->column_callback_get_dates_upcoming(get_the_ID(), $_GET['user_id']);
	 $print_pdf .= $dates; 
	 $print_pdf .='</td>';
     $print_pdf .= '<td>';
	 $loc = $initilizeclass->column_callback_location_pdf(get_the_ID());
	 $print_pdf .= $loc;
	 $print_pdf .='</td>
    </tr>';
	
	endwhile; // end of the loop.
    wp_reset_query();
   
   endif; 
   
 $print_pdf .= ' </tbody>
</table>';
	
	
	
	//echo $ff = custom_html();

// Load HTML content 

//$complete_content = $_GET['complete_content'];
$dompdf->loadHtml($print_pdf); 
 
// (Optional) Setup the paper size and orientation 
$dompdf->setPaper('A3', 'landscape'); 
 
// Render the HTML as PDF 
$dompdf->render(); 
 
// Output the generated PDF to Browser 
//$dompdf->stream('title.pdf');


// Load content from html file 
/*$html = file_get_contents("template_pdfdownload.php"); 
$dompdf->loadHtml($html); 
 
// (Optional) Setup the paper size and orientation 
$dompdf->setPaper('A4', 'landscape'); 
 
// Render the HTML as PDF 
$dompdf->render(); 
*/ 
// Output the generated PDF (1 = download and 0 = preview) 
$dompdf->stream("codexworld", array("Attachment" => 0));

?>