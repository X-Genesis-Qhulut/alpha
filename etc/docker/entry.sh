#!/bin/sh

BASEDIR=/opt/alpha

mkdir -p $BASEDIR
cd $BASEDIR 

if [ ! -f "$BASEDIR/wow_alpha_config.php" ]; then
  # cp $BASEDIR/wow_alpha_config.php.dist $BASEDIR/wow_alpha_config.php

  cat <<EOF >>wow_alpha_config.php
<?php
define ('DBSERVER',     'sql');
define ('DBUSER',       'root');
define ('DBPASSWORD',   'pwd');
define ('DBC_DBNAME',   'alpha_dbc');
define ('WORLD_DBNAME', 'alpha_world');

// where this code is relative to the server document root
define ('EXECUTIONDIR', './');

// make this true if you are using MySQL database server which
//  forces table names to lower-case
define ('LOWER_CASE_SQL_TABLES', false);
?>
EOF

fi

if [ ! -f "$BASEDIR/index.php" ]; then
    ln -s $BASEDIR/query_wow_alpha.php $BASEDIR/index.php
fi

php -S 0.0.0.0:3000
