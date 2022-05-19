FROM registry.gitlab.com/digitalprojex-public/dockerfiles/drupal
ARG GITLAB_TOKEN
ARG GITLAB_DOMAIN
ARG GITLAB_USER
ARG GITLAB_TOKEN
ARG GROUP_ID
ARG APP_PACKAGIST
ARG API_V4_URL
ARG GITLAB_ISA_USER
ARG GITLAB_ISA_TOKEN

RUN composer config --global gitlab-domains ${GITLAB_DOMAIN} && \
    composer config --global gitlab-token.${GITLAB_DOMAIN} ${GITLAB_TOKEN} && \
    composer create-project drupal/recommended-project:^9 ./ && \
    composer config repositories.${GITLAB_DOMAIN}/${GROUP_ID} \
     '{"type": "composer", "url": "'${API_V4_URL}'/group/'${GROUP_ID}'/-/packages/composer/packages.json"}' && \
    composer config --json extra.enable-patching true && \
    composer config minimum-stability dev && \
    composer require "${APP_PACKAGIST}" --no-interaction && \
    composer update --lock

# Aqui se obtiene la ultima version del diseño, se pudiera hacer de otra forma
RUN git clone https://${GITLAB_ISA_USER}:${GITLAB_ISA_TOKEN}@gitlab.isaltda.com.uy/FBalboa/portal-express-drupal.git \
    web/profiles/contrib/isa/themes/inten/design