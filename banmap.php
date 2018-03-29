<?php

require_once 'vendor/autoload.php';

/**
 * Lookup key we'll be using to map in RewriteMap condition for apache rules
 * @var string $bankey
 */
$bankey = 'BIP';

$webClient = new \GuzzleHttp\Client();

try {
	echo 'Opening file '.__DIR__.'/iplistbad.tmp'.' for writing temporary ip list'."\n";
	$outfile = fopen(__DIR__.'/iplistbad.tmp','w+');
	echo 'Connecting to https://www.blocklist.de/ for blacklisted ips'."\n";
	$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();
	echo 'Writing ips to file'."\n";
	while ($blackip = fgets($infile))
	{
		fwrite ($outfile, rtrim($blackip)."\t".$bankey."\n");
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
		fwrite ($outfile, rtrim($blackip)."\t".$bankey."\n");
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

	if (file_exists(__DIR__.'/iplistbad'))
	{
		echo 'Deleting '.__DIR__.'/iplistbad'."\n";
		unlink(__DIR__.'/iplistbad');
	}
	echo 'Renaming '.__DIR__.'/iplistbad.tmp'.' to '.__DIR__.'/iplistbad'."\n";
	rename(__DIR__.'/iplistbad.tmp', __DIR__.'/iplistbad');
}
catch (Exception $e)
{
	echo 'Error Caught !'."\n";
	echo $e->getMessage()."\n";
}
