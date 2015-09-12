<?php

/* AppBundle:Email:activation.txt.twig */
class __TwigTemplate_50e73fd6abd7f8e8a99a117d6366dae94a5d3427f136bad65cec9dc552bad2ac extends Twig_Template
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
        echo "SUPLA - New account activated
Email: ";
        // line 2
        echo $this->getAttribute((isset($context["user"]) ? $context["user"] : $this->getContext($context, "user")), "email", array());
    }

    public function getTemplateName()
    {
        return "AppBundle:Email:activation.txt.twig";
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
