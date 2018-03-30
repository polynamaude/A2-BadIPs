# A2-BadIPs

Apache 2 .htaccess file generator for blocking black-listed IPs.

The IPs are obtained from two services :

https://www.blocklist.de/downloads/export-ips_all.txt

http://www.badips.com/get/list/ssh/2

Are the processed to give 4 different kind of output depending on the actual file runned.

dbmfiles.php give you a dbm format for using with RewriteMap
htaccess.php give you a standard htaccess file including two line for each IPs (RewriteCond / RewriteRule)
  The script should and could be easily modified to only use one line for each IP and one line at the end.
plainhash.php give you a standard tab based list with a BIP tag (BadIP) for each IP so you can use with RewriteMap using text file
plaintext.php give you a text file with one line per IP.

File produced :

iplist.dbm -> produced by dbmfiles.php
iplist -> produced by htaccess.php
iplist.hash -> produced by plainhash.php
iplist.txt -> produced by plaintext.php


Require GuzzleHTTP
