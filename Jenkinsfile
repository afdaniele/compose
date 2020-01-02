pipeline {
  agent any
  stages {
    stage('Configure Environment') {
      steps {
        sh 'docker run --rm --privileged multiarch/qemu-user-static:register --reset'
      }
    }
    stage('Pull Image') {
      steps {
        sh 'make smart-pull ARCH=${ARCH}'
      }
    }
    stage('Build Image') {
      steps {
        sh 'make smart-build ARCH=${ARCH}'
      }
    }
    stage('Push Image') {
      steps {
        withDockerRegistry(credentialsId: 'DockerHub', url: 'https://index.docker.io/v1/') {
          sh 'make smart-push ARCH=${ARCH}'
        }
      }
    }
    stage('Clean up') {
      steps {
        sh 'make smart-clean ARCH=${ARCH}'

        cleanWs()
      }
    }
  }
}
