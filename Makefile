# Created by Andrea F. Daniele
# ----------------------------------------

ARCH="amd64"
IMAGE="afdaniele/compose"
TAG="latest"

build:
	@docker build -t "${IMAGE}:${TAG}-${ARCH}" ./

push:
	@docker push "${IMAGE}:${TAG}-${ARCH}"

pull:
	@docker pull "${IMAGE}:${TAG}-${ARCH}"
