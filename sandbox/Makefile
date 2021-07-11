# Created by Andrea F. Daniele
# ----------------------------------------

DEFAULT_ARCH=amd64
ARCH=${DEFAULT_ARCH}
IMAGE=afdaniele/compose
VERSION=latest
H=unix:///var/run/docker.sock
CONTAINER_NAME=compose-sandbox
MKFILE_FPATH=$(abspath $(lastword $(MAKEFILE_LIST)))
DIR=$(patsubst %/,%,$(dir $(MKFILE_FPATH)))
EXTRA_ARGS=

run:
	mkdir -p "${DIR}/user-data"
	docker -H=${H} run \
		-d \
		--rm \
		-p 8888:80/tcp \
		-v "${DIR}/user-data:/user-data" \
		${EXTRA_ARGS} \
		--name "${CONTAINER_NAME}" \
		"${IMAGE}:${VERSION}-${ARCH}"

run-mount:
	make run EXTRA_ARGS='-v "${DIR}/..:/var/www/html:ro"'

stop:
	docker stop "${CONTAINER_NAME}"
