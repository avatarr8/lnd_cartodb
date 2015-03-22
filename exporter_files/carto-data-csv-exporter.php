<?php 
set_time_limit(0);
ini_set("memory_limit", "1G");
$homeDir = "/home/livi2274682700/";
$docRoot = $homeDir."html/";
// load the wp environment
define('WP_USE_THEMES', false);
require($docRoot.'wp-blog-header.php');

global $wpdb;

$export_dir = $homeDir.'map_exports/';

if (!is_dir($export_dir)) {
	mkdir($export_dir, 0755);
}

function get_meta_value($post_id, $key, $single = true) {
	$value = get_post_meta($post_id, $key, $single);
	if (!empty($value)) {
		$value = htmlspecialchars($value, ENT_QUOTES);
		$value = str_replace(array("\r\n", "\r", "\n"), " ", $value);
	} else {
		$value = '';
	}
	return $value;
}

function get_taxonomy_list($post_id, $taxonomy) {
	$terms = get_the_terms($post_id, $taxonomy);
	$list = '';
	$counter = 1;
	if ( !empty($terms) ) {	
		foreach ($terms as $term) {	
		if ($counter > 1) { $separator = ', '; } else { $separator = ''; }
		$list .= htmlspecialchars($separator.$term->name, ENT_QUOTES);
		$counter++; 
		}
	}
	return $list; 
}

date_default_timezone_set('UTC');
$date = date('Y-m-d');
$export_file = fopen("$export_dir/$date-map-export.csv", 'w');
$columns_header = array("post_id", "post_title", "post_date", "post_excerpt", 
	"lat", "lng", "location_id", "location_name", "location_notes", "street_address", 
 	"city", "state", "zip", "county", "total_cost", "start_year", "completion_year", 
 	"new_deal_agencies", "new_deal_categories", "artists", "contractors", "designers", 
 	"status", "menu_order", "image"
 );

$query = $wpdb->prepare(
	"SELECT 
  	ID,  
    post_title,
		post_date,
		post_excerpt,
		post_content,
		menu_order
	FROM wp_posts
	WHERE post_type = 'projects'
	AND post_status = 'publish'
	ORDER BY menu_order ASC", '');

$results = $wpdb->get_results( $query );
fputcsv($export_file, $columns_header);

foreach( $results as $result ) {
	
  $custom_fields = get_post_custom($result->ID);
	$post_date = explode(' ', $result->post_date);
	$post_date = new dateTime($post_date[0]);
	$post_date = $post_date->format('Y/m/d');
	if ( !empty( $result->post_title ) ) {
		$title = $result->post_title; //get_the_title($result->ID);
		$title= htmlspecialchars($title, ENT_QUOTES);
	} else {
		$title = '';
	}
	if ( !empty( $result->post_excerpt ) ) {
		$post_excerpt = $result->post_excerpt;
	} elseif ( !empty( $result->post_content )) {
		$plain_content = str_replace(array("\r\n", "\r", "\n"), " ", $result->post_content);
		$plain_content = htmlspecialchars($plain_content, ENT_QUOTES);
		$post_excerpt = mb_substr($plain_content, 0, 200);
		if ($post_excerpt != $result->post_content) {
			$lastspace = strrpos($post_excerpt, ' ');
			$post_excerpt = substr($post_excerpt, 0, $lastspace) . "...";
		}
	} else {
		$post_excerpt = '';
	}

	$lat = get_meta_value($result->ID, '_lnd_lat');
	$lng = get_meta_value($result->ID, '_lnd_lng');
	if ($lat && $lng) {
		$lat = "$lat";
		$lng = "$lng";
	} else {
		$lat= '';
		$lng= '';
	}

	$location_notes = get_meta_value($result->ID, '_lnd_prj_directions');
	$street_address = get_meta_value($result->ID, '_lnd_prj_str_addr');
	$city = get_meta_value($result->ID, '_lnd_prj_city');
	$state = get_meta_value($result->ID, '_lnd_prj_state');
	$zip = get_meta_value($result->ID, '_lnd_prj_zip');
	$county = get_meta_value($result->ID, '_lnd_prj_county');
	$total_cost = get_meta_value($result->ID, '_lnd_prj_total_cost');

	if (has_post_thumbnail($result->ID)) {
		$image_id = get_post_thumbnail_id($result->ID);
		$image_src = wp_get_attachment_image_src($image_id,'project-image');
		$image = $image_src[0];
	} else {
		$image = '';
	}
	
	$locations = get_the_terms($result->ID, 'locations');
	if (!empty($locations)) {
		foreach ($locations as $location) {	
			$location_name = htmlspecialchars($location->name, ENT_QUOTES);
			$location_id = $location->term_id;
		}
	} else {
		$location_name = '';
		$location_id = '';
	}
	if (!empty($result->menu_order)) {
		$menu_order = $result->menu_order;
	}
	else {
		$menu_order = '';
	}

	$start_year_list = get_taxonomy_list($result->ID, 'start_years');
	$completion_year_list = get_taxonomy_list($result->ID, 'completion_years');
	$new_deal_agencies_list = get_taxonomy_list($result->ID, 'new_deal_agencies');
	$categories_list = get_taxonomy_list($result->ID, 'new_deal_categories');
	$artists_list = get_taxonomy_list($result->ID, 'artists');
	$contractors_list = get_taxonomy_list($result->ID, 'contractors');
	$designers_list = get_taxonomy_list($result->ID, 'designers');
	$status_list = get_taxonomy_list($result->ID, 'status');

	fputcsv($export_file, array($result->ID, $title, $post_date, $post_excerpt, $lat, $lng, $location_id, $location_name, 
		$location_notes, $street_address, $city, $state, $zip, $county, $total_cost, $start_year_list, 
		$completion_year_list, $new_deal_agencies_list, $categories_list, $artists_list, $contractors_list, 
		$designers_list, $status_list, $menu_order, $image));
} 	// end foreach

fclose($export_file);
echo 'Map Data CSV Export Completed';

?>
