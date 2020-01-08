# Created by Andrea F. Daniele
# ----------------------------------------

DEFAULT_ARCH=amd64
ARCH=${DEFAULT_ARCH}
IMAGE=afdaniele/compose

JENKINS_BRANCH=$(lastword $(subst /, ,${GIT_BRANCH}))

HEAD_TAG=$(shell git describe --exact-match --tags HEAD 2> /dev/null || :)
LATEST_TAG=$(shell git tag | tail -1)
HEAD_NAME=$(shell [ "${HEAD_TAG}" = "" ] && git rev-parse --abbrev-ref HEAD || echo "${HEAD_TAG}")
VERSION=$(shell [ "${HEAD_NAME}" = "HEAD" ] && echo "${JENKINS_BRANCH}" || echo "${HEAD_NAME}")
TAG=$(shell [ "${HEAD_TAG}" = "${LATEST_TAG}" ] && echo "latest" || echo "${VERSION}")
EXTRA_TAG=$(shell [ "${ARCH}" = "${DEFAULT_ARCH}" ] && echo "-t ${IMAGE}:${TAG}" || echo "")
IMAGE_SHA=$(shell docker inspect --format="{{index .Id}}" "${IMAGE}:${VERSION}-${ARCH}" 2> /dev/null || :)
DOCKERFILE=$(shell [ "${VERSION}" = "devel" ] && echo "Dockerfile.devel" || echo "Dockerfile")
COMPOSE_VERSION=${VERSION}


build:
	docker build \
		-t "${IMAGE}:${VERSION}-${ARCH}" \
		-t "${IMAGE}:${TAG}-${ARCH}" \
		${EXTRA_TAG} \
		-f "${DOCKERFILE}" \
		--build-arg ARCH=${ARCH} \
		--build-arg COMPOSE_VERSION=${COMPOSE_VERSION} \
		./

push:
	docker push "${IMAGE}:${VERSION}-${ARCH}"
	docker push "${IMAGE}:${TAG}-${ARCH}"
	@if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker push "${IMAGE}:${TAG}"; \
  fi

pull:
	docker pull "${IMAGE}:${VERSION}-${ARCH}" || :

clean:
	docker rmi -f "${IMAGE_SHA}" || :

bump:
	./bump-version.sh

_build_all:
	set -e; \
	TAGS=`git tag | sed -z "s/\n/,/g"`; \
	TAGS=$${TAGS::-1}; \
	brun \
	  -f tag:list:$${TAGS} \
	  -f arch:list:amd64,arm32v7 \
	  -- \
	    make \
	      build \
	      push \
	        VERSION={tag} \
	        ARCH={arch}

_clean_all:
	set -e; \
	TAGS=`git tag | sed -z "s/\n/,/g"`; \
	TAGS=$${TAGS::-1}; \
	brun \
	  -f tag:list:$${TAGS} \
	  -f arch:list:amd64,arm32v7 \
	  -- \
	    make \
	      clean \
	        VERSION={tag} \
	        ARCH={arch}
