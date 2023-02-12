# to runs sakila, first you must download and install the database (the lite version) using:
* schema: sakila_lite
* user: root
* password: abc.123

If you want to change those data, you can edit the configuration sakila2021.config.php

https://github.com/escuelainformatica/Sakila-Database-2021

# then you must run (cli) this operator 

```shell
php ../lib/pdoonecli generate --loadconfig .\sakila2021
```

# Inside the GUI, you can scan for changes (scan)

# And you can create the PHP repository classes.

# Finally, you can save the configuration and exit the program.

# And you can run the test

PdoOne_mysql_sakila2021_Test.php





