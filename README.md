Referee
=======

Referee is a simple tool for refactoring legacy PHP code, based on the strategies presented in Paul M. Jones' book [Modernizing Legacy Applications in PHP](https://leanpub.com/mlaphp).

Installation
------------

The preferred installation method is [composer](https://getcomposer.org);

```sh
composer require-dev elvanto/referee
```

Usage
-----

A list of valid commands can be found by running ``vendor/bin/referee list``.  Before executing any of the commands, it is recommended that you are using a version control system with a clean working copy. This will ensure that any unwanted changes can be rolled back without affecting prior changes.  

Using the ``--dry-run`` flag with any of the commands will result in changes being reported without any files being updated.

### Extracting static classes from function files

The ``extract-class`` command takes the name of a file containing function definitions and generates a namespaced class with equivalent static methods.  Any usages of the original functions in the ``<search>`` directories will be replaced with appropriate static method calls on the newly generated class.  All files are changed in place.

Further commands will be added in future.

Notes
-----

Referee uses the excellent [PHP-Parser](https://github.com/nikic/PHP-Parser) by Nikita Popov for parsing and generating source code. While the resulting code attempts to maintain some of the original formatting of the file (such as line spacing between statements) through a custom pretty-printer, not all formatting is guaranteed to stay consistent.

License
-------
[MIT License](LICENSE)
