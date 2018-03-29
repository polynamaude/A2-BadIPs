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
$lockfile = 'iplistdbm.lock';

require_once 'vendor/autoload.php';

if (file_exists(__DIR__.'/'.$lockfile) || file_exists(__DIR__.'/iplistdbm.lck'))
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
	if (file_exists(__DIR__.'/iplistdbm.tmp'))
	{
		unlink(__DIR__.'/iplistdbm.tmp');
	}
	echo 'Opening database file for writing temporary ip list'."\n".__DIR__.'/iplistdbm.tmp'."\n";
	$db = dba_popen(__DIR__.'/iplistdbm.tmp','cl','db4');
	if ($db != FALSE)
	{
		try {
			echo 'Connecting to https://www.blocklist.de/ for blacklisted ips'."\n";
			$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();
			echo 'Writing ips to file'."\n";
			while ($blackip = fgets($infile))
			{
				dba_insert(rtrim($blackip), $bankey, $db);
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
				dba_insert(rtrim($blackip), $bankey, $db);
			}
		}
		catch (Exception $e)
		{
			echo 'Error Caught !'."\n";
			echo $e->getMessage()."\n";
		}
		try
		{
			dba_close($db);
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
		echo 'Can\'t open database file'."\n";
		dba_close($db);
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
	if (file_exists(__DIR__.'/iplistdbm.lck'))
	{
		unlink(__DIR__.'/iplistdbm.lck');
		echo 'Removed database lockfile'."\n";
	}
	exit(255);
}
