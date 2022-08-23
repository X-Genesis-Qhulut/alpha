# Database browser for the WoW Alpha Core project

This is intended for browsing the database used in: https://github.com/The-Alpha-Project/alpha-core/

On the main menu click on one of the main parts of the database to browse.

Then you can enter a filter (search) selection by entering some text. This is treated as a regular expression.

You can enter a database ID (number) directly to go to that item. For example, 4949 to view Thrall. Or just type in "Thrall".

---

## Filtering

The main tables can be filtered by text or numeric comparisons, reducing the number of rows returned to those that match the filter.

### Text filter

The "Filter" box can be used to filter on text. This is used to search descriptions, names, and so on. It is not used to search numeric fields. You can enter:

* Nothing. If the filter is left blank, all rows will be returned, subject to the secondary filter described below.

* A number. This will be used to go straight to that database record by key. For example, enter "257" to see the quest "A Hunter's Boast". This is useful for situations where you know the database key (from looking at another table) and simply want to look it up.

* A regular expression. This will search text fields for a match on the regular expression. For tables with multiple text fields (like quests) a match on any of them will suffice. The website [Regular Expressions 101](https://regex101.com/) can be used to experiment with regular expressions. Do not worry too much about the details of regular expressions. Simple text just matches itself, for example enter "Dark Threat" to match any quest with that in its title or description. If you happen to want to search for a pure number, like all quests with "10" in the description, put it in brackets, eg. "(10)" to stop the browser from trying to look up record key 10.

### Numeric comparison filter (secondary filter)

In addition to searching for text, you can narrow down results by using the secondary filter "Also match:" beneath the filter box. This lets you choose *any* field from that database table, and compare it to a number.

You select the fieldname from a drop-down list, for example: "Effect_1". Then choose a comparison (eg. equal, not equal, greater) and then enter a number which is the comparison number. So, you might have: "if Effect_1 equal 36" (learn spell).

The field you choose is added as a right-hand column to the columns displayed, so you can see what its value was. This is more useful for comparisons like "greater than".

In fact, you could choose to use this to simply view some column, like "min_level" to simply see what it is. To do this just enter a comparison that will always be true. One such comparison is "masked by all bits: 0".

The "masked by" comparisons need a bit of explanation.

* Masked by any bit: This matches if any bit in the selected field matches **one** of the mask bits. That is: `(field & mask) != 0`. So, for example for spells "Targets" "masked by any bit" 0x4010 would return any spells that target "Game Object Item" (0x4000) **or** "Item" (0x10).

* Not masked by any bit: This is the inverse of the above. Thus it matches if **none** of the mask bits match. That is: `(field & mask) == 0`.

* Masked by all bits: This matches if **all** the bits in the selected field matches **all** of the mask bits. That is: `(field & mask) == mask`

* Not masked by all bits: This is the inverse of the above. Thus it matches if `(field & mask) != mask`

To avoid the effect of the secondary filter just leave the "comparison value" box empty.

---

## How to install on your own server

1. Put all the files into a folder on your server, somewhere inside the document root. Personally I put them in:

    ```
    /var/www/html/alpha
    ```

    ... where `/var/www/html/` is the web server document root.

    The "icons" folder would remain a sub-folder of that main folder.

    More specifically, to get started the first time:

    ```
    cd /var/www/html    # for example, whereever your server document root is
    git clone https://github.com/X-Genesis-Qhulut/alpha.git
    cd alpha
    ```


2. The main file to run is: query_wow_alpha.php

3. Make a soft link so that it is the default file to be executed:

    ```
    ln -s query_wow_alpha.php index.php
    ```

4. Create two databases to hold the database information from the databases which you download from the Alpha Project GitHub pages:

    * https://github.com/The-Alpha-Project/alpha-core/tree/master/etc/databases/dbc
    * https://github.com/The-Alpha-Project/alpha-core/tree/master/etc/databases/world


    In other words, using the mysql client:

    ```
    create database alpha_world
    create database alpha_dbc
    ```

5. Download the files from The-Alpha-Project on GitHub if you haven't already:

    ```
    git clone https://github.com/The-Alpha-Project/alpha-core.git
    cd alpha-core
    ```

6. Import the databases from the SQL files in the links above into the appropriate databases, eg.

    ```
    mysql -uUSERNAME -pPASSWORD -h SERVER_ADDRESS alpha_world < etc/databases/world/world.sql
    mysql -uUSERNAME -pPASSWORD -h SERVER_ADDRESS alpha_world < etc/databases/world/updates/updates.sql
    mysql -uUSERNAME -pPASSWORD -h SERVER_ADDRESS alpha_dbc   < etc/databases/dbc/dbc.sql
    mysql -uUSERNAME -pPASSWORD -h SERVER_ADDRESS alpha_dbc   < etc/databases/dbc/updates/updates.sql
    ```

    These instructions assume that you have downloaded the alpha-core project from GitHub and are in the "alpha-core" directory.

    Of course, use your own username, password and server address. If you are running locally the server could be "localhost".

    Perhaps turn the above four lines into a shell script and run that when appropriate.

7. Rename the file wow_alpha_config.php.dist to be wow_alpha_config.php, ie.

    ```
    mv wow_alpha_config.php.dist wow_alpha_config.php
    ```

8. Edit the file wow_alpha_config.php to suit your setup. The default is:

    ```
    <?php
    define ('DBSERVER',     'localhost');
    define ('DBUSER',       'USERNAME');
    define ('DBPASSWORD',   'PASSWORD');
    define ('DBC_DBNAME',   'alpha_dbc');
    define ('WORLD_DBNAME', 'alpha_world');

    // where this code is relative to the server document root
    define ('EXECUTIONDIR', '/alpha');

    // make this true if you are using MySQL database server which
    //  forces table names to lower-case
    define ('LOWER_CASE_SQL_TABLES', true);
    ?>
    ```

    You need to put in that file the username and password for your MySQL account on the server. Also the server address of your MySQL server. Also alter the table names "alpha_dbc" and "alpha_world" to suit the tables you created above. For example my server provider forces me to use different database names than the ones I showed above.

    The constant EXECUTIONDIR should be the name of the directory containing the files from this project, *relative to the Apache document root*.

    If you are using MySQL then LOWER_CASE_SQL_TABLES should be true. If you are using MariaDB then LOWER_CASE_SQL_TABLES should be false.

It should now be ready to run. Try connecting to index.php (or query_wow_alpha.php) in your web browser and you should see the main menu.

---

## How to apply updates

To update the database browser, just connect to the appropriate directory on your web server (eg. /var/www/html/alpha) and do:

```
git pull
```

To update the database contents themselves, if they are changed by The-Alpha-Project, connect to whereever you placed the The-Alpha-Project files and do a "git pull". Then repeat step 6 above to replace the databases with the updated ones.
