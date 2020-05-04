<?php

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\CommonBundle\Storage\Processor\Processor;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class RouterController
{
    /** @var Handler */
    private $handler;
    /** @var Environment */
    private $templating;
    /** @var Processor*/
    private $processor;

    public function __construct(Handler $handler, Environment $templating, Processor $processor)
    {
        $this->handler = $handler;
        $this->templating = $templating;
        $this->processor = $processor;
    }

    public function handle(Request $request): Response
    {
        $result = $this->handler->handle($request);

        return new Response($this->templating->render($result['template'], $result['context']), 200);
    }

    public function redirect(Request $request)
    {
        $result = $this->handler->handle($request);
        $json = $this->templating->render($result['template'], $result['context']);

        $data = \json_decode($json, true);

        return new RedirectResponse($data['url'], ($data['status'] ?? 302));
    }

    public function asset(Request $request): Response
    {
        $result = $this->handler->handle($request);
        $json = $this->templating->render($result['template'], $result['context']);

        $data = \json_decode($json, true);
        return $this->processor->getResponse($request, $data['hash'], $data['config'], $data['filename']);
    }
}
