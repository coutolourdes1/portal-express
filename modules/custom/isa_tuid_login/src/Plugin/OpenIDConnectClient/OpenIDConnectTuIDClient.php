<?php

namespace Drupal\isa_tuid_login\Plugin\OpenIDConnectClient;

use Drupal;
use Exception;
use Drupal\Core\Url;
use Drupal\openid_connect\OpenIDConnectStateToken;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * TuID OpenID Connect client.
 *
 * Implements OpenID Connect Client plugin for TuID.
 *
 * @OpenIDConnectClient(
 *   id = "tuid",
 *   label = @Translation("TuID")
 * )
 */
class OpenIDConnectTuIDClient extends OpenIDConnectClientBase {

  /**
   * La variable $time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RequestStack $request_stack,
    ClientInterface $http_client,
    LoggerChannelFactoryInterface $logger_factory,
    TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $request_stack, $http_client, $logger_factory);
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('http_client'),
      $container->get('logger.factory'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['production_base_url'] = [
      '#title' => $this->t('Production Environment Base url'),
      '#type' => 'url',
      '#description' => $this->t('Set Base Url of Production Environment Endpoint'),
      '#default_value' => isset($this->configuration['production_base_url']) ? $this->configuration['production_base_url'] : '',
    ];

    $form['development_base_url'] = [
      '#title' => $this->t('Development Environment Base url'),
      '#type' => 'url',
      '#description' => $this->t('Set Base Url of Development Environment Endpoint for Testing Purposes'),
      '#default_value' => isset($this->configuration['development_base_url']) ? $this->configuration['development_base_url'] : '',
    ];

    $form['envairoment'] = [
      '#type' => 'radios',
      '#title' => t('Envairoment'),
      '#default_value' => isset($this->configuration['envairoment']) ? $this->configuration['envairoment'] : 'development',
      '#options' => [
        'producction'=>t('Producction'),
        'development'=>t('Development')
      ]
    ];  
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function decodeIdToken($id_token) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoints() {
    $base_url = $this->productionMode($this->configuration['envairoment']) ? $this->configuration['production_base_url'] : $this->configuration['development_base_url'];
    $base_url = trim($base_url, '/');
    $client_id = $this->configuration['client_id'];
    $redirect_uri = $this->getRedirectUrl()->toString();
    
    return [
      'authorization' => $base_url.'/trustedx-authserver/oauth/as-principal?response_type=code&client_id='.$client_id.'&state=1sqiedls&redirect_uri='.$redirect_uri.'&scope=profile attributes',
      'token' => $base_url.'/trustedx-authserver/oauth/as-principal/token',
      'userinfo' => $base_url.'/trustedx-resources/openid/v1/users/me',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function authorize($scope = 'attributes') {
    $state = OpenIDConnectStateToken::create();
    
    $url_options = [
      'query' => [
        'client_id' => $this->configuration['client_id'],
        'response_type' => 'code',
        'scope' => 'attributes',
        'prompt' => 'login',
        'redirect_uri' => $this->getRedirectUrl()->toString(),
        'state' => $state,
      ],
    ];
    
    $endpoints = $this->getEndpoints();
    // Clear _GET['destination'] because we need to override it.
    $this->requestStack->getCurrentRequest()->query->remove('destination');
    $authorization_endpoint = Url::fromUri($endpoints['authorization'], $url_options)
      ->toString(TRUE);
    $response = new TrustedRedirectResponse($authorization_endpoint->getGeneratedUrl());
    // We can't cache the response, since this will prevent the state to be
    // added to the session. The kill switch will prevent the page getting
    // cached for anonymous users when page cache is active.
    Drupal::service('page_cache_kill_switch')->trigger();

    return $response;
  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveIDToken().
   *
   * @param string $authorization_code
   *   A authorization code string.
   *
   * @return array|bool
   *   A result array or false.
   */
  public function retrieveTokens($authorization_code) {  
    // Exchange `code` for access token and ID token.
    $endpoints = $this->getEndpoints();
    $credentials = base64_encode(urlencode(utf8_encode($this->configuration['client_id'])).':'.urlencode(utf8_encode($this->configuration['client_secret'])));

    $request_options = [
      'form_params' => [
        'code' => $authorization_code,
        'redirect_uri' => $this->getRedirectUrl()->toString(),
        'grant_type' => 'authorization_code',
      ],
      'headers' => [
        'Accept' => 'application/json',
        'Content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        'Authorization' => 'Basic '. $credentials,

      ],
      'verify' => FALSE,
    ];

    /* @var \GuzzleHttp\ClientInterface $client */
    try {
       $response = Drupal::httpClient()
         ->post($endpoints['token'], $request_options);
      $response_data = json_decode((string) $response->getBody(), TRUE);

      // Expected result.
      $tokens = [
        'id_token' => isset($response_data['id_token']) ? $response_data['id_token'] : NULL,
        'access_token' => isset($response_data['access_token']) ? $response_data['access_token'] : NULL,
      ];

      if (array_key_exists('expire', $response_data)) {
        $tokens['expire'] = $this->time->getRequestTime() + $response_data['expire'];
      }
      if (array_key_exists('refresh_token', $response_data)) {
        $tokens['refresh_token'] = $response_data['refresh_token'];
      }
      return $tokens;
    }
    catch (Exception $e) {
      $variables = [
        '@message' => 'Could not retrieve tokens',
        '@error_message' => $e->getMessage(),
      ];
      $this->loggerFactory->get('openid_connect_' . $this->pluginId)
        ->error('@message. Details: @error_message', $variables);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveUserInfo($access_token) {
    $request_options = [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $access_token,
      ],
      'verify' => FALSE,
    ];
    $endpoints = $this->getEndpoints();

    try {

      $response = Drupal::httpClient()
        ->post($endpoints['userinfo'], $request_options);
      return json_decode((string) $response->getBody(), TRUE);
    }
    catch (Exception $e) {
      $variables = [
        '@message' => 'Could not retrieve user profile information',
        '@error_message' => $e->getMessage(),
      ];
      $this->loggerFactory->get('openid_connect_' . $this->pluginId)
        ->error('@message. Details: @error_message', $variables);
      return FALSE;
    }
  }

  /**
   * Return true if run in producction mode.
   *
   * @param string $envairoment
   *   String that indicate the envairoment.
   *
   * @return bool
   *   True if envairoment config settings is 'producction'.
   */
  protected function productionMode($envairoment){
    return $envairoment === 'producction';
  }

}
