# Created by Andrea F. Daniele
# ----------------------------------------

DEFAULT_ARCH=amd64
ARCH=${DEFAULT_ARCH}
IMAGE=afdaniele/compose
TAG=latest
COMPOSE_VERSION=stable
DOCKERFILE=Dockerfile

devel-build:
	${MAKE} build DOCKERFILE=Dockerfile.devel TAG=devel

release-build:
	${MAKE} build TAG=`git describe --tags --abbrev=0` COMPOSE_VERSION=`git describe --tags --abbrev=0`

release-push:
	${MAKE} push TAG=`git describe --tags --abbrev=0`

release-pull:
	${MAKE} pull TAG=`git describe --tags --abbrev=0`

release-clean:
	${MAKE} clean TAG=`git describe --tags --abbrev=0`

build:
	set -e; \
	docker build \
		-t "${IMAGE}:${TAG}-${ARCH}" \
		-f "${DOCKERFILE}" \
		--build-arg ARCH=${ARCH} \
		--build-arg COMPOSE_VERSION=${COMPOSE_VERSION} \
			./; \
	if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker tag \
			"${IMAGE}:${TAG}-${ARCH}" \
			"${IMAGE}:${TAG}"; \
  fi

push:
	set -e; \
	docker push "${IMAGE}:${TAG}-${ARCH}"; \
	if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker push "${IMAGE}:${TAG}"; \
  fi

pull:
	set -e; \
	docker pull "${IMAGE}:${TAG}-${ARCH}" || :; \
	if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker pull "${IMAGE}:${TAG}" || :; \
  fi

clean:
	docker rmi "${IMAGE}:${TAG}-${ARCH}" || :; \
	if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker rmi "${IMAGE}:${TAG}" || :; \
  fi

bump:
	./bump-version.sh
