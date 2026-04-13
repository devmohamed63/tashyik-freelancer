<?php
$key = 'AIzaSyDh3CnsdNeqAXkEsI4joJ8MYXVaDzG_HOA';
$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=New+York&key=' . $key;
$context = stream_context_create(['http' => ['ignore_errors' => true]]);
$response = file_get_contents($url, false, $context);
echo $response;
