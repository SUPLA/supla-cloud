#!/usr/bin/env bash

# example command to release version 22.07: ./release 22.07

cd "$(dirname "$0")"

if [ $# -eq 1 ]
  then
    export RELEASE_VERSION=$1
fi

# export BUILDKIT_PROGRESS=plain

DOCKER_BUILDKIT=1 docker build --build-arg RELEASE_FILENAME=$RELEASE_FILENAME --build-arg RELEASE_VERSION="$RELEASE_VERSION" --file release.Dockerfile --output . .
