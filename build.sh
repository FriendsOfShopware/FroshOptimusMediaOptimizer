#!/usr/bin/env bash

commit=$1
if [ -z ${commit} ]; then
    commit=$(git tag --sort=-creatordate | head -1)
    if [ -z ${commit} ]; then
        commit="master";
    fi
fi

# Remove old release
rm -rf TinectOptimusOptimizer TinectOptimusOptimizer-*.zip

# Build new release
mkdir -p TinectOptimusOptimizer
git archive ${commit} | tar -x -C TinectOptimusOptimizer
composer install --no-dev -n -o -d TinectOptimusOptimizer
( find ./TinectOptimusOptimizer -type d -name ".git" && find ./TinectOptimusOptimizer -name ".gitignore" && find ./TinectOptimusOptimizer -name ".gitmodules" ) | xargs rm -r
zip -r TinectOptimusOptimizer-${commit}.zip TinectOptimusOptimizer