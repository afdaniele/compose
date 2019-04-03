pipeline {
  agent any
  environment {
    // Tag: latest
    BASE_IMAGE = "nimmis/apache-php7:latest"
    BUILD_IMAGE = "afdaniele/compose:latest"
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
      }
    }
    stage('Push Image') {
      steps {
        withDockerRegistry(credentialsId: 'DockerHub', url: 'https://index.docker.io/v1/') {
          sh 'docker push $BUILD_IMAGE'
        }
      }
    }
    stage('Clean up') {
      steps {
        sh 'docker rmi $BASE_IMAGE || :'
        sh 'docker rmi $BUILD_IMAGE || :'

        cleanWs()
      }
    }
  }
}
