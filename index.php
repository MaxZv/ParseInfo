<?php
include "simple_html_dom.php";

function get_data_by_selector($html, $selector)
{
    $all_found_data = $html->find($selector);
    $data_arr = [];
    foreach ($all_found_data as $data) {
        array_push($data_arr, $data->plaintext);
    }
    return $data_arr;
}

$curl = curl_init();

$url = 'https://butlon.com/boodschappen/categorieen/dagknallers';

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($curl);
$html = str_get_html($result);

$titles = get_data_by_selector($html, '.supmrtdets_head a');
$descriptions = get_data_by_selector($html, '.supmrtdec');
$price_block = $html->find('.ov-disc-price');
$normal_prices_arr = [];
$offer_prices_arr = [];
foreach ($price_block as $item) {
    $before_price = str_get_html($item->outertext)->find('.ovd-before p', 0);
    if (!empty($before_price->plaintext)) {
        array_push($normal_prices_arr, $before_price->plaintext);
    } else {
        array_push($normal_prices_arr, $item->plaintext);
    }
}
$offer_prices = $html->find('.ov-disc-price > p');
foreach ($offer_prices as $offer) {
    array_push($offer_prices_arr, $offer->plaintext);
}

$start_date = date("d.m.y");
$end_date = date('d.m.y', strtotime("+1 day"));
$info = [];
array_push($info, ['Store', 'Country', 'Title', 'Description', 'Normal price', 'Offer price', 'Start date', 'End date']);
for ($i = 0; $i < count($titles); $i++) {
    array_push($info, ['Butlon', 'nl', $titles[$i], $descriptions[$i], $normal_prices_arr[$i], $offer_prices_arr[$i], $start_date, $end_date]);
}

curl_close($curl);

$fp = fopen('info.csv', 'w+');
fputs($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
foreach ($info as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);
