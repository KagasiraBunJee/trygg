<?php

namespace Manager\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Manager\Bundle\Entity\Company;
use Manager\Bundle\Form\CompanyType;
use Manager\Bundle\Entity\Step;
use Manager\Bundle\Entity\Log;

class CompanyController extends Controller
{
    /**
     * @Route("/", defaults={"step" = 1, "id" = 0}, name="home")
     * @Route("/home/{step}" , name="main", defaults={"step" = 1, "id" = 0})
     * @Route("/home/{step}/{id}" , name="main_with_item", defaults={"step" = 1, "id" = 0})
     * @Template()
     */
    public function listCategorizedAction($step, $id,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $step = $em->getRepository("ManagerBundle:Step")->find($step);
        $searchText = $request->get("search") ? $request->get("search") : "";
        $companies = $em->getRepository("ManagerBundle:Company")->getCompanies($step,$searchText);
        $company = new Company();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $companies,
            $request->query->get('page', 1)/*page number*/,
            $this->container->getParameter("items_per_page")/*limit per page*/
        );

        if($id)
        {
            $company = $em->getRepository("ManagerBundle:Company")->find($id);
        }

        return [
            'step' => $step,
            'company' => $company,
            'companies' => $pagination
        ];
    }

    /**
     * @return array
     * @Route("/all", name="all_customers")
     * @Template("ManagerBundle:Company:list.html.twig")
     */
    public function allCompaniesAction(Request $request)
    {
        $searchText = $request->get("search") ? $request->get("search") : "";
        $companies = $this->getDoctrine()->getRepository("ManagerBundle:Company")->getAllCompaniesQuery($searchText);
        $step = new Step();
        $step->setName('All Customers');

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $companies,
            $request->query->get('page', 1)/*page number*/,
            $this->container->getParameter("items_per_page")/*limit per page*/
        );

        return [
            'companies' => $pagination,
            'step' => $step
        ];
    }

    /**
     * @Route("/add", name="add_sale")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $step = $em->getRepository("ManagerBundle:Step")->find(1);
        $company = new Company();
        $user = $this->getUser();
        $form = $this->createForm(new CompanyType(), $company);
        $form->handleRequest($request);
        $result = "nothing";
        if($form->isValid())
        {
            $company->setCreator($user);
            $company->setStep($step);
            $company->setRejected(false);
            $company->setTrashed(false);
            $user->addCompany($company);
            $step->addCompany($company);
            $log = new Log();
            $log->setUser($user);
            $log->setTitle("New");
            $log->setMessage("User:".$user->getName()." has added new sale[".$company->getName()."]");
            $log->setCreated(new \DateTime("now"));
            $log->setCompany($company);
            $em->persist($company);
            $em->persist($log);
            $company->addLog($log);
            $em->flush();
            $result = "success";
            return $this->redirectToRoute('home');
        }
        else
        {

            if($request->isMethod("POST"))
            {
                $result = "invalid";
            }
        }



        return [
            'bar_title' => "Add new company",
            'form' => $form->createView(),
            'action' => "Create",
            "hide_add_btn" => true,
            "result" => $result
        ];
    }

    /**
     * @Route("/edit/{id}", name="edit_company")
     * @Template("ManagerBundle:Company:add.html.twig")
     * @ParamConverter("company", class="ManagerBundle:Company", options={"id" = "id"})
     */
    public function editAction(Company $company, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $form = $this->createForm(new CompanyType(), $company);
        $step = $company->getStep();
        $image = $company->getImage();
        $form->handleRequest($request);

        $result = "nothing";

        if($form->isValid())
        {
            $company->setCreator($user);
            $company->setStep($step);
            $company->setRejected(false);
            $user->addCompany($company);
            $step->addCompany($company);
            $log = new Log();
            $log->setUser($user);
            $log->setTitle("Update");
            $log->setMessage("User:".$user->getName()." has added new sale[".$company->getName()."]");
            $log->setCreated(new \DateTime("now"));
            $log->setCompany($company);
            $result = "success";
            /*if($form->getData()->getImage() != null)
            {
                if($image)
                {
                    $company->setImage($image);
                }
            }*/
            $company->addLog($log);
            $em->persist($log);
            $em->flush();

            //return $this->redirectToRoute('edit_sale');
        }
        else
        {
            if($request->isMethod("POST"))
            {
                $result = "invalid";
            }
        }

        return [
            'bar_title' => "Edit <strong>".$company->getName()."</strong>",
            'form' => $form->createView(),
            'action' => "Save",
            "hide_add_btn" => true,
            "result" => $result
        ];
    }

    /**
     * @Route("/list/rejected", name="rejected", defaults={"id" = 0})
     * @Route("/list/rejected/{id}", name="rejectedCom", defaults={"id" = 0})
     * @Template("ManagerBundle:Company:listRejected.html.twig")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function rejectedListAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $step = new Step();
        $step->setName('Rejected');
        $step->setStepLvl("rejected");
        $company = new Company();
        $searchText = $request->get("search") ? $request->get("search") : "";
        $companies = $em->getRepository("ManagerBundle:Company")->getRejectedListQuery($searchText);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $companies,
            $request->query->get('page', 1)/*page number*/,
            $this->container->getParameter("items_per_page")/*limit per page*/
        );

        if($companies)
        {
            //$company = $companies[0];
            if($id)
            {
                $company = $em->getRepository("ManagerBundle:Company")->find($id);
            }
        }

        return [
            'companies' => $pagination,
            'step' => $step,
            'company' => $company
        ];
    }

    /**
     * @Route("/reject/{id}", name="reject")
     */
    public function rejectCompanyAction(Company $company)
    {
        $em = $this->getDoctrine()->getManager();
        $company->setRejected(true);
        $lastStep = $company->getStep();
        $step = $em->getRepository("ManagerBundle:Step")->find(1);
        $company->setStep($step);
        $log = new Log();
        $log->setCreated(new \DateTime("now"));
        $log->setMessage("Rejected ".$company->getName());
        $log->setUser($this->getUser());
        $log->setTitle("Update");
        $log->setCompany($company);
        $em->persist($log);
        $company->addLog($log);
        $em->flush();
        return $this->redirectToRoute("main_with_item",["step"=>$lastStep->getId(), "id" => $company->getId()]);
    }

    /**
     * @Route("/ajax/company/{id}", name="ajax_company")\
     * @Template("ManagerBundle:Company:company.html.twig")
     */
    public function ajaxConpanyAction(Company $company, Request $request)
    {
        $main_page = $request->query->get('main_page') ? true : false;
        return [
            'company' => $company,
            'main_page' => $main_page
        ];
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteAction(Company $company)
    {
        $em = $this->getDoctrine()->getManager();
        $company->setTrashed(true);
        $lastStep = $company->getStep();
        $log = new Log();
        $log->setCreated(new \DateTime("now"));
        $log->setMessage("Deleted ".$company->getName());
        $log->setUser($this->getUser());
        $log->setTitle("Deleting company");
        $log->setCompany($company);
        $em->persist($log);
        $company->addLog($log);
        $em->flush();
        return $this->redirectToRoute("main_with_item",["step"=>$lastStep->getId()]);
    }

    /**
     * @Route("/company/setType/{stepId}/{id}", name="set_step")
     * @ParamConverter("step", class="ManagerBundle:Step", options={"id" = "stepId"})
     * @ParamConverter("company", class="ManagerBundle:Company", options={"id" = "id"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function setStep(Step $step, Company $company, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $lastStep = $company->getStep();
        $company->setStep($step);
        $step->addCompany($company);
        $log = new Log();
        $log->setCreated(new \DateTime("now"));
        $company->setRejected(false);
        $company->setTrashed(false);
        $log->setMessage("Updated status of ".$company->getName()." to status ".$step->getName());
        $log->setUser($this->getUser());
        $log->setCompany($company);
        $log->setTitle("Update");
        $em->persist($log);
        $company->addLog($log);
        $em->flush();
        $main_page = $request->query->get('main_page') ? true : false;
        if($main_page)
        {
            return $this->redirectToRoute("all_customers");
        }
        return $this->redirectToRoute("main_with_item",["step"=>$lastStep->getId(), "id" => $company->getId()]);
    }

    /**
     * @Route("/company/search", name="smart_search")
     */
    public function smartSearchAction(Request $request)
    {
        $searchTxt = $request->query->get("smartSearch") ? $request->query->get("smartSearch") : false;
        $searchWithStep = $request->query->get("stepId") ? $request->query->get("stepId") : false;
        $rejected = $request->query->get("rejected");
        $trashed = $request->query->get("trashed");
        if($searchTxt)
        {
            $em = $this->getDoctrine()->getManager();
            if(!$rejected and !$trashed and $searchWithStep)
            {

                $step = $em->getRepository("ManagerBundle:Step")->find($searchWithStep);
                $companies = $em->getRepository("ManagerBundle:Company")->getCompanies($step,$searchTxt);
            }
            elseif($rejected)
            {
                $companies = $em->getRepository("ManagerBundle:Company")->getRejectedListQuery($searchTxt);
            }
            /*elseif($trashed)
            {
            }*/
            else
            {
                $companies = $this->getDoctrine()->getRepository("ManagerBundle:Company")->getAllCompaniesQuery($searchTxt);
            }

            $result = [];

            $companies = $companies->getResult();

            foreach($companies as $company)
            {
                $url = $rejected ? $this->generateUrl("rejectedCom", ["id"=>$company->getId()]) : $this->generateUrl("main_with_item", ["id"=>$company->getId(), "step"=> $company->getStep()->getId()]);
                $result[] = [
                    "id" => $company->getId(),
                    "name" => $company->getName(),
                    "url" => $url
                ];
            }

            return new JsonResponse([
                "result" => $result
            ]);
        }
        return new JsonResponse([
            "result" => "nothing"
        ]);
    }

    /**
     * @Route("/company/reports", name="reports")
     * @Route("/company/reports/{id}", name="reported_company")
     * @Template("ManagerBundle:Company:listReported.html.twig")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function reportsListAction($id = 0,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $step = new Step();
        $step->setName('Reported companies');
        $step->setStepLvl("reported");
        $searchText = $request->get("search") ? $request->get("search") : "";
        $companies = $em->getRepository("ManagerBundle:Company")->getReportedCompanies($searchText);
        $company = new Company();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $companies,
            $request->query->get('page', 1)/*page number*/,
            $this->container->getParameter("items_per_page")/*limit per page*/
        );

        if($id)
        {
            $company = $em->getRepository("ManagerBundle:Company")->find($id);
        }

        return [
            'step' => $step,
            'company' => $company,
            'companies' => $pagination
        ];
    }

}
