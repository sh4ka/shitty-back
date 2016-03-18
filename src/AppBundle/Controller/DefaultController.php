<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/news", name="articles")
     */
    public function newsAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $newsForToday = $em->getRepository('AppBundle:News')->findForToday();
        foreach($newsForToday as $new){
            if(is_null($new->getDateShown())){
                $new->setDateShown(new \DateTime());
                $em->persist($new);
            }
        }
        $em->flush();
        $response = new Response($serializer->serialize($newsForToday, 'json'));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/news/check", name="articles_check")
     */
    public function check()
    {
        $serializer = $this->container->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $newsForToday = $em->getRepository('AppBundle:News')->findForToday();
        $data = [
            'count' => count($newsForToday)
        ];

        $response = new Response($serializer->serialize($data, 'json'));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/", name="home")
     */
    public function indexAction()
    {
        return $this->redirect('https://play.google.com/store/apps/details?id=floppolapps.shittytimes', 301);
    }
}
