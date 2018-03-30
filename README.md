# A2-BadIPs

Apache 2 .htaccess file generator for blocking black-listed IPs.

The IPs are obtained from two services :

https://www.blocklist.de/downloads/export-ips_all.txt

http://www.badips.com/get/list/ssh/2

They are processed to give 4 different kind of output depending on the actual file runned.

dbmfiles.php give you a dbm format for using with RewriteMap
htaccess.php give you a standard htaccess file including one line for each IPs (RewriteCond) and one last line (RewriteRule)
plainhash.php give you a standard tab based list with a BIP tag (BadIP) for each IP so you can use with RewriteMap using text file
plaintext.php give you a text file with one line per IP.

File produced :

iplist.dbm -> produced by dbmfiles.php
iplist -> produced by htaccess.php
iplist.hash -> produced by plainhash.php
iplist.txt -> produced by plaintext.php


Require GuzzleHTTP
