# Created by Andrea F. Daniele
# ----------------------------------------

DEFAULT_ARCH=amd64
ARCH=${DEFAULT_ARCH}
IMAGE=afdaniele/compose
VERSION=devel
H=unix:///var/run/docker.sock
CONTAINER_NAME=compose-sandbox
MKFILE_FPATH=$(abspath $(lastword $(MAKEFILE_LIST)))
DIR=$(patsubst %/,%,$(dir $(MKFILE_FPATH)))
EXTRA_ARGS=
COMPOSE_PY_ARGS=

run:
	mkdir -p "${DIR}/user-data"
	docker -H=${H} run \
		-d \
		--rm \
		-p 8888:80/tcp \
		-v "${DIR}/user-data:/user-data" \
		${EXTRA_ARGS} \
		${COMPOSE_PY_ARGS} \
		--name "${CONTAINER_NAME}" \
		"${IMAGE}:${VERSION}-${ARCH}"

run-mount:
	make run EXTRA_ARGS='-v "${DIR}/..:/var/www/html:ro"'

run-mount-py:
	make run EXTRA_ARGS='-v "${DIR}/..:/var/www/html:ro"' COMPOSE_PY_ARGS='-v "${DIR}/../../compose-python/include/compose_cms:/usr/local/lib/python3.5/dist-packages/compose_cms"'

stop:
	docker stop "${CONTAINER_NAME}"

enter:
	docker exec -it "${CONTAINER_NAME}" bash