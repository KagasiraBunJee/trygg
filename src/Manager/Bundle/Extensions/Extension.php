<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 25.03.2015
 * Time: 21:02
 */

namespace Manager\Bundle\Extensions;

use Doctrine\ORM\EntityManager;

class Extension extends \Twig_Extension{

    /**
     * Constructor
     * @param EntityManager $em
     */

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction("steps", array($this, 'getSteps')),
            new \Twig_SimpleFunction("managers", array($this, 'getManagers'))
        );
    }


    public function getSteps($reverse = false)
    {
        $steps = $this->em->getRepository("ManagerBundle:Step")->findAll();
        if($reverse)
        {
            $steps = array_reverse($steps);
        }
        return $steps;
    }

    public function getManagers($reverse = false)
    {
        $steps = $this->em->getRepository("ManagerBundle:User")->findAll();
        if($reverse)
        {
            $steps = array_reverse($steps);
        }
        return $steps;
    }

    public function getName()
    {
        return "extension";
    }

}