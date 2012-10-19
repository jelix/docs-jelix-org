This is the http://docs.jelix.org website. It displays manuals of the Jelix framework.
It uses [Gitiwiki](https://github.com/laurentj/gitiwiki).


To install applications
=======================

- clone the repository https://github.com/jelix/jelix-design. It contains files for the design (css, png...).
 
- in your apache configuration, declare an alias /design to the www/ directory of jelix-design

- To allow easily to remove temp files, put your login into a group, "devweb" for example, and:
   - run with root rights: `set_rights.sh devweb`
   - in /etc/apache2/envvars, add this line: `umask 002`

- copy doc_en/var/config/defaultconfig.ini.php.dist to doc_en/var/config/defaultconfig.ini.php and change it if needed

- copy doc_fr/var/config/defaultconfig.ini.php.dist to doc_en/var/config/defaultconfig.ini.php and change it if needed

- copy doc_en/var/config/profiles.ini.php.dist to doc_en/var/config/profiles.ini.php and change it if needed

- copy doc_fr/var/config/profiles.ini.php.dist to doc_en/var/config/profiles.ini.php and change it if needed

- link (or copy) lib/jelix-www to www/jelix

- run the script `update_appli.sh`, it will install applications.

To install Manuals
==================

- run `install_repositories.sh`
- or run `install_repositories.sh login`
  where login is you github login, if you have forked manuals repositories

