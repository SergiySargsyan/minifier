<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Link;
use AppBundle\Entity\Visit;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VisitController extends Controller
{
    /**
     * @param Request $request
     * @param $short_link
     * @Route("/link/{short_link}", name="link_way")
     *
     * @return array|Response
     */
    public function goToLinkAction(Request $request, $short_link = null)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Link $link */
        $link = $em->getRepository('AppBundle:Link')->findOneBy(['shortLink'=>$short_link]);

        if ($link && $link->getIsActive() && (!$link->getLifeTime() || $link->getLifeTime()>(new \DateTime('now')))){
            $visit = new Visit();
            $visit->setDate(new \DateTime())
                ->setBrowser($this->getBrowser())
                ->setIpAddress($request->getClientIp())
                ->setLink($link);
            $em->persist($visit);
            $em->flush();
            return $this->redirect($link->getOriginalLink());
        }



        return $this->render('@App/page_404.html.twig');
    }


    // скопировал з http://qaru.site/questions/1039570/php-get-the-browser-name
    private function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif(preg_match('/Firefox/i',$u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif(preg_match('/Chrome/i',$u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif(preg_match('/Safari/i',$u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }
        elseif(preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
        }
        elseif(preg_match('/Netscape/i',$u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        return $bname. ' '. $version;
    }

}
