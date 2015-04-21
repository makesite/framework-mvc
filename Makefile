ROOT_DESTDIR="layout"
CORE_DESTDIR="layout/core"
MODELS_DESTDIR="layout/models"

init:
	git submodule init
	git submodule update

clean:
	-@rm $(CORE_DESTDIR)/domtempl.php
	-@rm $(CORE_DESTDIR)/qry5.php
	-@rm $(CORE_DESTDIR)/db.php
	-@rm $(CORE_DESTDIR)/db.orm.php
	-@rm $(CORE_DESTDIR)/common.php
	-@rm $(CORE_DESTDIR)/form.php
	-@rm $(CORE_DESTDIR)/dispatch.php
	-@rm $(MODELS_DESTDIR)/settings.php
	-@rm $(MODELS_DESTDIR)/files.php
	-@rm $(ROOT_DESTDIR)/install.php

layout-dev: init
	-mkdir $(MODELS_DESTDIR)
	-mkdir $(CORE_DESTDIR)
	ln -s ../../submodules/domtempl/domtempl.php $(CORE_DESTDIR)
	ln -s ../../submodules/qry/qry5.php $(CORE_DESTDIR)
	ln -s ../../submodules/pdb/db.php $(CORE_DESTDIR)
	ln -s ../../submodules/pdb/db.orm.php $(CORE_DESTDIR)
	ln -s ../../submodules/varcore/common.php $(CORE_DESTDIR)
	ln -s ../../submodules/varcore/form.php $(CORE_DESTDIR)
	ln -s ../../submodules/varcore/dispatch.php $(CORE_DESTDIR)
	ln -s ../../submodules/pdb/models/settings.php $(MODELS_DESTDIR)
	ln -s ../../submodules/pdb/models/files.php $(MODELS_DESTDIR)
	ln -s ../submodules/varcore/install.php $(ROOT_DESTDIR)

layout-dist: init
	-mkdir $(MODELS_DESTDIR)
	-mkdir $(CORE_DESTDIR)
	cp submodules/domtempl/domtempl.php $(CORE_DESTDIR)
	cp submodules/qry/qry5.php $(CORE_DESTDIR)
	cp submodules/pdb/db.php $(CORE_DESTDIR)
	cp submodules/pdb/db.orm.php $(CORE_DESTDIR)
	cp submodules/varcore/common.php $(CORE_DESTDIR)
	cp submodules/varcore/form.php $(CORE_DESTDIR)
	cp submodules/varcore/dispatch.php $(CORE_DESTDIR)
	cp submodules/pdb/models/settings.php $(MODELS_DESTDIR)
	cp submodules/pdb/models/files.php $(MODELS_DESTDIR)
	cp submodules/varcore/install.php $(ROOT_DESTDIR)

dist:
	tar -chf dist.tar layout/.htaccess layout/**/*.php layout/*.php
