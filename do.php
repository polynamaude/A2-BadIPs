<?php

require_once 'vendor/autoload.php';

$webClient = new \GuzzleHttp\Client();

$iplists = new ArrayObject();

$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();

$frewrite = "RewriteCond %{REMOTE_ADDR} ";
$frewrite2 = "RewriteRule ^.* - [F,L]";
$outfile = fopen('iplist','w+');

while ($blackip = fgets($infile))
{
	$blackedip = rtrim($blackip);
	fwrite ($outfile, $frewrite.$blackedip."\n");
	echo $frewrite.$blackedip."\n";
	fwrite ($outfile, $frewrite2."\n");
	echo $frewrite2."\n";
}

$infile = $webClient->get('http://www.badips.com/get/list/ssh/2')->getBody()->detach();

while ($blackip = fgets ($infile2))
{
	$blackedip = rtrim($blackip);
	fwrite ($outfile, $frewrite.$blackedip."\n");
	echo $frewrite.$blackedip."\n";
	fwrite ($outfile, $frewrite2."\n");
	echo $frewrite2."\n";
}

fclose ($outfile);

