<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Link;
use AppBundle\Entity\User;
use AppBundle\Form\LinkType;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends Controller
{
    /**
     * @param Request $request
     * @Template()
     * @Route("/list", name="list")
     * @return array|Response
     */
    public function listAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user){
            return $this->redirectToRoute('login');
        }

        $em = $this->getDoctrine()->getManager();

        if ($user->hasRole('ROLE_ADMIN')){
            $query = $em->getRepository('AppBundle:Link')
                ->createQueryBuilder('link')
                ->orderBy('link.id','DESC')
                ->getQuery();

        } else {
            $query = $em->getRepository('AppBundle:Link')
                ->createQueryBuilder('link')
                ->andWhere('link.creator = :creator')
                ->orderBy('link.id','DESC')
                ->setParameter('creator',$user)
                ->getQuery();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/
        );


        $links = $pagination->getItems();

        $serializer = $this->get('jms_serializer');
        $links_serialized = $serializer->serialize($links, 'json' ,
            SerializationContext::create()->setGroups(array('link')));

        return ['pagination' => $pagination, 'links_serialized'=>$links_serialized];
    }


    /**
     * @Route("/create_link", name = "link_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user){
            throw $this->createAccessDeniedException();
        }
        $content = $request->getContent();
        $serializer = $this->get('jms_serializer');

        $data = $serializer->deserialize($content,'array','json');

        if (!$data['original_link']){
            return  new JsonResponse('not valid', 400);
        }

        $em = $this->getDoctrine()->getManager();

        if (isset($data['life_time']) && $data['life_time']){
            $lifetime = new \DateTime($data['life_time']);
        } else {
            $lifetime = null;
        }
        if ($data['id']){
            /** @var Link $link */
            $link = $em->getRepository('AppBundle:Link')->find($data['id']);
            if (!$link || !$this->canEditLink($link)){
                throw $this->createNotFoundException();
            }

            $link
                ->setOriginalLink($data['original_link'])
                ->setIsActive($data['is_active'])
                ->setLifeTime($lifetime);
        } else {
            $link = new Link();
            $link
                ->setOriginalLink($data['original_link'])
                ->setShortLink($this->generateShortLink())
                ->setLifeTime($lifetime)
                ->setIsActive($data['is_active'])
                ->setCreator($user);

            $em->persist($link);
        }

        $em->flush();

        $link_serialized = $serializer->serialize($link, 'json' ,
            SerializationContext::create()->setGroups(array('link')));

        $response = new JsonResponse($link_serialized, 200);
        return $response;
    }

    /**
     * @Route("/remove_link", name = "link_remove")
     * @param Request $request
     * @return JsonResponse
     */
    public function removeLinkAction(Request $request)
    {
        $link_id = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $link = $em->getRepository('AppBundle:Link')->find($link_id);

        if (!$link || !$this->canEditLink($link)){
            throw $this->createNotFoundException();
        }

        $em->remove($link);
        $em->flush();

        $response = new JsonResponse('ok', 200);
        return $response;
    }

    /**
     * @Route("/statistic_link", name = "link_statistic")
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatisticAction(Request $request)
    {
        $link_id = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $link = $em->getRepository('AppBundle:Link')->find($link_id);

        if (!$link || !$this->canEditLink($link)){
            throw $this->createNotFoundException();
        }

        $serializer = $this->get('jms_serializer');
        $link_serialized = $serializer->serialize($link, 'json' ,
            SerializationContext::create()->setGroups(array('link_stat')));

        $response = new JsonResponse($link_serialized, 200);
        return $response;
    }

    private function generateShortLink(){
        $length = 6;
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $chars[rand(1,$numChars)-1];
        }

        $duplicate = $this->getDoctrine()->getRepository('AppBundle:Link')->findOneBy(['shortLink'=>$string]);

        if ($duplicate){
            return $this->generateShortLink();
        }

        return $string;
    }

    /**
     * @param Link $link
     * @return bool
     */
    private function canEditLink(Link $link)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user){
            return false;
        } elseif ($user->hasRole('ROLE_ADMIN') || $link->getCreator() == $user){
            return true;
        } else {
            return false;
        }
    }
}
