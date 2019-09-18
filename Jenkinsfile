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
        sh 'make build ARCH=${ARCH}'
      }
    }
    stage('Push Image') {
      steps {
        withDockerRegistry(credentialsId: 'DockerHub', url: 'https://index.docker.io/v1/') {
          sh 'make push ARCH=${ARCH}'
        }
      }
    }
    stage('Clean up') {
      steps {
        sh 'make clean ARCH=${ARCH}'

        cleanWs()
      }
    }
  }
}
