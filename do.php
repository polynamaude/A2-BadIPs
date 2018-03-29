<?php

require_once 'vendor/autoload.php';

$frewrite = "RewriteCond %{REMOTE_ADDR} ";
$frewrite2 = "RewriteRule ^.* - [F,L]";

$webClient = new \GuzzleHttp\Client();

try {
	echo 'Opening file '.__DIR__.'/iplist.tmp'.' for writing temporary ip list'."\n";
	$outfile = fopen('iplist.tmp','w+');
	echo 'Connecting to https://www.blocklist.de/ for blacklisted ips'."\n";
	$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();
	echo 'Writing ips to file'."\n";
	while ($blackip = fgets($infile))
	{
		fwrite ($outfile, $frewrite.rtrim($blackip)."\n");
		fwrite ($outfile, $frewrite2."\n");
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
	while ($blackip = fgets ($infile))
	{
		fwrite ($outfile, $frewrite.$blackip."\n");
		fwrite ($outfile, $frewrite2."\n");
	}
}
catch (Exception $e)
{
	echo 'Error Caught !'."\n";
	echo $e->getMessage()."\n";
}
try {
	fclose($outfile);
	echo 'File closed'."\n";
	if (file_exists(__DIR__.'/iplist'))
	{
		echo 'Deleting '.__DIR__.'/iplist'."\n";
		unlink(__DIR__.'/iplist');
	}
	echo 'Renaming '.__DIR__.'/iplist.tmp'.' to '.__DIR__.'/iplist'."\n";
	rename(__DIR__.'/iplist.tmp', __DIR__.'/iplist');
}
catch (Exception $e)
{
	echo 'Error Caught !'."\n";
	echo $e->getMessage()."\n";
}
