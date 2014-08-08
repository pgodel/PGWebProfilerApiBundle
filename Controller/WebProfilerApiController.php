<?php

namespace PG\Bundle\WebProfilerApiBundle\Controller;

use FOS\RestBundle\Util\Codes;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\RouteRedirectView;

use FOS\RestBundle\View\View;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebProfilerApiController extends FOSRestController
{
    private $profiler;

    public function indexAction()
    {
        return $this->render('PGWebProfilerApiBundle:Default:index.html.twig', array());
    }

    /**
     * List all tokens.
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing tokens.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many tokens to return.")
     *
     * @Annotations\View()
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getTokensAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $session = $request->getSession();

        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        $limit = $paramFetcher->get('limit');

        $this->profiler = $this->get('profiler');

        if (null === $this->profiler) {
            throw new NotFoundHttpException('The profiler must be enabled.');
        }

        $this->profiler->disable();

        //$profile = $this->profiler->loadProfile('');

        $ip     = $request->query->get('ip');
        $method = $request->query->get('method');
        $url    = $request->query->get('url');
        $start  = $request->query->get('start', null);
        $end    = $request->query->get('end', null);
        $limit  = $request->query->get('limit');


        $tokens = $this->profiler->find($ip, $url, $limit, $method, $start, $end);
        return $tokens;

    }

    /**
     * Get a token.
     *
     * @Annotations\View(templateVar="token")
     *
     * @param Request $request the request object
     * @param int     $token      the token id
     *
     * @return array
     *
     * @throws NotFoundHttpException when note not exist
     */
    public function getTokenAction(Request $request, $token)
    {

        $tokens = [$token => $token];

        if (!isset($tokens[$token])) {
            throw $this->createNotFoundException("Token does not exist.");
        }

        $view = new View($tokens[$token]);

        return $view;
    }
}
