<?php

/* AppBundle:Auth:login.html copy.twig */
class __TwigTemplate_743f32721a154fd9de59429dbe0d202f9329688b1e001724bb6d11fcc3d727fa extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:Auth:login.html copy.twig", 1);
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
\t";
        // line 5
        if ((isset($context["error"]) ? $context["error"] : null)) {
            // line 6
            echo "        <div class=\"error\">";
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute((isset($context["error"]) ? $context["error"] : null), "message", array()), array(), "messages"), "html", null, true);
            echo "</div>
    ";
        }
        // line 8
        echo "\t
\t<div class=\"logon-screen\">
\t
\t\t <form action=\"";
        // line 11
        echo $this->env->getExtension('routing')->getPath("_auth_login_check");
        echo "\" method=\"post\" id=\"login\">
\t\t <a href=\"http://supla.org\" target=\"_blank\"><img src=";
        // line 12
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/img/logo.svg"), "html", null, true);
        echo "></img></a>
            <input type=\"text\" id=\"username\" name=\"_username\" value=\"";
        // line 13
        echo twig_escape_filter($this->env, (isset($context["last_username"]) ? $context["last_username"] : null), "html", null, true);
        echo "\" placeholder=\"E-mail\"/><br />
            <input type=\"password\" id=\"password\" name=\"_password\" placeholder=\"Password\"/><br />
\t\t\t<button type=\"submit\" class=\"btn btn-default\">";
        // line 15
        echo $this->env->getExtension('translator')->getTranslator()->trans("Sign In", array(), "messages");
        echo "</button>
\t\t</form>
\t</div>

";
    }

    public function getTemplateName()
    {
        return "AppBundle:Auth:login.html copy.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  60 => 15,  55 => 13,  51 => 12,  47 => 11,  42 => 8,  36 => 6,  34 => 5,  31 => 4,  28 => 3,  11 => 1,);
    }
}
