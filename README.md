# Database browser for the WoW Alpha Core project

This is intended for browsing the database used in: <https://github.com/The-Alpha-Project/alpha-core/>

* On the main menu click on one of the main parts of the database to browse.
* Then you can enter a filter (search) selection by entering some text. This is treated as a regular expression.
* Alternatively, you can enter a database ID (number) directly to go to that item. For example, 4949 to view Thrall.
* You can further filter on numeric fields (eg. items above a certain level)

A public implementation of this code is at: <https://db.thealphaproject.eu/>

If that goes down, see below for how to install it yourself.

---

## Filtering

The main tables can be filtered by text or numeric comparisons, reducing the number of rows returned to those that match the filter.

### Text filter

The "Filter" box can be used to filter on text. This is used to search descriptions, names, and so on. It is not used to search numeric fields. You can enter:

* Nothing. If the filter is left blank, all rows will be returned, subject to the secondary filter described below.

* A number. This will be used to go straight to that database record by key. For example, enter "257" to see the quest "A Hunter's Boast". This is useful for situations where you know the database key (from looking at another table) and simply want to look it up.

* A regular expression.
    * This will search text fields for a match on the regular expression.
    * For tables with multiple text fields (like quests) a match on any of them will suffice.
    * The website [Regular Expressions 101](https://regex101.com/) can be used to experiment with regular expressions.
    * Do not worry too much about the details of regular expressions. Simple text just matches itself, for example enter "Dark Threat" to match any quest with that in its title or description.
    * If you happen to want to search for a pure number, like all quests with "10" in the description, put it in brackets, eg. "(10)" to stop the browser from trying to look up record key 10.
    * Searches are not case-sensitive.

### Numeric comparison filter (secondary filter)

In addition to searching for text, you can narrow down results by using the secondary filter "Also match:" beneath the filter box. This lets you choose *any* field from that database table, and compare it to a number.

You select the fieldname from a drop-down list, for example: "Effect_1". Then choose a comparison (eg. equal, not equal, greater) and then enter a number which is the comparison value. So, you might have: "if Effect_1 equal 36" (learn spell).

The field you choose is added as a right-hand column to the columns displayed, so you can see what its value was. This is more useful for comparisons like "greater than".

In fact, you could choose to use this to simply view some column, like "min_level" to simply see what it is. To do this just enter a comparison that will always be true. One such comparison is "masked by all bits: 0".

### Comparing for masks

The "masked by" comparisons need a bit of explanation.

* Masked by any bit: This matches if any bit in the selected field matches **one** of the mask bits. That is: `(field & mask) != 0`. So, for example for spells "Targets" "masked by any bit" 0x4010 would return any spells that target "Game Object Item" (0x4000) **or** "Item" (0x10).

* Not masked by any bit: This is the inverse of the above. Thus it matches if **none** of the mask bits match. That is: `(field & mask) == 0`.

* Masked by all bits: This matches if **all** the bits in the selected field matches **all** of the mask bits. That is: `(field & mask) == mask`

* Not masked by all bits: This is the inverse of the above. Thus it matches if `(field & mask) != mask`

The number to compare to can be entered in:

* Decimal, eg. 666
* Floating-point, eg. 12.34
* Hex, eg. 0xBADBAD
* Binary, eg. 0b010011

To avoid the effect of the secondary filter just leave the "comparison value" box empty.

**Warning**: Comparing floating-point values (eg. map coordinates) for equality may fail due to implementation issues in the SQL server. Comparing floating-point numbers to be exactly equal to each other is notoriously difficult.

#### Comparing for "in set" or "not in set"

Another comparison is "in set" or "not in set". This allows you to supply a comma-separated list of values you want to compare to. For example, you could compare faction to "in set: 21,87" which would return rows which have faction either as 21 or 87. "Not in set" would return the inverse result.

#### Comparing for a range

You can also compare for a range. For example "10 to 20". In this case you must supply two numbers with the word "to" inbetween them, including a space on each side of the word "to". The range is inclusive. The comparison "not in range" would return the inverse result.

---

## Proximity search

From the main menu you can find a link to do "Spawn point proximity search". This lets you enter in (or paste in) any coordinates the game world (including the map number) and it will calculate which NPCs spawn within a certain distance of that location (default is 100 yards).

For example, Mankrik is at "-520.983 -2641.41 95.8708 1". Just copy those coordinates (inside the quotes) and paste as the source location, then click "Search". At present I find 24 matches within 100 yards of Mankrik.

This could be useful for:

* Checking nearby NPCs are where they should be.
* Finding NPCs that are improperly spawned inside walls, under the ground, etc.
* Finding NPCs that should not be there (in an otherwise uninhabited zone).

---

## Generating SQL

If you are planning to submit an update to the database via the Alpha Core GitHub site, you can easily generate a change to a single value like this:

* Find a specific database entry (eg. a single spell, creature, quest). This will list all of the columns from the database in two columns: field-name / value.
* Alt+LH-Click on a particular entry (eg. "min_money_loot")
* At the head of the fields listing, an SQL "update" statement will appear, like this:

    ```
    UPDATE `item_template` SET `min_money_loot` = xxxx WHERE (`entry` = 2462);
    ```

* Triple-click to copy that line to the clipboard and paste into whatever editor you are using for submitting updates.
* Change the "xxxx" part to whatever new value you want.
* Submit the SQL to the Alpha Project.

---

## How to install on your own server


### TLDR:

* Grab this code
* Grab the databases from <https://github.com/The-Alpha-Project/alpha-core/tree/master/etc/databases/> and install them
* Rename the file "wow_alpha_config.php.dist" as "wow_alpha_config.php"
* In that file, put in your details (database username, password, host address)
* Make a soft link pointing the web server to "query_wow_alpha.php" (ie. link that to "index.php")
* Done!

---


1. Put all the files into a folder on your server, somewhere inside the document root. Personally I put them in:

    ```
    /var/www/html/alpha
    ```

    ... where `/var/www/html/` is the web server document root.

    The "icons" and "creatures" folders would remain sub-folders of that main folder.

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

6. Import the databases from the SQL files in the links above into the appropriate databases, eg. Start MySQL client and then:

    First time, make the databases so you can "use" them:

    ```
    create database alpha_world;
    create database alpha_dbc;
    ```

    Then:

    ```
    use alpha_world;
    source etc/databases/world/world.sql
    source etc/databases/world/updates/updates.sql

    use alpha_dbc;
    source etc/databases/dbc/dbc.sql
    source etc/databases/dbc/updates/updates.sql
    ```

    These instructions assume that you have downloaded the alpha-core project from GitHub and are in the "alpha-core" directory.

    Note that if you are using MySQL server (and not MariaDB server) then the updates.sql files will (probably) not be processed properly because they have "if" statements in them which MySQL server doesn't recognise. In that case you can strip them out, like this:

    ```
    sed -E '/(^delimiter)|(^\s+if)|(^begin)|(^\s*end)/d' \
        etc/databases/world/updates/updates.sql \
      > etc/databases/world/updates/fixed_world_updates.sql

    sed -E '/(^delimiter)|(^\s+if)|(^begin)|(^\s*end)/d' \
        etc/databases/dbc/updates/updates.sql \
      > etc/databases/dbc/updates/fixed_dbc_updates.sql
    ```

    Then, "source" those two files you just created (fixed_world_updates.sql and fixed_dbc_updates.sql) instead of "updates.sql" as in the instructions above.

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

## Recent changes

A "card" on the main menu shows the most recent commits to the browser. This may silently fail if the web server does not have ownership of the Git repository. You may need to do this (in the top-level "alpha" directory):

```
sudo chgrp -R www-data .git
```

If there is an error in obtaining the Git information the error message will be placed as an HTML comment in the page source.

---

## How to apply updates

To update the database browser, just connect to the appropriate directory on your web server (eg. /var/www/html/alpha) and do:

```
git pull
```

To update the database contents themselves, if they are changed by The-Alpha-Project, connect to whereever you placed the The-Alpha-Project files and do a "git pull". Then repeat step 6 above to replace the databases with the updated ones.

---

## Disclaimer

The data shown is not the actual data used by Blizzard. It has been deduced by observing the game by many people, over time. People have noted spawn points, loot drops, and other behaviour, and then created a database which is supposedly similar to the one used by Blizzard. However there may well be discrepancies.
