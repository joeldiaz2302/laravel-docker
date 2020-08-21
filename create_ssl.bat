@ECHO off

echo load the environment variables

setlocal enableextensions enabledelayedexpansion

if EXIST ".env" (
  for /F "tokens=*" %%I in (.env) do @set %%I
)
echo DOCKER_LOCATION=%DOCKER_LOCATION%
echo PROJECT_LOCATION=%PROJECT_LOCATION%


echo create self signed certificates for use with nginx

echo docker pull paulczar/omgwtfssl
docker pull paulczar/omgwtfssl

echo docker run -v "%DOCKER_LOCATION%/certs":/certs -e SSL_SUBJECT="%PROJ_DOMAIN%" --name certmaker paulczar/omgwtfssl
docker run -v "%DOCKER_LOCATION%/certs":/certs -e SSL_SUBJECT="%PROJ_DOMAIN%" --name certmaker paulczar/omgwtfssl

echo docker container stop certmaker
docker container stop certmaker

echo docker container rm certmaker
docker container rm certmaker

echo certs created, complete