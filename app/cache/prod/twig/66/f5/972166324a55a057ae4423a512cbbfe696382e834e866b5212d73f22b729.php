<?php

/* AppBundle:Email:confirm.txt.twig */
class __TwigTemplate_66f5972166324a55a057ae4423a512cbbfe696382e834e866b5212d73f22b729 extends Twig_Template
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
        echo $this->env->getExtension('translator')->getTranslator()->trans("SUPLA - Account Activation", array(), "messages");
        // line 2
        echo $this->env->getExtension('translator')->getTranslator()->trans("Welcome", array(), "messages");
        echo "!,

";
        // line 4
        echo $this->env->getExtension('translator')->getTranslator()->trans("Thank you for registering an account with cloud.supla.org. Please click the following link to complete activation of your account.", array(), "messages");
        // line 5
        echo "
";
        // line 6
        echo (isset($context["confirmationUrl"]) ? $context["confirmationUrl"] : null);
        echo "

";
        // line 8
        echo $this->env->getExtension('translator')->getTranslator()->trans("If the link is not working, please copy and paste it or enter manually in a new browser window.", array(), "messages");
        // line 9
        echo "
";
        // line 10
        echo $this->env->getExtension('translator')->getTranslator()->trans("NOTICE", array(), "messages");
        echo "!
";
        // line 11
        echo $this->env->getExtension('translator')->getTranslator()->trans("If you have not used this email address with cloud.supla.org, please do not click the link.", array(), "messages");
        // line 12
        echo "

";
        // line 14
        echo $this->env->getExtension('translator')->getTranslator()->trans("Yours faithfully", array(), "messages");
        echo ",
";
        // line 15
        echo $this->env->getExtension('translator')->getTranslator()->trans("CLOUD.SUPLA.ORG Team", array(), "messages");
        // line 16
        echo "



";
    }

    public function getTemplateName()
    {
        return "AppBundle:Email:confirm.txt.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  57 => 16,  55 => 15,  51 => 14,  47 => 12,  45 => 11,  41 => 10,  38 => 9,  36 => 8,  31 => 6,  28 => 5,  26 => 4,  21 => 2,  19 => 1,);
    }
}
