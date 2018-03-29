<?php

/**
 * Lookup key we'll be using to map in RewriteMap condition for apache rules
 * @var string $bankey
 */
$bankey = 'BIP';

/**
 * Name of lockfile
 * @var string $lockfile
 */
$lockfile = 'iplist.hash.lock';

require_once 'vendor/autoload.php';

if (file_exists(__DIR__.'/'.$lockfile) || file_exists(__DIR__.'/iplist.hash.lck'))
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

$webClient = new \GuzzleHttp\Client();

try {
	if (file_exists(__DIR__.'/iplist.hash.tmp'))
	{
		unlink(__DIR__.'/iplist.hash.tmp');
	}
	echo 'Opening hash file for writing temporary ip list'."\n".__DIR__.'/iplist.hash.tmp'."\n";
	$db = fopen(__DIR__.'/iplist.hash.tmp','w+');
	if ($db !== FALSE)
	{
		try {
			echo 'Connecting to https://www.blocklist.de/ for blacklisted ips'."\n";
			$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();
			echo 'Writing ips to file'."\n";
			while ($blackip = fgets($infile))
			{
				fwrite($db, rtrim($blackip,"\r\n")."\t".$bankey."\n");
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
				fwrite($db, rtrim($blackip,"\r\n")."\t".$bankey."\n");
			}
		}
		catch (Exception $e)
		{
			echo 'Error Caught !'."\n";
			echo $e->getMessage()."\n";
		}
		try
		{
			fclose($db);
			if (file_exists(__DIR__.'/iplist.hash'))
			{
				echo 'Deleting '.__DIR__.'/iplist.hash'."\n";
				unlink(__DIR__.'/iplist.hash');
			}
			echo 'Renaming '.__DIR__.'/iplist.hash.tmp'.' to'."\n".__DIR__.'/iplist.hash'."\n";
			rename(__DIR__.'/iplist.hash.tmp', __DIR__.'/iplist.hash');
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
	else {
		echo 'Can\'t open hash file'."\n";
		fclose($db);
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
