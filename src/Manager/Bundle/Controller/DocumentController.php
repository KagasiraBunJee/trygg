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
use Manager\Bundle\Entity\Log;
use Manager\Bundle\Entity\Note;
use Manager\Bundle\Entity\User;
use Manager\Bundle\Entity\Document;
use Manager\Bundle\Form\DocumentType;
use Symfony\Component\Form\Form;

/**
 * Class DocumentController
 * @package Manager\Bundle\Controller
 */
class DocumentController extends Controller
{
    /**
     * @Route("/company/{id}/documents/add", name="add_document")
     * @Template()
     */
    public function createAction(Company $company, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $document = new Document();
        /** @var Form $form */
        $form = $this->createForm(new DocumentType(), $document);
        if ($request->getMethod() == "POST")
        {
            $form->handleRequest($request);
            if ($form->isValid())
            {
                $response['success'] = true;
                $document->setUser($user);
                $document->setCompany($company);

                $log = new Log();
                $log->setUser($user);
                $log->setTitle("");
                $log->setMessage("User: ".$user->getName()." has added new document.");
                $log->setCompany($company);

                $company->addLog(
                    $log
                );

                $em->persist($log);
                $em->persist($document);
                $em->flush();
            }
            else
            {
                $response['success'] = false;
                $response['reason'] = "Error: Wrong image or image size";
            }
            return new JsonResponse($response);
        }
        return [
            'companyId' => $company->getId(),
            'form' => $form->createView()
        ];
    }
    /**
     * @Route("/company/{id}/documents", name="document_list")
     * @Template()
     */
    public function listAction(Company $company, Request $request)
    {
        $user = $this->getUser();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $notesQuery = $em->getRepository("ManagerBundle:Document")->getDocumentsQuery($company);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $notesQuery,
            $request->query->get('page', 1)/*page number*/,
            $this->container->getParameter("items_per_page")/*limit per page*/
        );
        return [
            'documents' => $pagination
        ];
    }
}