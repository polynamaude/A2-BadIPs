<?php

/**
 * Name of lockfile
 * @var string $lockfile
 */

$lockfile = 'iplist.txt.lock';

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
	$webClient = new \GuzzleHttp\Client();

	try {
		if (file_exists(__DIR__.'/iplist.txt.tmp'))
		{
			unlink(__DIR__.'/iplist.txt.tmp');
		}
		echo 'Opening file for writing temporary ip list'."\n".__DIR__.'/iplist.txt.tmp'."\n";
		$outfile = fopen(__DIR__.'/iplist.txt.tmp','w+');
		echo 'Connecting to https://www.blocklist.de/ for blacklisted ips'."\n";
		$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();
		echo 'Writing ips to file'."\n";
		while ($blackip = fgets($infile))
		{
			fwrite ($outfile, rtrim($blackip,"\r\n")."\n");
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
			fwrite ($outfile, rtrim($blackip,"\r\n")."\n");
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
		echo 'Renaming '.__DIR__.'/iplist.txt.tmp'.' to'."\n".__DIR__.'/iplist.txt'."\n";
		rename(__DIR__.'/iplist.txt.tmp', __DIR__.'/iplist.txt');
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
}