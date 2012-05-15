#!/bin/bash

cd repositories/en/
git clone git://github.com/jelix/jelix-manual-en.git
cd jelix-manual-en
git checkout -b jelix-1.0 remotes/origin/jelix-1.0
git checkout -b jelix-1.1 remotes/origin/jelix-1.1
git checkout -b jelix-1.2 remotes/origin/jelix-1.2
git checkout -b jelix-1.3 remotes/origin/jelix-1.3
git checkout master

cd ../../fr/
git clone git://github.com/jelix/jelix-manuel-fr.git

cd jelix-manuel-fr
git checkout -b jelix-1.0 remotes/origin/jelix-1.0
git checkout -b jelix-1.1 remotes/origin/jelix-1.1
git checkout -b jelix-1.2 remotes/origin/jelix-1.2
git checkout -b jelix-1.3 remotes/origin/jelix-1.3
git checkout master

cd ../..

