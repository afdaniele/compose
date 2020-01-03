# Created by Andrea F. Daniele
# ----------------------------------------

DEFAULT_ARCH=amd64
ARCH=${DEFAULT_ARCH}
IMAGE=afdaniele/compose
DOCKERFILE=Dockerfile

JENKINS_BRANCH=$(lastword $(subst /, ,${GIT_BRANCH}))

HEAD_TAG=$(shell git describe --exact-match --tags HEAD 2> /dev/null || :)
LATEST_TAG=$(shell git tag | tail -1)
HEAD_NAME=$(shell [ "${HEAD_TAG}" = "" ] && git rev-parse --abbrev-ref HEAD || echo "${HEAD_TAG}")
BRANCH_NAME=$(shell [ "${HEAD_NAME}" = "HEAD" ] && echo "${JENKINS_BRANCH}" || echo "${HEAD_NAME}")
TAG=$(shell [ "${HEAD_TAG}" = "${LATEST_TAG}" ] && echo "latest" || echo "${BRANCH_NAME}")
EXTRA_TAG=$(shell [ "${ARCH}" = "${DEFAULT_ARCH}" ] && echo "-t ${IMAGE}:${TAG}" || echo "")
IMAGE_SHA=$(shell docker inspect --format="{{index .Id}}" "${IMAGE}:${BRANCH_NAME}-${ARCH}" 2> /dev/null || :)


build:
	docker build \
		-t "${IMAGE}:${BRANCH_NAME}-${ARCH}" \
		-t "${IMAGE}:${TAG}-${ARCH}" \
		${EXTRA_TAG} \
		-f "${DOCKERFILE}" \
		--build-arg ARCH=${ARCH} \
		--build-arg COMPOSE_VERSION=${BRANCH_NAME} \
		./

push:
	docker push "${IMAGE}:${BRANCH_NAME}-${ARCH}"
	docker push "${IMAGE}:${TAG}-${ARCH}"
	@if [ "${ARCH}" = "${DEFAULT_ARCH}" ]; then \
    docker push "${IMAGE}:${TAG}"; \
  fi

pull:
	docker pull "${IMAGE}:${BRANCH_NAME}-${ARCH}" || :

clean:
	docker rmi -f "${IMAGE_SHA}" || :

bump:
	./bump-version.sh
