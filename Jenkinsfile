pipeline {
  agent any
  environment {
    // Tag: latest
    BASE_IMAGE = "nimmis/apache-php7:latest"
    BUILD_IMAGE = "afdaniele/compose:latest"

    BUILD_IMAGE_ARM32V7 = "afdaniele/compose-arm32v7:latest"
  }
  stages {
    stage('Update Base Image') {
      steps {
        sh 'docker pull $BASE_IMAGE'
      }
    }
    stage('Build Image') {
      steps {
        sh 'docker build -t $BUILD_IMAGE --no-cache -f docker/master/Dockerfile ./docker/master/'

        sh 'docker build -t $BUILD_IMAGE_ARM32V7 --no-cache -f docker/arm32v7/Dockerfile ./docker/arm32v7/'
      }
    }
    stage('Push Image') {
      steps {
        withDockerRegistry(credentialsId: 'DockerHub', url: 'https://index.docker.io/v1/') {
          sh 'docker push $BUILD_IMAGE'

          sh 'docker push $BUILD_IMAGE_ARM32V7'
        }
      }
    }
    stage('Clean up') {
      steps {
        sh 'docker rmi $BASE_IMAGE'

        sh 'docker rmi $BASE_IMAGE_ARM32V7'

        cleanWs()
      }
    }
  }
}
