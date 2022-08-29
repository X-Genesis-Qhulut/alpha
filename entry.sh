#!/bin/sh

BASEDIR=/opt/alpha

mkdir -p $BASEDIR
cd $BASEDIR 

if [ ! -f "$BASEDIR/wow_alpha_config.php" ]; then
  cp $BASEDIR/wow_alpha_config.php.dist $BASEDIR/wow_alpha_config.php
fi

if [ ! -f "$BASEDIR/index.php" ]; then
    ln -s $BASEDIR/query_wow_alpha.php $BASEDIR/index.php
fi

# /bin/sh
php -S 0.0.0.0:3000
