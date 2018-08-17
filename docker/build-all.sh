#!/bin/bash

docker build -t afdaniele/compose:latest ./master
docker build -t afdaniele/compose:devel ./devel
docker build -t afdaniele/compose-arm32v7:latest ./arm32v7
