makesite framework-mvc
======================

NOTE: UNTIL FURTHER NOTICE, THIS REPO IS *NOT* USABLE AS-IS.
YOU WOULD HAVE TO EDIT SOME FILES TO MAKE IT WORK. THIS IS
A WORK IN PROGRESS AND IS A SUBJECT TO CHANGE.

MVC-like layout for makesite framework.

This repository should give you something akin to CodeIgniter or Kohana
in terms of project layout.

As we favor configuration over convention, this is only one of many
possible uses of the (very) modular components. 

However, sometimes you don't want to spend any time on configuration.
For such cases, you could use this repo.

An alternative convention is [layout-flat](https://github.com/makesite/framework-flat),
which is in it's own repo.

Usage
-----

```
git clone https://github.com/makesite/framework-mvc.git
cd framework-mvc
make init
```

If you wish to create your layout using symlinks, use

```
make layout-dev
```

For hard-copying the files instead of using symlinks, use

```
make layout-dist
```

Finally, to prepare a stand-alone .tar.gz suitable for git-less
distribution, run:

```
make dist
```

Layout
------

```
layout
├── assets
├── controllers
├── core
├── files
├── language
├── models
├── templates
├── config.php
├── config.dev.php
├── db.conf.php
├── install.php
└── index.php
```

Getting out of the webroot
--------------------------

Is definitly possible, but you would have to re-arrange some files
and adjust the APP_DIR and CORE_DIR constants in `config.php`.

Such a layout would be a subject for a different repository in the
future.
