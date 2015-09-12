<?php

/* AppBundle:Auth:login_x.html.twig */
class __TwigTemplate_da6f02a2b7b91c20152772897a9f5742f10e2394ef0f018f40fc3708174782e3 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:Auth:login_x.html.twig", 1);
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
    ";
        // line 5
        if ((isset($context["error"]) ? $context["error"] : $this->getContext($context, "error"))) {
            // line 6
            echo "        <div class=\"error\">";
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute((isset($context["error"]) ? $context["error"] : $this->getContext($context, "error")), "message", array()), array(), "messages"), "html", null, true);
            echo "</div>
    ";
        }
        // line 8
        echo "\t
\t<div id=\"formContainer\">
\t\t
\t\t
\t\t <form action=\"";
        // line 12
        echo $this->env->getExtension('routing')->getPath("_auth_login_check");
        echo "\" method=\"post\" id=\"login\">
            <input type=\"text\" id=\"username\" name=\"_username\" value=\"";
        // line 13
        echo twig_escape_filter($this->env, (isset($context["last_username"]) ? $context["last_username"] : $this->getContext($context, "last_username")), "html", null, true);
        echo "\" placeholder=\"E-mail\"/><br />
            <input type=\"password\" id=\"password\" name=\"_password\" placeholder=\"Password\"/><br />
\t\t\t<button type=\"submit\" class=\"btn btn-default\">
\t\t\t\t\t\t<i class=\"pe-7s-right-arrow\"></i>
\t\t\t</button>
    </form>
\t</div>
    <br>
    <br>
            <ul id=\"menu\">
                <li><a href=\"";
        // line 23
        echo $this->env->getExtension('routing')->getPath("_account_register");
        echo "\">";
        echo $this->env->getExtension('translator')->getTranslator()->trans("Registration", array(), "messages");
        echo "</a></li>
                
                ";
        // line 25
        if ((isset($context["error"]) ? $context["error"] : $this->getContext($context, "error"))) {
            // line 26
            echo "                <li><a href=\"";
            echo $this->env->getExtension('routing')->getPath("_account_forgot_passwd");
            echo "\">";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Forgot password ?", array(), "messages");
            echo "</a></li>
                ";
        }
        // line 28
        echo "            </ul>
        
";
    }

    public function getTemplateName()
    {
        return "AppBundle:Auth:login_x.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  82 => 28,  74 => 26,  72 => 25,  65 => 23,  52 => 13,  48 => 12,  42 => 8,  36 => 6,  34 => 5,  31 => 4,  28 => 3,  11 => 1,);
    }
}
