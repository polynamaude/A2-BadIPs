<?php

require_once 'vendor/autoload.php';

$webClient = new \GuzzleHttp\Client();

$iplists = new ArrayObject();

$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();

$outfile = fopen('iplist.txt','w+');

while ($blackip = fgets($infile))
{
	fwrite ($outfile, rtrim($blackip)."\n");
}

$infile = $webClient->get('http://www.badips.com/get/list/ssh/2')->getBody()->detach();

while ($blackip = fgets ($infile))
{
	fwrite ($outfile, rtrim($blackip)."\n");
}

fclose ($outfile);

