Production deployment
=====================


- It needs https://github.com/jelix/jelix-design. It contains files for the design (css, png...). The apache/nginx
  configuration should contain an alias /design to the www/ directory of jelix-design.

- To allow easily to remove temp files, put your login into a group, "devweb" for example, and:
   - run with root rights: `set_rights.sh devweb`
   - in /etc/apache2/envvars, add this line: `umask 002`

- link (or copy or create an alias into the web server virtualhost) lib/jelix-www to www/jelix

- create an environment variable DOCS_JELIX_ORG_DEPLOY_TARGET containing a target
  for rsync. Ex: `export DOCS_JELIX_ORG_DEPLOY_TARGET=deploy@my.machine:/some/where`.
  Then launch `make deploy`.

To install Manuals
==================

- run `install_repositories.sh`
- or run `install_repositories.sh login`
  where login is your github login, if you have forked manuals repositories

