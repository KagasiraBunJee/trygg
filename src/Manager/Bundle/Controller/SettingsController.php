<?php

namespace Manager\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Manager\Bundle\Entity\Settings;
use Manager\Bundle\Form\SettingsType;

class SettingsController extends Controller
{
    /**
     * @param Request $request
     * @Route("/user/settings", name="settings_page")
     * @Template()
     */
    public function settingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository("ManagerBundle:Settings")->find(1);
        if(!$settings)
        {
            $settings = new Settings();
            $settings->setEmail("example@mail.com");
            $settings->setActivate(false);
            $settings->setDays(1);
            $settings->setNumbers(1);
            $em->persist($settings);
            $em->flush();
        }
        $form = $this->createForm(new SettingsType(), $settings);
        $form->handleRequest($request);

        if($form->isValid())
        {
            $em->flush();
        }

        return [
            'settings' => $settings,
            'form' => $form->createView(),
            'bar_title' => "Settings",
            "hide_add_btn" => true
        ];
    }
}
