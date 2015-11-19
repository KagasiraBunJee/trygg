<?php
/**
 * Created by PhpStorm.
 * User: Historic
 * Date: 07.11.2015
 * Time: 16:46
 */

namespace Manager\Bundle\Extensions;

use Symfony\Component\Form\FormFactory;
use Manager\Bundle\Form\NoteType;

class FormExtension extends \Twig_Extension
{
    /**
     * Constructor
     * @param FormFactory $forms
     */

    public function __construct($forms)
    {
        $this->forms = $forms;
    }

    public function addNoteForm()
    {
        $form = $this->forms->createBuilder(new NoteType());
        $form->add('submit', 'submit');

        return $form;
    }

    public function getName()
    {
        return "form_extension";
    }
}