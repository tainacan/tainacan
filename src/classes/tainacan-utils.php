<?php
/**
 * This file gathers functions usefull for theme and plugin developers
 */


/**
 * Retrieves raw data sent to an API endpoint reading the php://input stream
 * @return Object PHP Raw Postdata
 */
function tainacan_get_api_postdata() {
	$postdata = file_get_contents("php://input");
	$post = json_decode($postdata);
	return $post;
}