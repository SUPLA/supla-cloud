<?php

/* AppBundle:Email:resetpwd.txt.twig */
class __TwigTemplate_e590889e4f2cce661d9cc9012e537e884764a81fa540988456aa00bd6787d53d extends Twig_Template
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
        echo $this->env->getExtension('translator')->getTranslator()->trans("SUPLA - Password reset", array(), "messages");
        // line 2
        echo $this->env->getExtension('translator')->getTranslator()->trans("Welcome", array(), "messages");
        echo "!,

";
        // line 4
        echo $this->env->getExtension('translator')->getTranslator()->trans("Please click the following link to reset your password to cloud.supla.org.", array(), "messages");
        // line 5
        echo "
";
        // line 6
        echo (isset($context["confirmationUrl"]) ? $context["confirmationUrl"] : null);
        echo "

";
        // line 8
        echo $this->env->getExtension('translator')->getTranslator()->trans("If the link is not working, please copy and paste it or enter manually in a new browser window.", array(), "messages");
        echo " 
";
        // line 9
        echo $this->env->getExtension('translator')->getTranslator()->trans("This link is only valid for an hour.", array(), "messages");
        // line 10
        echo "  
";
        // line 11
        echo $this->env->getExtension('translator')->getTranslator()->trans("NOTICE", array(), "messages");
        echo "!
";
        // line 12
        echo $this->env->getExtension('translator')->getTranslator()->trans("If you did not request this password reset, please ignore this information.", array(), "messages");
        echo " 
";
        // line 13
        echo $this->env->getExtension('translator')->getTranslator()->trans("It is possible that another User have entered your email address by mistake while resetting password to their own account.", array(), "messages");
        echo " 


";
        // line 16
        echo $this->env->getExtension('translator')->getTranslator()->trans("Yours faithfully", array(), "messages");
        echo ", 
";
        // line 17
        echo $this->env->getExtension('translator')->getTranslator()->trans("CLOUD.SUPLA.ORG Team", array(), "messages");
    }

    public function getTemplateName()
    {
        return "AppBundle:Email:resetpwd.txt.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  63 => 17,  59 => 16,  53 => 13,  49 => 12,  45 => 11,  42 => 10,  40 => 9,  36 => 8,  31 => 6,  28 => 5,  26 => 4,  21 => 2,  19 => 1,);
    }
}
