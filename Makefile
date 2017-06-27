ifndef DOCS_JELIX_ORG_DEPLOY_TARGET
    DOCS_JELIX_ORG_DEPLOY_TARGET=/tmp/docs.jelix.org
endif

distfiles= doc_en/var/config/profiles.ini.php \
           doc_fr/var/config/profiles.ini.php \
           doc_en/var/config/localconfig.ini.php \
           doc_fr/var/config/localconfig.ini.php

$(distfiles):
	cp $@.dist $@

.PHONY: build
build: clean $(distfiles)

.PHONY: clean
clean:
	rm -f $(distfiles)

.PHONY: deploy
deploy: build
	rsync -av --delete --ignore-times --checksum --include-from=.build-files ./ $(DOCS_JELIX_ORG_DEPLOY_TARGET)

