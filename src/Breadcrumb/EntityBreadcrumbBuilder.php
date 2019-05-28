<?php

namespace Drupal\password_enhancements\Breadcrumb;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\system\PathBasedBreadcrumbBuilder;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * Breadcrumb builder for the password config entities.
 */
class EntityBreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestContext $context, AccessManagerInterface $access_manager, RequestMatcherInterface $router, InboundPathProcessorInterface $path_processor, ConfigFactoryInterface $config_factory, TitleResolverInterface $title_resolver, AccountInterface $current_user, CurrentPathStack $current_path, EntityTypeManagerInterface $entity_type_manager, PathMatcherInterface $path_matcher = NULL) {
    parent::__construct($context, $access_manager, $router, $path_processor, $config_factory, $title_resolver, $current_user, $current_path, $path_matcher);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $parameters = $route_match->getParameters()->all();
    if (array_key_exists('password_enhancements_policy', $parameters)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $original_breadcrumb = parent::build($route_match);
    $links = $original_breadcrumb->getLinks();

    // Create our own breadcrumbs, because from the original it is not possible
    // to remove links.
    $breadcrumb = new Breadcrumb();
    // Copy cache settings from the original breadcrumb.
    $breadcrumb->addCacheContexts($original_breadcrumb->getCacheContexts())
      ->addCacheTags($original_breadcrumb->getCacheTags())
      ->mergeCacheMaxAge($original_breadcrumb->getCacheMaxAge());

    // Edit form's link.
    foreach ($links as $index => $link) {
      if (in_array($link->getUrl()->getRouteName(), ['entity.password_enhancements_policy.edit_form', 'entity.password_enhancements_constraint.edit_form'])) {
        unset($links[$index]);
      }
    }

    // Add our links.
    $links[] = Link::createFromRoute($this->t('Policies'), 'entity.password_enhancements_policy.collection');

    // Load policy if we got only its ID.
    if (is_string($password_policy = $route_match->getParameter('password_enhancements_policy'))) {
      $policy_storage = $this->entityTypeManager->getStorage('password_enhancements_policy');
      /** @var \Drupal\password_enhancements\Entity\PolicyInterface $password_policy */
      $password_policy = $policy_storage->load($password_policy);
    }

    $password_constraint = $route_match->getParameter('password_enhancements_constraint');
    if (!empty($password_constraint) && $route_match->getRouteName() != 'entity.password_enhancements_constraint.collection') {
      $links[] = Link::createFromRoute($password_policy->getName(), 'entity.password_enhancements_constraint.collection', [
        'password_enhancements_policy' => $password_policy->id(),
      ]);

      if ($route_match->getRouteName() == 'entity.password_enhancements_constraint.delete_form') {
        $links[] = Link::createFromRoute($password_constraint->getType(), 'entity.password_enhancements_constraint.edit_form', [
          'password_enhancements_policy' => $password_policy->id(),
          'password_enhancements_constraint' => $password_constraint->id(),
        ]);
      }
    }
    elseif ($route_match->getRouteName() == 'entity.password_enhancements_policy.delete_form') {
      $links[] = Link::createFromRoute($password_policy->getName(), 'entity.password_enhancements_policy.edit_form', [
        'password_enhancements_policy' => $password_policy->id(),
      ]);
    }

    $breadcrumb->setLinks($links);

    return $breadcrumb;
  }

}
