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
$lockfile = 'iplist.dbm.lock';

require_once 'vendor/autoload.php';

if (file_exists(__DIR__.'/'.$lockfile) || file_exists(__DIR__.'/iplist.dbm.tmp.lck'))
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
	if (file_exists(__DIR__.'/iplist.dbm.tmp'))
	{
		unlink(__DIR__.'/iplist.dbm.tmp');
	}
	echo 'Opening database file for writing temporary ip list'."\n".__DIR__.'/iplist.dbm.tmp'."\n";
	$db = dba_popen(__DIR__.'/iplist.dbm.tmp','cl','db4');
	if ($db !== FALSE)
	{
		try {
			echo 'Connecting to https://www.blocklist.de/ for blacklisted ips'."\n";
			$infile = $webClient->get('https://www.blocklist.de/downloads/export-ips_all.txt')->getBody()->detach();
			echo 'Writing ips to file'."\n";
			while ($blackip = fgets($infile))
			{
				dba_insert(rtrim($blackip,"\r\n"), $bankey, $db);
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
				dba_insert(rtrim($blackip,"\r\n"), $bankey, $db);
			}
		}
		catch (Exception $e)
		{
			echo 'Error Caught !'."\n";
			echo $e->getMessage()."\n";
		}
		try
		{
			dba_sync($db);
			dba_close($db);
			if (file_exists(__DIR__.'/iplist.dbm.tmp.lck'))
			{
				unlink(__DIR__.'/iplist.dbm.tmp.lck');
				echo 'Removed database lockfile'."\n";
			}
			if (file_exists(__DIR__.'/iplist.dbm'))
			{
				echo 'Deleting '.__DIR__.'/iplist.dbm'."\n";
				unlink(__DIR__.'/iplist.dbm');
			}
			echo 'Renaming '.__DIR__.'/iplist.dbm.tmp'.' to'."\n".__DIR__.'/iplist.dbm'."\n";
			rename(__DIR__.'/iplist.dbm.tmp', __DIR__.'/iplist.dbm');
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
			dba_close($db);
			fclose($lockhandle);
			if (file_exists(__DIR__.'/iplist.dbm.tmp.lck'))
			{
				unlink(__DIR__.'/iplist.dbm.tmp.lck');
				echo 'Removed database lockfile'."\n";
			}
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
		dba_sync($db);
		dba_close($db);
		fclose($lockhandle);
		if (file_exists(__DIR__.'/iplist.dbm.tmp.lck'))
		{
			unlink(__DIR__.'/iplist.dbm.tmp.lck');
			echo 'Removed database lockfile'."\n";
		}
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
	if (file_exists(__DIR__.'/iplist.dbm.tmp.lck'))
	{
		unlink(__DIR__.'/iplist.dbm.tmp.lck');
		echo 'Removed database lockfile'."\n";
	}
	exit(255);
}
