#!/bin/bash
## Usage: ./install_repositories.sh [account]

account=${1-jelix}
echo "Setting github account to $account..."

cd repositories/en/
git clone git://github.com/${account}/jelix-manual-en.git
cd jelix-manual-en
git checkout -b jelix-1.0 remotes/origin/jelix-1.0
git checkout -b jelix-1.1 remotes/origin/jelix-1.1
git checkout -b jelix-1.2 remotes/origin/jelix-1.2
git checkout -b jelix-1.3 remotes/origin/jelix-1.3
git checkout master

if test $account != "jelix"; then
  git remote add upstream git://github.com/jelix/jelix-manual-en.git
fi

cd ../../fr/
git clone git://github.com/${account}/jelix-manuel-fr.git

cd jelix-manuel-fr
git checkout -b jelix-1.0 remotes/origin/jelix-1.0
git checkout -b jelix-1.1 remotes/origin/jelix-1.1
git checkout -b jelix-1.2 remotes/origin/jelix-1.2
git checkout -b jelix-1.3 remotes/origin/jelix-1.3
git checkout master

if test $account != "jelix"; then
  git remote add upstream git://github.com/jelix/jelix-manuel-fr.git
fi

cd ../..

