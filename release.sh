#!/usr/bin/env bash

cd "$(dirname "$0")"

# export BUILDKIT_PROGRESS=plain

DOCKER_BUILDKIT=1 docker build --build-arg RELEASE_FILENAME=$RELEASE_FILENAME --build-arg RELEASE_VERSION=$RELEASE_VERSION --file release.Dockerfile --output . .
