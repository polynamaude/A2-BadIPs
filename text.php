<?php

require_once 'vendor/autoload.php';

$webClient = new \GuzzleHttp\Client();

try {
	echo 'Opening file '.__DIR__.'/iplist.txt.tmp'.' for writing temporary ip list'."\n";
	$outfile = fopen(__DIR__.'/iplist.txt.tmp','w+');
	echo 'Connecting to https://www.blocklist.de/ for blacklisted ips'."\n";
	$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();
	echo 'Writing ips to file'."\n";
	while ($blackip = fgets($infile))
	{
		fwrite ($outfile, rtrim($blackip)."\n");
	}
}
catch (Exception $e)
{
	echo 'Error Caught !'."\n";
	echo $e->getMessage()."\n";
}
try {
	echo 'Connecting to https://www.badips.com/ for blacklisted ips'."\n";
	$infile = $webClient->get('http://www.badips.com/get/list/ssh/2')->getBody()->detach();
	echo 'Writing ips to file'."\n";
	while ($blackip = fgets ($infile))
	{
		fwrite ($outfile, rtrim($blackip)."\n");
	}
}
catch (Exception $e)
{
	echo 'Error Caught !'."\n";
	echo $e->getMessage()."\n";
}
try {
	fclose ($outfile);
	echo 'File closed'."\n";
	if (file_exists(__DIR__.'/iplist.txt'))
	{
		echo 'Deleting '.__DIR__.'/iplist.txt'."\n";
		unlink(__DIR__.'/iplist.txt');
	}
	echo 'Renaming '.__DIR__.'/iplist.txt.tmp'.' to '.__DIR__.'/iplist.txt'."\n";
	rename(__DIR__.'/iplist.txt.tmp', __DIR__.'/iplist.txt');
}
catch (Exception $e)
{
	echo 'Error Caught !'."\n";
	echo $e->getMessage()."\n";
}
