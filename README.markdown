PHP ROS
=======

PHP ROS is a [PHP-GTK](http://gtk.php.net)-based reservation system for hotels,
motels, campgrounds and other acommodations businesses.  
While the primary graphical interface is based upon PHP-GTK, the aim is to have
it agnostic to the graphical interface being used; additional interfaces, such
as a web interface, can simply hook into the codebase as a library.

Installation
------------

Since this software is still in development, installation is not very well
polished.

You will require PHP-GTK >= 2.0.1 and PDO-MySQL for the default setup.

You should first load the phpros.mysql schema into a new database.
Then, create a database.cfg file in the root directory as per the example
configuration.

After the database is available, you should simply be able to invoke main.php
to start the application.
