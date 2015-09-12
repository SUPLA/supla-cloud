<?php

/* AppBundle:Help:devlisthelp.html.twig */
class __TwigTemplate_3586fadfbf37caec9aa3dc1c2b6a2ee38c96d1335c8699d9a9619afe8a2b2085 extends Twig_Template
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
        echo "<div class=\"cd-panel from-left\">
\t\t<div class=\"cd-panel-container\">
\t\t\t<div class=\"cd-panel-content\">
\t\t\t\t<h1>I/O Devices</h1>
\t\t\t\t<p>Devices connected to SUPLA can do for You many things, for eg. turn lights on/off, open doors, gates, garage doors, controll blinds, show temperature at Your home, but also offers many sensors. As a device You can use Raspberry Pi or Arduino.</p>

\t\t\t\t<p>Setting up devices is simple, all You need to do is follow these <a href=\"";
        // line 7
        echo $this->env->getExtension('routing')->getPath("_homepage");
        echo "\">steps</a></p>

\t\t\t\t<p>You can have connected as many devices as You need, use them in many <a href=\"";
        // line 9
        echo $this->env->getExtension('routing')->getPath("_loc_list");
        echo "\">Locations</a> and connect with them through many <a href=\"";
        echo $this->env->getExtension('routing')->getPath("_aid_list");
        echo "\">Access Identifiers</a></p>
\t\t\t</div> <!-- cd-panel-content -->
\t\t</div> <!-- cd-panel-container -->
\t</div> <!-- cd-panel -->";
    }

    public function getTemplateName()
    {
        return "AppBundle:Help:devlisthelp.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  32 => 9,  27 => 7,  19 => 1,);
    }
}
