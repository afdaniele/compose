#!/bin/bash

docker build -t afdaniele/compose:latest ./master
docker build -t afdaniele/compose:devel -f ./master/Dockerfile.devel ./master

docker build -t afdaniele/compose-arm32v7:latest ./arm32v7
docker build -t afdaniele/compose-arm32v7:devel -f ./arm32v7/Dockerfile.devel ./arm32v7
