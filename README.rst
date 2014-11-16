.. contents::

======
OLIF Framework
======

Information
===========
.. image:: https://travis-ci.org/olif-fm/olif.svg?token=Y9wApmn2xrsquzm21C6R
	:target: https://travis-ci.org/olif-fm/olif

.. image:: https://coveralls.io/repos/olif-fm/olif/badge.png?branch=master
  :target: https://coveralls.io/r/olif-fm/olif?branch=master

OLIF is a PHP framework to support developers to build fast websites

Documentation
=============
See docs Folder

First install
=============

From new project
----------------
Download Core:

::

    git clone https://github.com/olif-fm/olif.git

Or a Example Project:

::

    git clone https://github.com/olif-fm/olif-example.git

From submodule
--------------

::

    git submodule add https://github.com/olif-fm/olif olif

Install dependencies
--------------------

::

    cd olif/

Install composer:

::

    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

Install composer dependencies:

::

    composer install

Install git submodules:

::

    ./git-submodule.sh

Update git submodules:

::

    git submodule update --init --recursive

See [LINK TO DOCS] to configure PHP settings

