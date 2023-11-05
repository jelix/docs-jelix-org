
distfiles= doc_en/var/config/profiles.ini.php \
           doc_fr/var/config/profiles.ini.php \
           doc_en/var/config/localconfig.ini.php \
           doc_fr/var/config/localconfig.ini.php

$(distfiles):
	cp $@.dist $@

.PHONY: build
build: clean $(distfiles)
	composer install --prefer-dist --no-dev --no-progress --no-suggest --no-ansi --no-interaction --working-dir=lib/

.PHONY: clean
clean:
	rm -f $(distfiles)

.PHONY: deploy
deploy: build
	rsync -av --delete --ignore-times --checksum --include-from=.build-files ./ $(DOCS_JELIX_ORG_DEPLOY_SSH):$(DOCS_JELIX_ORG_DEPLOY_DIR)
	ssh $(DOCS_JELIX_ORG_DEPLOY_SSH) 'cd $(DOCS_JELIX_ORG_DEPLOY_DIR) && ./update.sh'


