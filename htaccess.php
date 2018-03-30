<?php

/**
 * First string written in rule
 * @var string $frewrite
 */

$frewrite = "RewriteCond %{REMOTE_ADDR} ";

/**
 * Second string written in rule written after IP adress
 * @var string $frewrite2
 */
$frewrite2 = " [NC]";

/**
 * Last line written, howto treat IPs flagged
 * @var string $frewritelast
 */

$frewritelast = "RewriteRule ^.* - [F,L]";

/**
 * Name of lockfile
 * @var string $lockfile
 */

$lockfile = 'iplist.lock';

require_once 'vendor/autoload.php';

if (file_exists(__DIR__.'/'.$lockfile))
{
	echo 'Lockfile exist, please remove and make sure no other instance are running before trying again'."\n";
	echo 'Lockfile name '.__DIR__.'/'.$lockfile."\n";
	exit(254);
}

try {
	$lockhandle = fopen(__DIR__.'/'.$lockfile, 'w+');
}

catch (Exception $e)
{
	echo 'Can\'t create lockfile '.__DIR__.'/'.$lockfile."\n";
	echo 'Error Caught !'."\n";
	echo $e->getMessage()."\n";
	exit(255);
}

try {
	try {
		$webClient = new \GuzzleHttp\Client();
		if (file_exists(__DIR__.'/iplist.tmp'))
		{
			unlink(__DIR__.'/iplist.tmp');
		}
		echo 'Opening file '."\n".__DIR__.'/iplist.tmp'."\n".' for writing temporary ip list'."\n";
		$outfile = fopen('iplist.tmp','w+');
	}

	catch (Exception $e)
	{
		echo 'Error Caught !'."\n";
		echo $e->getMessage()."\n";
		fclose($lockhandle);
		if (file_exists(__DIR__.'/'.$lockfile))
		{
			unlink(__DIR__.'/'.$lockfile);
			echo 'Removing lockfile'."\n";
		}
		exit(255);
	}

	try {
		echo 'Connecting to https://www.blocklist.de/ for blacklisted ips'."\n";
		$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();
		echo 'Writing ips to file'."\n";
		while ($blackip = fgets($infile))
		{
			fwrite ($outfile, $frewrite.rtrim($blackip,"\r\n").$frewrite2."\n");
		}
	}
	catch (Exception $e)
	{
		echo 'Error Caught !'."\n";
		echo $e->getMessage()."\n";
	}
	try {
		echo 'Connecting to https://www.badips.com/ for blacklisted ips'."\n";
		unset($infile);
		$infile = $webClient->get('http://www.badips.com/get/list/ssh/2')->getBody()->detach();
		while ($blackip = fgets ($infile))
		{
			fwrite ($outfile, $frewrite.rtrim($blackip,"\r\n").$frewrite2."\n");
		}
	}
	catch (Exception $e)
	{
		echo 'Error Caught !'."\n";
		echo $e->getMessage()."\n";
	}
	try {
		fwrite($outfile, $frewritelast."\n");
		fclose($outfile);
		echo 'File closed'."\n";
		if (file_exists(__DIR__.'/iplist'))
		{
			echo 'Deleting '.__DIR__.'/iplist'."\n";
			unlink(__DIR__.'/iplist');
		}
		echo 'Renaming '.__DIR__.'/iplist.tmp'.' to'."\n".__DIR__.'/iplist'."\n";
		rename(__DIR__.'/iplist.tmp', __DIR__.'/iplist');
		fclose($lockhandle);
		if (file_exists(__DIR__.'/'.$lockfile))
		{
			unlink(__DIR__.'/'.$lockfile);
			echo 'Removing lockfile'."\n";
		}
		exit(0);
	}
	catch (Exception $e)
	{
		echo 'Error Caught !'."\n";
		echo $e->getMessage()."\n";
		fclose($lockhandle);
		if (file_exists(__DIR__.'/'.$lockfile))
		{
			unlink(__DIR__.'/'.$lockfile);
			echo 'Removing lockfile'."\n";
		}
		exit(255);
	}
}
catch (Exception $e)
{
	echo 'Error Caught !'."\n";
	echo $e->getMessage()."\n";
	fclose($lockhandle);
	if (file_exists(__DIR__.'/'.$lockfile))
	{
		unlink(__DIR__.'/'.$lockfile);
		echo 'Removing lockfile'."\n";
	}
	exit(255);
}