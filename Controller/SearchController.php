<?php

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\ClientHelperBundle\Helper\Search\Manager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    /** @var Manager */
    private $manager;
    /** @var Handler */
    private $handler;
    /** @var \Twig_Environment */
    private $templating;
    /** @var array */
    private $locales;
    /** @var CacheHelper */
    private $cacheHelper;

    public function __construct(Manager $manager, Handler $handler, \Twig_Environment $templating, CacheHelper $cacheHelper, array $locales)
    {
        $this->manager = $manager;
        $this->handler = $handler;
        $this->templating = $templating;
        $this->locales = $locales;
        $this->cacheHelper = $cacheHelper;
    }

    public function handle(Request $request): Response
    {
        $result = $this->handler->handle($request);
        $search = $this->manager->search($request);

        $context = array_merge($result['context'], $search);

        $response = new Response($this->templating->render($result['template'], $context), 200);
        $this->cacheHelper->makeResponseCacheable($request, $response);
        return $response;
    }

    /**
     * @deprecated
     */
    public function results(Request $request, string $template): Response
    {
        @trigger_error('Deprecated use routing content type and use controller emsch.controller.search::handle', E_USER_DEPRECATED);

        $search = $this->manager->search($request);

        $routes = [];
        foreach ($this->locales as $locale) {
            $routes[$locale] = $this->generateUrl('emsch_search', array_merge(['_locale' => $locale], $request->query->all()));
        }

        return $this->render($template, array_merge([
            'trans_default_domain' => $this->manager->getClientRequest()->getCacheKey(),
            'language_routes' => $routes,
        ], $search));
    }
}
