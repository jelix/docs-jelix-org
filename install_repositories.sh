#!/bin/bash
## Usage: ./install_repositories.sh [account]

account=${1-jelix}
echo "Setting github account to $account..."

if [ ! -d repositories/en/jelix-manual-en ]; then
  (
    cd repositories/en/
    if test $account != "jelix"; then
      git clone git@github.com:${account}/jelix-manual-en.git
      git fetch
    else
      git clone https://github.com/jelix/jelix-manual-en.git
    fi

    cd jelix-manual-en
    git checkout -b jelix-1.0 remotes/origin/jelix-1.0
    git checkout -b jelix-1.1 remotes/origin/jelix-1.1
    git checkout -b jelix-1.2 remotes/origin/jelix-1.2
    git checkout -b jelix-1.3 remotes/origin/jelix-1.3
    git checkout -b jelix-1.4 remotes/origin/jelix-1.4
    git checkout -b jelix-1.5 remotes/origin/jelix-1.5
    git checkout -b jelix-1.6 remotes/origin/jelix-1.6
    git checkout -b jelix-1.7 remotes/origin/jelix-1.7
    git checkout -b jelix-1.8 remotes/origin/jelix-1.8
    git checkout master

    if test $account != "jelix"; then
      git remote add upstream https://github.com/jelix/jelix-manual-en.git
    fi
  )
fi

if [ ! -d repositories/fr/jelix-manuel-fr ]; then
  (
    cd repositories/fr/
    if test $account != "jelix"; then
      git clone git@github.com:${account}/jelix-manuel-fr.git
      git fetch
    else
      git clone https://github.com/jelix/jelix-manuel-fr.git
    fi

    cd jelix-manuel-fr
    git checkout -b jelix-1.0 remotes/origin/jelix-1.0
    git checkout -b jelix-1.1 remotes/origin/jelix-1.1
    git checkout -b jelix-1.2 remotes/origin/jelix-1.2
    git checkout -b jelix-1.3 remotes/origin/jelix-1.3
    git checkout -b jelix-1.4 remotes/origin/jelix-1.4
    git checkout -b jelix-1.5 remotes/origin/jelix-1.5
    git checkout -b jelix-1.6 remotes/origin/jelix-1.6
    git checkout -b jelix-1.7 remotes/origin/jelix-1.7
    git checkout -b jelix-1.8 remotes/origin/jelix-1.8
    git checkout master

    if test $account != "jelix"; then
      git remote add upstream https://github.com/jelix/jelix-manuel-fr.git
    fi
  )
fi
