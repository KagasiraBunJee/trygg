<?php

namespace Manager\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Manager\Bundle\Entity\User;
use Manager\Bundle\Entity\Company;
use Manager\Bundle\Form\CompanyType;
use Manager\Bundle\Entity\Step;
use Manager\Bundle\Entity\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        /** @var User $user **/
        $user = $this->getUser();

        //filter
        $searchText = $request->get("search", "");
        $month = $request->get("month", 0);
        $week = $request->get("week", 0);
        $managerId = $request->get("manager", null);
        $manager = null;
        if ($managerId != null || $user->isManager())
        {
            /** @var User $manager **/
            if ($user->isManager())
            {
                $manager = $em->getRepository("ManagerBundle:User")->find($user->getId());
            }
            else
            {
                $manager = $em->getRepository("ManagerBundle:User")->find($managerId);
            }
        }

        $companies = $em->getRepository("ManagerBundle:Company")->getCompanies($step, $searchText,$month, $week, $manager);

        $company = new Company();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $companies,
            $request->query->get('page', 1)/*page number*/,
            $this->container->getParameter("items_per_page")/*limit per page*/,
            array('wrap-queries'=>true)
        );

        if($id)
        {
            $company = $em->getRepository("ManagerBundle:Company")->find($id);
        }

        $managers = $em->getRepository("ManagerBundle:User")->findAll();

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
        $em = $this->getDoctrine()->getManager();
        $searchText = $request->get("search", "");
        $month = $request->get("month", 0);
        $week = $request->get("week", 0);
        $managerId = $request->get("manager", null);

        $manager = null;
        if ($managerId != null)
        {
            /** @var User $manager **/
            $manager = $em->getRepository("ManagerBundle:User")->find($managerId);
        }

        $companies = $em->getRepository("ManagerBundle:Company")->getAllCompaniesQuery($searchText, $month, $week, $manager);
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
            $company->addStep($step);
            $company->setRejected(false);
            $company->setTrashed(false);
            $user->addCompany($company);
            $step->addCompany($company);
            $log = new Log();
            $log->setUser($user);
            $log->setTitle("New");
            $log->setMessage("User: <strong>".$user->getName()."</strong> has added new sale[<strong>".$company->getName()."</strong>]");
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
        //$image = $company->getImage();
        $form->handleRequest($request);

        $result = "nothing";

        if($form->isValid())
        {
            $company->setCreator($user);
            $company->setRejected(false);
            $user->addCompany($company);
            $log = new Log();
            $log->setUser($user);
            $log->setTitle("Update");
            $log->setMessage("User: <strong>".$user->getName()."</strong> has added new sale[<strong>".$company->getName()."]</strong>");
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

        $searchText = $request->get("search", "");
        $month = $request->get("month", 0);
        $week = $request->get("week", 0);
        $managerId = $request->get("manager", null);

        $manager = null;
        if ($managerId != null)
        {
            /** @var User $manager **/
            $manager = $em->getRepository("ManagerBundle:User")->find($managerId);
        }

        $companies = $em->getRepository("ManagerBundle:Company")->getRejectedListQuery($searchText, $month, $week, $manager);

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
     * @Route("/reject/{id}/{current_step}", name="reject")
     */
    public function rejectCompanyAction(Company $company, $curstep = 0)
    {
        $em = $this->getDoctrine()->getManager();
        if($curstep > 0)
        {
            $current_step = $em->getRepository("ManagerBundle:Step")->find($curstep);
        }
        $company->setRejected(true);
        $lastStep = $company->getStep();
        $step = $em->getRepository("ManagerBundle:Step")->find(1);
        $company->setStep($step);
        $log = new Log();
        $log->setCreated(new \DateTime("now"));
        $log->setMessage("Rejected <strong>".$company->getName()."</strong>");
        $log->setUser($this->getUser());
        $log->setTitle("Update");
        $log->setCompany($company);
        $em->persist($log);
        $company->addLog($log);
        $em->flush();
        if($current_step)
        {
            return $this->redirectToRoute("main_with_item",["step"=>$current_step->getId(), "id" => $company->getId()]);
        }
        return $this->redirectToRoute("all_customers");
    }

    /**
     * @Route("/ajax/company/{id}", name="ajax_company")
     * @Template("ManagerBundle:Company:company.html.twig")
     */
    public function ajaxCompanyAction(Company $company, Request $request)
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
        $lastStep = $company->getStep()->last();
        $log = new Log();
        $log->setCreated(new \DateTime("now"));
        $log->setMessage("Deleted <strong>".$company->getName()."</strong>");
        $log->setUser($this->getUser());
        $log->setTitle("Deleting company");
        $log->setCompany($company);
        $em->persist($log);
        $company->addLog($log);
        $em->flush();
        return $this->redirectToRoute("main_with_item",["step"=>$lastStep->getId()]);
    }

    /**
     * @Route("/company/setType/{stepId}/{id}/{current_step}", name="set_step")
     * @ParamConverter("step", class="ManagerBundle:Step", options={"id" = "stepId"})
     * @ParamConverter("company", class="ManagerBundle:Company", options={"id" = "id"})
     * @ParamConverter("current_step", class="ManagerBundle:Step", options={"id" = "current_step"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function setStep(Step $step, Company $company, Request $request, Step $current_step)
    {
        $em = $this->getDoctrine()->getManager();
        $steps = $company->getStep();
        $log = new Log();
        $log->setCreated(new \DateTime("now"));
        $lastRejected = $company->getRejected();
        $lastTrashed = $company->getTrashed();
        $company->setRejected(false);
        $company->setTrashed(false);

        if($step->getId() > 1)
        {
            $mainStep = $em->getRepository("ManagerBundle:Step")->find(1);
            if($steps->contains($mainStep))
            {
                $company->removeStep($mainStep);
            }
        }
        if($steps->contains($step))
        {
            $company->removeStep($step);
            $step->removeCompany($company);
            $log->setMessage("Removed status <strong>".$step->getName()."</strong> of company <strong>".$company->getName()."</strong>");
        }
        else
        {
            $company->addStep($step);
            $step->addCompany($company);
            $log->setMessage("Updated status of <strong>".$company->getName()."</strong> to status <strong>".$step->getName()."</strong>");
        }
        $log->setUser($this->getUser());
        $log->setCompany($company);
        $log->setTitle("Update");
        $em->persist($log);
        $company->addLog($log);
        $em->flush();
        return $this->redirect($request->headers->get('referer'));
        /*$main_page = $request->query->get('main_page') ? true : false;
        if($lastRejected)
        {
            return $this->redirectToRoute("rejected");
        }
        elseif($lastTrashed)
        {
            return $this->redirectToRoute("all_customers");
        }
        return $this->redirectToRoute("main_with_item",["step" => $current_step->getId(), "id" => $company->getId()]);*/
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
                $companies = $em->getRepository("ManagerBundle:Company")->getCompanies($step,$searchTxt)->getResult();
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

