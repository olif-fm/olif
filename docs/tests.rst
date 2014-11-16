Tests
=====
Run in your terminal:

.. sourcecode:: sh

    phpunit --configuration ./phpunit.xml.dist


Generate a coverage report:

::

    phpunit --configuration ./travis.phpunit.xml.dist --coverage-html ./reports


Jenkins
-------

Travis
------
olif/tests/olif_test.php have the database conf to run in Travis. If you want to run your tests. You must comment this lines.
More info to Test, travis and DDBB:

.. sourcecode:: sh

    http://docs.travis-ci.com/user/database-setup/#MySQL

* If travis don't create mysql tables, you could run in a terminal:

.. sourcecode:: sh

    mysql -e "create database IF NOT EXISTS olif_example;" -uroot; 
    mysql -uroot -h localhost olif_example < sql_base.sql; 
    mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'olif_user'@'localhost' IDENTIFIED BY 'test1234' WITH GRANT OPTION;" -uroot;   