<?php

/* AppBundle:Auth:login-test.html.twig */
class __TwigTemplate_9170792f44bfbd7bfac871d7b216a61784fdb0a10640168beb6db51b5883ef48 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:Auth:login-test.html.twig", 1);
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
\t\t<div id=\"formContainer\">
\t\t<form action=\"";
        // line 11
        echo $this->env->getExtension('routing')->getPath("_auth_login_check");
        echo "\" method=\"post\" id=\"login\">
\t\t<a href=\"#\" id=\"flipToRecover\" class=\"flipLink\">?</a>
\t\t<input type=\"text\" id=\"username\" name=\"_username\" value=\"";
        // line 13
        echo twig_escape_filter($this->env, (isset($context["last_username"]) ? $context["last_username"] : null), "html", null, true);
        echo "\" placeholder=\"E-mail\"/><br>
\t\t<input type=\"password\" id=\"password\" name=\"_password\" placeholder=\"";
        // line 14
        echo $this->env->getExtension('translator')->getTranslator()->trans("Password", array(), "messages");
        echo "\"/>
\t\t<button type=\"submit\" class=\"btn btn-default\">
\t\t\t\t<span class=\"border-l\">
\t\t\t\t\t<span class=\"border-r\">
\t\t\t\t\t\t<span class=\"btn-bg\">";
        // line 18
        echo $this->env->getExtension('translator')->getTranslator()->trans("Sign In", array(), "messages");
        echo "</span>
\t\t\t\t\t</span>
\t\t\t\t</span>
\t\t\t</button>
\t\t</form>
\t\t<form id=\"recover\" method=\"post\" action=\"./\">
\t\t<a href=\"#\" id=\"flipToLogin\" class=\"flipLink\">Forgot?</a>
\t\t<input type=\"text\" id=\"recovery\" placeholder=\"Your E-mail?\"/>
\t\t<input type=\"submit\" name=\"submit\" value=\"Recover\" />
\t\t</form>
\t\t</div>
\t</div>
\t
\t<div id=\"particles-js\"></div>

<script src=\"";
        // line 33
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/particles.js"), "html", null, true);
        echo "\"></script>
<script src=\"";
        // line 34
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/app.js"), "html", null, true);
        echo "\"></script>


";
    }

    public function getTemplateName()
    {
        return "AppBundle:Auth:login-test.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  85 => 34,  81 => 33,  63 => 18,  56 => 14,  52 => 13,  47 => 11,  42 => 8,  36 => 6,  34 => 5,  31 => 4,  28 => 3,  11 => 1,);
    }
}
