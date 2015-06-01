.. contents::

==============
OLIF Framework
==============
.. image:: https://poser.pugx.org/olif-fm/olif/v/stable
    :target: https://packagist.org/packages/olif-fm/olif
.. image:: https://poser.pugx.org/olif-fm/olif/downloads
    :target: https://packagist.org/packages/olif-fm/olif
.. image:: https://poser.pugx.org/olif-fm/olif/v/unstable
    :target: https://packagist.org/packages/olif-fm/olif
.. image:: https://poser.pugx.org/olif-fm/olif/license
    :target: https://packagist.org/packages/olif-fm/olif
.. image:: https://insight.sensiolabs.com/projects/6c8d26f4-8096-460b-be1a-40ca2979f166/mini.png
    :target: https://insight.sensiolabs.com/projects/6c8d26f4-8096-460b-be1a-40ca2979f166

Information
===========
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
