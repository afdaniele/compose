# Install using Docker (recommended)

**\\compose\\** comes as pre-built Docker images that can be downloaded from
DockerHub at
<a href="https://hub.docker.com/r/afdaniele/compose" target="\_blank">
https://hub.docker.com/r/afdaniele/compose
</a>.
**\\compose\\** is maintained for both x86(\_64), and arm32 architecture.

Visit the page
<a href="https://hub.docker.com/r/afdaniele/compose-arm32v7" target="\_blank">
https://hub.docker.com/r/afdaniele/compose-arm32v7
</a>
for Docker images for arm32 architecture.

NOTE: For the sake of clarity, we assume that we are working
on an **x86(\_64)** machine, thus we will refer to the Docker image
`afdaniele/compose:latest`. If you are using a machine with **arm** architecture
(e.g., Raspberry Pi), you need to replace it with `afdaniele/compose-arm32v7:latest`.

Remember to check out the list of
[Important directories and terminology](index#important-directories-and-terminology)
if you get confused while reading this documentation.


## Table of Contents

[toc]


## Step 1: Prerequisites

Make sure you have Docker installed and configured before starting this session.
If you have not installed Docker yet, you can do so by following the instructions
at this page:
<a href="https://docs.docker.com/install/" target="\_blank">
https://docs.docker.com/install/
</a>.

In this page, we will use the word `SERVER_HOSTNAME` to indicate the public
hostname of your server. If you don't know what a public hostname is, then it is
likely that your server does not have one. In that case you can just
replace it with the IP address of your machine or simply `localhost`.


## Step 2: Build Docker image

**This step is optional.** If you are not planning on modifying **\\compose\\**
but you are interested in developing your own application using **\\compose\\**,
you can skip this step
and simply pull the pre-built Docker image from DockerHub as explained in the
next step. If you don't know which one is the right choice for you, it is very
likely that you can skip this step.

If you want to build the Docker image yourself, you need to clone the repository
locally. You can do so by running the commands,

```plain
cd ~
git clone https://github.com/afdaniele/compose
cd ~/compose/
```

This will pull the latest version of **\\compose\\** in a directory called
`compose` in your home directory.

Move to the directory inside the repository containing the Dockerfiles,

```plain
cd ./docker/master/
```

NOTE: If you are using a machine with Arm architecture (e.g., Raspberry Pi),
you need to navigate to the directory `./docker/arm32v7/` instead.

You can now build the Docker image by running the following command.

```plain
docker build -t afdaniele/compose:latest ./
```

This will build the Docker image. After this command has finished,
you can run the following command,

```plain
docker images
```

This will show you the list of Docker images available in your local
machine. You should be able to see a line like the following.

<pre>
afdaniele/compose         latest         f2238f9859bc         1 minute ago         589MB
</pre>


## Step 3: Pull Docker image

If you have not built the Docker image by following the instructions in the
previous step ([Step 2: Build Docker image](#step-2-build-docker-image)),
you can pull it directly from DockerHub. To do so, run the following
command,

```plain
docker pull afdaniele/compose:latest
```


## Step 4: Run \\compose\\

Now that you have the Docker image for **\\compose\\**, you can run it
by using the following command,

```plain
docker run -itd -p 80:80 afdaniele/compose:latest
```

Wait up to 10 seconds, then visit the page [http://SERVER_HOSTNAME/](#)
in your browser. You should be able to see a **Setup** page like the following.

<p style="text-align: center;">
  <img src="images/setup/first_setup.png" width="80%" height="100%" style="border: 1px solid black">
</p>

You can now move to the step [First Setup](first-setup).

If you experience problems reaching your installation of **\\compose\\**, please check again
all the steps above. If you still have problems, take a look at the
[Troubleshooting](troubleshooting) section of our documentation. If the problem persists,
do not hesitate to open a new *Issue* on our [GitHub page](https://github.com/afdaniele/compose).


## Develop with Docker

When you use **\\compose\\** with Docker, the source code for **\\compose\\**
is embedded in the Docker image. This means that if you want to contribute
to the **\\compose\\** project or develop your new packages and pages, you will
need to do so inside the Docker container. This is not ideal.

It is possible to run **\\compose\\** while maintaining the source code outside
the container. This allows us to leverage the power of Docker in giving us a
nice a ready-to-use environment, while maintaining the option to work on the
code using our preferred IDE or text-editor (outside Docker).

To do so, we need to obtain a local copy of the **\\compose\\**
source code and then tell Docker to use this copy instead of the one inside
the container.

Let's start by obtaining the source code by running the following commands.

```plain
cd ~
git clone https://github.com/afdaniele/compose
```

This will pull the latest version of **\\compose\\** in a directory called
`compose` in your home directory. Let this be `/home/user/compose/`, for example.
We can now run **\\compose\\** in Docker by running the command,

```plain
docker run -itd -p 80:80 -v /home/user/compose/:/var/www/html/ afdaniele/compose:latest
```

This is similar to the command used in [Step 4](#step-4-run-compose). The argument
`-v /home/user/compose/:/var/www/html/` tells Docker to *mount* the directory
`/home/user/compose/` from your host machine to the path `/var/www/html/` inside
the container, which is the path where **\\compose\\** is installed by default.
