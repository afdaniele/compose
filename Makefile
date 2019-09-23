# Created by Andrea F. Daniele
# ----------------------------------------

DEFAULT_ARCH=amd64
ARCH=${DEFAULT_ARCH}
IMAGE=afdaniele/compose
TAG=latest

build:
	set -e; \
	docker build -t "${IMAGE}:${TAG}-${ARCH}" --build-arg ARCH=${ARCH} ./; \
	if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker tag "${IMAGE}:${TAG}-${ARCH}" "${IMAGE}:${TAG}"; \
  fi

push:
	set -e; \
	docker push "${IMAGE}:${TAG}-${ARCH}"; \
	if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker push "${IMAGE}:${TAG}"; \
  fi

pull:
	set -e; \
	docker pull "${IMAGE}:${TAG}-${ARCH}"; \
	if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker pull "${IMAGE}:${TAG}"; \
  fi

clean:
	docker rmi "${IMAGE}:${TAG}-${ARCH}" || :; \
	if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker rmi "${IMAGE}:${TAG}" || :; \
  fi
