pipeline {
  agent any
  stages {
    stage('Configure Environment') {
      steps {
        sh 'docker run --rm --privileged multiarch/qemu-user-static:register --reset'
      }
    }
    stage('Build Image') {
      steps {
        sh 'make build ARCH=amd64'
        sh 'make build ARCH=arm32v7'
      }
    }
    stage('Push Image') {
      steps {
        withDockerRegistry(credentialsId: 'DockerHub', url: 'https://index.docker.io/v1/') {
          sh 'make push ARCH=amd64'
          sh 'make push ARCH=arm32v7'
        }
      }
    }
    stage('Clean up') {
      steps {
        sh 'make clean ARCH=amd64'
        sh 'make clean ARCH=arm32v7'

        cleanWs()
      }
    }
  }
}
