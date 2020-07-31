# phpze
PHP implementation of the Gigya and Kamareon API for Renault Z.E. vehicles

* `config.php`: Configuration file
* `index.php`: HTML output of vehicle data
* `cron.php`: Cron job for data storage
* `debug.php`: Output JSON responses of API endpoints (`?uncensored` for unaltered output)

## Todo
* `/actions/charging-start` endpoint
* `/actions/hvac-start` endpoint
* Send mail when charging complete
* SQLite
* Graphs and sh*t

## Based on the works by
* [-db-](https://www.goingelectric.de/forum/memberlist.php?mode=viewprofile&u=26446)
* [Muscat](https://muscatoxblog.blogspot.com/2019/07/delving-into-renaults-new-api.html)
* [pype](https://github.com/jamesremuscat/pyze)
