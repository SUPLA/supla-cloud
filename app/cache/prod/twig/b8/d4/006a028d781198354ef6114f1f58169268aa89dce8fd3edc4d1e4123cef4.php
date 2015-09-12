<?php

/* AppBundle:Dialogs:flashmsgs.html.twig */
class __TwigTemplate_b8d4006a028d781198354ef6114f1f58169268aa89dce8fd3edc4d1e4123cef4 extends Twig_Template
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
    <script>
       (function(\$) {
          \$(document).ready(function() {
              ";
        // line 5
        $this->loadTemplate("AppBundle:Dialogs:flashmsg.html.twig", "AppBundle:Dialogs:flashmsgs.html.twig", 5)->display(array_merge($context, array("msgtype" => "success")));
        // line 6
        echo "              ";
        $this->loadTemplate("AppBundle:Dialogs:flashmsg.html.twig", "AppBundle:Dialogs:flashmsgs.html.twig", 6)->display(array_merge($context, array("msgtype" => "info")));
        // line 7
        echo "              ";
        $this->loadTemplate("AppBundle:Dialogs:flashmsg.html.twig", "AppBundle:Dialogs:flashmsgs.html.twig", 7)->display(array_merge($context, array("msgtype" => "error")));
        // line 8
        echo "              ";
        $this->loadTemplate("AppBundle:Dialogs:flashmsg.html.twig", "AppBundle:Dialogs:flashmsgs.html.twig", 8)->display(array_merge($context, array("msgtype" => "warning")));
        // line 9
        echo "          });
       })(jQuery);
    </script>
";
    }

    public function getTemplateName()
    {
        return "AppBundle:Dialogs:flashmsgs.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  36 => 9,  33 => 8,  30 => 7,  27 => 6,  25 => 5,  19 => 1,);
    }
}
