#!/usr/bin/env bash
set -Eeuo pipefail
shopt -s nullglob

source .env

usage(){
    cat <<.
Usage: $0 <IMAGE> <REGISTRY_TARGET>"
Example:"
$0 prod infocmdb"
# PUSH IMAGE:  ${COMPOSE_DOCKER_REGISTRY}/infocmdb-{php,cron,db,web}
# TO REGISTRY: <REGISTRY_TARGET>/infocmdb-{php,cron,db,web}:{MAJOR_MINOR_PATCH}"
.
}

GIVEN_IMAGE=${1:-}
if [[ ${GIVEN_IMAGE} == "" ]]; then
  usage
  exit 1
fi

REGISTRY_TARGET=${2:-}
if [[ ${REGISTRY_TARGET} == "" ]]; then
  usage
  exit 1
fi

IMAGE_TAG_MAJOR=$(git describe --abbrev=0 --tags --always | sed -E 's/(^v([0-9]+)\.([0-9]+)\.([0-9]+)$)|^().*$/\2/') || 0
IMAGE_TAG_MINOR=$(git describe --abbrev=0 --tags --always | sed -E 's/(^v([0-9]+)\.([0-9]+)\.([0-9]+)$)|^().*$/\2.\3/') || 0
IMAGE_TAG_PATCH=$(git describe --abbrev=0 --tags --always | sed -E 's/(^v([0-9]+)\.([0-9]+)\.([0-9]+)$)|^().*$/\2.\3.\4/') || 0

if [[ ! "${IMAGE_TAG_MAJOR}" -gt "0" ]]; then
  echo "Failed to parse Version from Git-Tag: $(git describe --abbrev=0 --tags --always)"
  exit 1
fi

echo "Schemantic Version: v${IMAGE_TAG_PATCH}"

echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin

SERVICES="php cron db web"
for service in $SERVICES; do
  docker tag  "${COMPOSE_DOCKER_REGISTRY}/infocmdb-${service}:${GIVEN_IMAGE}" "${REGISTRY_TARGET}/infocmdb-${service}:${IMAGE_TAG_MAJOR}"
  docker tag  "${COMPOSE_DOCKER_REGISTRY}/infocmdb-${service}:${GIVEN_IMAGE}" "${REGISTRY_TARGET}/infocmdb-${service}:${IMAGE_TAG_MINOR}"
  docker tag  "${COMPOSE_DOCKER_REGISTRY}/infocmdb-${service}:${GIVEN_IMAGE}" "${REGISTRY_TARGET}/infocmdb-${service}:${IMAGE_TAG_PATCH}"

  docker push "${REGISTRY_TARGET}/infocmdb-${service}:${IMAGE_TAG_MAJOR}"
  docker push "${REGISTRY_TARGET}/infocmdb-${service}:${IMAGE_TAG_MINOR}"
  docker push "${REGISTRY_TARGET}/infocmdb-${service}:${IMAGE_TAG_PATCH}"
done

