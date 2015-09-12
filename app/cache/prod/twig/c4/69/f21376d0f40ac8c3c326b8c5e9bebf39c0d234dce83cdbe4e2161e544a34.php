<?php

/* AppBundle:Dialogs:delete.html.twig */
class __TwigTemplate_c469f21376d0f40ac8c3c326b8c5e9bebf39c0d234dce83cdbe4e2161e544a34 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "
";
        // line 2
        $this->loadTemplate("AppBundle:Dialogs:confirm.html.twig", "AppBundle:Dialogs:delete.html.twig", 2)->display(array_merge($context, array("cancel" => $this->env->getExtension('translator')->trans("Cancel"), "confirm" => $this->env->getExtension('translator')->trans("Delete"), "confirm_btn_type" => "danger", "message" => $this->env->getExtension('translator')->trans("Are you sure you want to delete?"))));
    }

    public function getTemplateName()
    {
        return "AppBundle:Dialogs:delete.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  22 => 2,  19 => 1,);
    }
}
