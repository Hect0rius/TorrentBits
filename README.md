# TBDev
A Modern take on the original 2010 PHP version, updated to be more up to date.

# Installation
Firstly create a new database called TBDev, with mysql or mariadb, then correct the conifg.php and announce.php with database information.

Then to populate the database upload SQL/tb.sql and then the rest of them in any old way aslong as tb.sql is first.

Upload to your www dir the entire folder, minus SQL, then signup a account and it will make you a sysop :)

# Changes from 2010 Version.
17/01/2019 - Changed TBDEV.NET to TBDEV.info.

15/01/2019 - Cleaned up announce.php (added mysql.php) added ob_start() to start to trim extra shit that invalidates the output..

15/01/2019 - Cleaned up download.php, added ob_start to trim the extra shit that invalidates the torrent file.

15/01/2019 - Fixed up browse.php, updated mysql_fetch_assoc's to remove MYSQL_NUM as this is not needed.

15/01/2019 - inserted mysql.php to override original mysql functions to mysqli.