//            $companies = $companies->getResult();

            /**
             * @var Company $company
             */
//            foreach($companies as $index=>$company)
//            {
//                $steps = $company->getStep();
//                /**
//                 * @var Step $cStep
//                 */
//                foreach($steps as $cStep)
//                {
//                    if($cStep->getId() > $step->getId())
//                    {
//                        unset($companies[$index]);
//                    }
//                }
//            }

            foreach($companies as $company)
            {
                $url = $rejected ? $this->generateUrl("rejectedCom", ["id"=>$company->getId()]) : $this->generateUrl("main_with_item", ["id"=>$company->getId(), "step"=> $company->getStep()->first()->getId()]);
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

        $searchText = $request->get("search", "");
        $month = $request->get("month", 0);
        $week = $request->get("week", 0);
        $managerId = $request->get("manager", null);

        $manager = null;
        if ($managerId != null)
        {
            /** @var User $manager **/
            $manager = $em->getRepository("ManagerBundle:User")->find($managerId);
        }

        $companies = $em->getRepository("ManagerBundle:Company")->getReportedCompanies($searchText, $month, $week, $manager);
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
     * @Route("/ajax/step/{stepId}/{id}", name="set_ajax_step")
     * @ParamConverter("step", class="ManagerBundle:Step", options={"id" = "stepId"})
     * @ParamConverter("company", class="ManagerBundle:Company", options={"id" = "id"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function setAjaxStepAction(Step $step, Company $company, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $steps = $company->getStep();
        $log = new Log();
        $log->setCreated(new \DateTime("now"));
        $company->setRejected(false);
        $company->setTrashed(false);
        $action = "added";
        $mainStep = $em->getRepository("ManagerBundle:Step")->find(1);
        $secondStep = $em->getRepository("ManagerBundle:Step")->find(2);

        $stepstoenter = [
            4,
            5,
            6,
            7,
            9,
            10
        ];

        if($step->getId() > 1)
        {
            if($steps->contains($mainStep))
            {
                $company->removeStep($mainStep);
            }
        }
        if($steps->contains($step))
        {
            $action = "removed";
            $company->removeStep($step);
            $step->removeCompany($company);
            $log->setMessage("Removed status <strong>".$step->getName()."</strong> of company <strong>".$company->getName()."</strong>");

            if ($step->getId() == 3)
            {
                foreach ($stepstoenter as $newStepId)
                {
                    $newStep = $em->getRepository("ManagerBundle:Step")->find($newStepId);
                    if ($newStep != null)
                    {
                        if ($steps->contains($newStep))
                        {
                            $company->removeStep($newStep);
                        }
                    }
                }
            }

            if($company->getStep()->count() < 2)
            {
                $company->addStep($mainStep);
            }
        }
        else
        {
            if($step->getId() == 3)
            {
                foreach ($stepstoenter as $newStepId)
                {
                    $newStep = $em->getRepository("ManagerBundle:Step")->find($newStepId);
                    if ($newStep != null)
                    {
                        if (!$steps->contains($newStep))
                        {
                            $company->addStep($newStep);
                        }
                    }
                }
                if ($company->getStep()->contains($secondStep))
                {
                    $company->removeStep($secondStep);
                }
            }

            $company->addStep($step);
            $step->addCompany($company);
            $log->setMessage("Updated status of <strong>".$company->getName()."</strong> to status <strong>".$step->getName()."</strong>");
        }

        $log->setUser($this->getUser());
        $log->setCompany($company);
        $log->setTitle("Update");
        $company->addLog($log);
        $em->persist($log);
        $em->flush();

        return new JsonResponse([
            'result' => $action
        ]);
    }

    /**
     * @Route("/ajax/companies/{items}", name="get_ajax_companies")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function getAjaxCompaniesByPage(Request $request, $items)
    {
        $em = $this->getDoctrine()->getManager();

        $searchText = $request->get("search", "");
        $month = $request->get("month", 0);
        $week = $request->get("week", 0);
        $managerId = $request->get("manager", null);

        $manager = null;
        if ($managerId != null)
        {
            /** @var User $manager **/
            $manager = $em->getRepository("ManagerBundle:User")->find($managerId);
        }

        $limit = $this->container->getParameter("items_per_page");

        $currentItems = $items;
        $itemsPerPage = $this->container->getParameter("items_per_page");
        $nextPage = floor($currentItems/$itemsPerPage)+1;

        if((($nextPage*$itemsPerPage)-$itemsPerPage) < $currentItems)
        {
            throw new NotFoundHttpException("Found no companies");
        }

        $companies = $em->getRepository("ManagerBundle:Company")->getAllCompaniesQuery($searchText, $month, $week, $manager);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $companies,
            $nextPage/*page number*/,
            $this->container->getParameter("items_per_page")/*limit per page*/
        );

        if(count($pagination) < 1){
            throw new NotFoundHttpException("Found no companies");
        }

        $renderedView = $this->render("@Manager/ajax/table_list.html.twig", ["companies"=>$pagination])->getContent();

        return new JsonResponse([
            "render" => $renderedView
        ]);
    }
}
