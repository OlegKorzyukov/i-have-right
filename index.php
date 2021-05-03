<?php 

require_once __DIR__ . '/vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$whoops = new \Whoops\Run;
$whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$result = $client->get('https://pravo.minjust.gov.ua/ua/advices');

$host = 'https://pravo.minjust.gov.ua';

$content = $result->getBody()->getContents();

preg_match_all('#<div[^>]*class="[^"]*(?<=\s|")post-list-item(?=\s|")[^>]*>.*?\s*<div[^>]*class="[^"]*(?<=\s|")thumb(?=\s|")[^>]*>\s*<a[^>]*href="(?<url>[^>]*)"#ims', $content, $data, PREG_SET_ORDER);

	$item_url = [];
	$i = 0;
	
foreach ($data as $item){
	$item_url_text = $host . $item['url'];
	$item_url[$i] = $item_url_text;
	$result = $client->get($item_url_text);
	$content = $result->getBody()->getContents();

	preg_match('#<div[^>]*class="[^"]*(?<=\s|")header(?=\s|")[^>]*>.*?<img[^>]*class="[^"]*(?<=\s|")img-fluid(?=\s|")[^>]*>*src="(?<url>[^"]*)"#ims', $content, $img_head);
	
	foreach ($img_head as $img){
		$main_img[$i] = $host . $img;
	}

	preg_match('#<h1[^>]*class="[^"]*(?<=\s|")page-title(?=\s|")[^>]*>.*?>#ims', $content, $title);

	foreach ($title as $tit){
		$title_strip = strip_tags($tit);
		$title_arr[$i] = $title_strip;
	}

	preg_match('#<div[^>]*class="[^"]*(?<=\s|")entry-content(?=\s|")[^>]*>.*?</div[^>]*>#ims', $content, $news);

	foreach ($news as $new){
		$item_news[$i] = $new;
	}

		$all_item[$i] = ["origin_url" => $item_url[$i], "origin_img" => $main_img[$i], "post_title" => $title_arr[$i], "post_content" => $item_news[$i]];

	$i++;
}


function timeZone(){
	$all_time = [];
	$tz = ['Europe/Kiev', 'Europe/London'];
	$count = count($tz);
	for($i = 0; $i < $count; $i++){
		
		$tz = $tz[$i];
		$timestamp = time();
		$now_time = new DateTime("now", new DateTimeZone($tz));
		$now_time->setTimestamp($timestamp);
		$time = $now_time->format('Y-m-d H:i:s');

		$all_time[$i] = $time;
	}
	return $all_time;
}

$post_author = '1';

list($post_date_gmt, $post_date) = timeZone();



$post_data = array(
	'post_title'    => $all_item[0]['post_title'],
	'post_content'  => $all_item[0]['post_content'],
	'post_status'   => 'publish',
	'post_author'   => 1,
	'comment_status' => 'closed',
	'post_category' => array(8,39)
);


// Вставляем данные в БД
$post_id = wp_insert_post( wp_slash($post_data) );

 //$all_array = array_merge($item_url, $main_img, $title_arr, $item_news);

 //print_r($all_item);
?>