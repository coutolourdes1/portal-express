docker login registry.gitlab.com -u gitlab-ci-token -p $CI_JOB_TOKEN

VERSION_APP=${CI_COMMIT_REF_NAME}
VERSION_IMAGE_TAG="${CI_REGISTRY_IMAGE}:${VERSION_APP}"
VERSION_IMAGE_LATEST="${CI_REGISTRY_IMAGE}:latest"

if [ "${CI_COMMIT_REF_NAME}" = "sandbox" ]; then
    VERSION_APP=dev-${CI_COMMIT_REF_NAME}
fi

if [ "${CI_COMMIT_REF_NAME}" = "master" ]; then
    VERSION_APP="*"
fi

echo "========>> Build image <<=================="
docker build -t "${VERSION_IMAGE_TAG}" \
--build-arg GITLAB_TOKEN=${PERSONAL_ACCESS_TOKEN} \
--build-arg GITLAB_USER=${GITLAB_USER} \
--build-arg GITLAB_DOMAIN=${GITLAB_DOMAIN} \
--build-arg APP_PACKAGIST=${APP_PACKAGIST}:${VERSION_APP} \
--build-arg API_V4_URL=${CI_API_V4_URL} \
--build-arg GITLAB_ISA_USER=${GITLAB_ISA_USER} \
--build-arg GITLAB_ISA_TOKEN=${GITLAB_ISA_TOKEN} \
--build-arg GROUP_ID=${GROUP_ID}  .

docker tag "${VERSION_IMAGE_TAG}" "${VERSION_IMAGE_LATEST}"
echo "========>> Push image <<=================="
docker push "${VERSION_IMAGE_TAG}"
docker push "${VERSION_IMAGE_LATEST}"
