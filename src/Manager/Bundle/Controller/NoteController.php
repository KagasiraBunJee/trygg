<?php

namespace Manager\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\ORM\EntityManager;
use Manager\Bundle\Entity\Company;
use Manager\Bundle\Form\CompanyType;
use Manager\Bundle\Entity\Step;
use Manager\Bundle\Entity\Log;
use Manager\Bundle\Entity\Note;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Manager\Bundle\Form\NoteType;

/**
 * Class NoteController
 * @package Manager\Bundle\Controller
 */
class NoteController extends Controller
{
    /**
     * @Route("/company/{id}/notes/add", name="note_form")
     * @Template()
     */
    public function formAction(Company $company, Request $request)
    {
        $user = $this->getUser();
        $note = new Note();
        $form = $this->createForm(new NoteType(), $note);

        if ($request->getMethod() == "POST")
        {
            $form->handleRequest($request);

            if ($form->isValid())
            {
                /**
                 * @var EntityManager $em
                 */
                $em = $this->getDoctrine()->getManager();

                $response['success'] = true;

                $note->setCreator($user);
                $note->setCompany($company);

                $em->persist($note);
                $em->flush();
            }
            else
            {
                $response['success'] = false;
                $response['reason'] = $form->getErrors();
            }

            return new JsonResponse($response);
        }

        return [
            'form' => $form->createView(),
            'companyId' => $company->getId()
        ];
    }

    /**
     * @Route("/company/{id}/notes", name="note_list")
     * @Template()
     */
    public function listAction(Company $company, Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $notesQuery = $em->getRepository("ManagerBundle:Note")->getNotesQuery($company);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $notesQuery,
            $request->query->get('page', 1)/*page number*/,
            $this->container->getParameter("items_per_page")/*limit per page*/
        );

        return [
            'notes' => $pagination
        ];
    }
}
