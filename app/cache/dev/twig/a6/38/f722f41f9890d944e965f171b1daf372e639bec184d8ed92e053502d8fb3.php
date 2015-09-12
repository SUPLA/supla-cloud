<?php

/* AppBundle:Default:index.html.twig */
class __TwigTemplate_a638f722f41f9890d944e965f171b1daf372e639bec184d8ed92e053502d8fb3 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:Default:index.html.twig", 1);
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

    // line 2
    public function block_content($context, array $blocks = array())
    {
        // line 3
        echo "<div class=\"container margin-top-30 \">
<h1 class=\"big animated\">";
        // line 4
        echo $this->env->getExtension('translator')->getTranslator()->trans("Start Here", array(), "messages");
        echo "</h1>
<p class=\"intro animated\">It's easy to get Your home connected to SUPLA. <br />
All You need to do is follow these four steps.</p>

<div class=\"row margin-top-70\">
\t<div class=\"computer animated\">
\t\t<div class=\"computer-window\"><div class=\"title\">location.txt</div><div class=\"body\">[LOCATION]<br />ID=";
        // line 10
        echo twig_escape_filter($this->env, (isset($context["base_locid"]) ? $context["base_locid"] : $this->getContext($context, "base_locid")), "html", null, true);
        echo "<br />PASSWORD=";
        echo twig_escape_filter($this->env, (isset($context["base_locpwd"]) ? $context["base_locpwd"] : $this->getContext($context, "base_locpwd")), "html", null, true);
        echo " <br /><br />
\t\t[SERVER] <br />host=";
        // line 11
        echo twig_escape_filter($this->env, (isset($context["base_server"]) ? $context["base_server"] : $this->getContext($context, "base_server")), "html", null, true);
        echo "<br />
<span class=\"blink\">|</span></div></div>
\t\t<img src=";
        // line 13
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/img/computer.svg"), "html", null, true);
        echo ">
\t</div>
\t<div class=\"phone animated\">
\t";
        // line 16
        if (((isset($context["show_base_settings"]) ? $context["show_base_settings"] : $this->getContext($context, "show_base_settings")) == true)) {
            // line 17
            echo "\t\t<div class=\"phone-app\">
\t\t\t<input type=\"text\" id=\"server_address\" value=\"";
            // line 18
            echo twig_escape_filter($this->env, (isset($context["base_server"]) ? $context["base_server"] : $this->getContext($context, "base_server")), "html", null, true);
            echo "\"><br />
\t\t\t<label for=\"server_address\">Server address</label>
\t\t\t<input type=\"text\" id=\"access_identifier\" value=\"";
            // line 20
            echo twig_escape_filter($this->env, (isset($context["base_accessid"]) ? $context["base_accessid"] : $this->getContext($context, "base_accessid")), "html", null, true);
            echo "\"><br />
\t\t\t<label for=\"access_identifier\">Access Identifier</label>
\t\t\t<input type=\"text\" id=\"password\" value=\"";
            // line 22
            echo twig_escape_filter($this->env, (isset($context["base_accesspwd"]) ? $context["base_accesspwd"] : $this->getContext($context, "base_accesspwd")), "html", null, true);
            echo "\"><br />
\t\t\t<label for=\"password\" class=\"password\">Password</label>
\t\t\t<i class=\"pe-7s-check\"></i></div>
\t\t\t<img src=";
            // line 25
            echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/img/smartphone.svg"), "html", null, true);
            echo ">
\t\t</div>
\t";
        } else {
            // line 28
            echo "\t<div class=\"phone-app-error animated\">
\t\t\t<i class=\"pe-7s-attention\"></i>
\t\t\t<h3>Make it work</h3>
\t\t\t<p>You can not connect with Your I/O Devices until You have at least one <a class=\"nav-link\" href=\"";
            // line 31
            echo $this->env->getExtension('routing')->getPath("_aid_list");
            echo "\">Access Identifier</a> with assigned Location.</p>
\t\t\t</div>
\t\t\t<img src=";
            // line 33
            echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/img/smartphone-error.svg"), "html", null, true);
            echo ">
\t\t</div>
\t";
        }
        // line 36
        echo "\t</div>
<div class=\"row instruction\">
\t<div class=\"half computer-instructions\">
\t\t<h2 class=\"animated fadeInUp\">On Your computer...</h2>
\t\t<ol>
\t\t\t<li class=\"animated fadeInUp\">Download <a href=\"#\">SUPLA</a></li>
\t\t\t<li class=\"animated fadeInUp\">Copy extracted files onto SD Card</li>
\t\t\t<li class=\"animated fadeInUp\">Create text file named <strong>location.txt</strong> and copy above settings into it</li>
\t\t\t<li class=\"animated fadeInUp\">Move location.txt onto SD Card with SUPLA</li>
\t\t</ol>
\t</div>
\t<div class=\"half phone-instructions\">
\t\t<h2 class=\"animated fadeInUp\">On Your phone...</h2>
\t\t<ol>
\t\t\t<li class=\"animated fadeInUp\">Install SUPLA app from <a href=\"#\">Apple App Store</a> or <a href=\"#\">Google Play</a></li>
\t\t\t<li class=\"animated fadeInUp\">Open SUPLA app</li>
\t\t\t<li class=\"animated fadeInUp\">Tap <strong>+</strong> button</li>
\t\t\t<li class=\"animated fadeInUp\">Fill requested fields with settings listed above and tap <strong>Done</strong></li>
\t\t</ol>
</div>
</div> 
";
    }

    public function getTemplateName()
    {
        return "AppBundle:Default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  103 => 36,  97 => 33,  92 => 31,  87 => 28,  81 => 25,  75 => 22,  70 => 20,  65 => 18,  62 => 17,  60 => 16,  54 => 13,  49 => 11,  43 => 10,  34 => 4,  31 => 3,  28 => 2,  11 => 1,);
    }
}
