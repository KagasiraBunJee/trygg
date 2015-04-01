<?php

namespace Manager\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

use Manager\Bundle\Entity\User;
use Manager\Bundle\Form\UserType;

/**
 * Class UserController
 * @package Manager\Bundle\Controller
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return array(
            // last username entered by the user
            'last_username' => $lastUsername,
            'error'         => $error,
        );
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function loginOutAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }
    
    /**
     * @param Request $request
     * @Route("/register/manager", name="reg_manager")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Template()
     */
    public function registerManagerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        if($form->isValid())
        {
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));
            $user->setRole('ROLE_ADMIN');
            $em->persist($user);
            $em->flush();
        }

        return [
            'form' => $form->createView(),
            'bar_title' => "Add new manager"
        ];
    }
    
    /**
     * @param Request $request
     * @Route("/register/admin", name="reg_admin")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        
        if($form->isValid())
        {
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));
            $user->setRole('ROLE_SUPER_ADMIN');
            $em->persist($user);
            $em->flush();
        }
        
        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/", name="personal")
     * @Template("ManagerBundle:User:manager_list.html.twig")
     */
    public function getManagersAction(Request $request)
    {
        $searchText = $request->get("search") ? $request->get("search") : "";
        $users = $this->getDoctrine()->getRepository("ManagerBundle:User")->getManagers($searchText);

        return [
            'users' => $users
        ];
    }
}
