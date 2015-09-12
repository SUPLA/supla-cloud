<?php

/* AppBundle:Default:index.html-old.twig */
class __TwigTemplate_2460268e8327fbc209ab83727beecb63c345088b9e7ae0c30038460cc25fd57f extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:Default:index.html-old.twig", 1);
        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "AppBundle::layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
        // line 4
        echo "
<h1 class=\"title\">";
        // line 5
        echo $this->env->getExtension('translator')->getTranslator()->trans("Home", array(), "messages");
        echo "</h1>


<div class=\"hidder\">
";
        // line 9
        if (((isset($context["show_base_settings"]) ? $context["show_base_settings"] : null) == true)) {
            // line 10
            echo "<h2>";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Base settings for your equipment", array(), "messages");
            echo "</h2>
<h4>";
            // line 11
            echo $this->env->getExtension('translator')->getTranslator()->trans("Server address", array(), "messages");
            echo ": ";
            echo twig_escape_filter($this->env, (isset($context["base_server"]) ? $context["base_server"] : null), "html", null, true);
            echo "</h4>
<h3>";
            // line 12
            echo $this->env->getExtension('translator')->getTranslator()->trans("Client devices (e.g. smartphones)", array(), "messages");
            echo "</h3>
<h4>";
            // line 13
            echo $this->env->getExtension('translator')->getTranslator()->trans("Access identifier", array(), "messages");
            echo ": ";
            echo twig_escape_filter($this->env, (isset($context["base_accessid"]) ? $context["base_accessid"] : null), "html", null, true);
            echo "</h4>
<h4>";
            // line 14
            echo $this->env->getExtension('translator')->getTranslator()->trans("Password", array(), "messages");
            echo ": ";
            echo twig_escape_filter($this->env, (isset($context["base_accesspwd"]) ? $context["base_accesspwd"] : null), "html", null, true);
            echo "</h4>
<h3>";
            // line 15
            echo $this->env->getExtension('translator')->getTranslator()->trans("I/O devices (e.g. raspberry)", array(), "messages");
            echo "</h3>
<h4>";
            // line 16
            echo $this->env->getExtension('translator')->getTranslator()->trans("Location identifier", array(), "messages");
            echo ": ";
            echo twig_escape_filter($this->env, (isset($context["base_locid"]) ? $context["base_locid"] : null), "html", null, true);
            echo "</h4>
<h4>";
            // line 17
            echo $this->env->getExtension('translator')->getTranslator()->trans("Password", array(), "messages");
            echo ": ";
            echo twig_escape_filter($this->env, (isset($context["base_locpwd"]) ? $context["base_locpwd"] : null), "html", null, true);
            echo "</h4>
";
        }
        // line 18
        echo " 
</div>
 
";
    }

    public function getTemplateName()
    {
        return "AppBundle:Default:index.html-old.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  87 => 18,  80 => 17,  74 => 16,  70 => 15,  64 => 14,  58 => 13,  54 => 12,  48 => 11,  43 => 10,  41 => 9,  34 => 5,  31 => 4,  28 => 3,  11 => 1,);
    }
}
