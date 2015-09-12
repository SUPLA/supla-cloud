<?php

/* AppBundle::layout.html.twig */
class __TwigTemplate_b8bd33cd802911381c7a60afd896b05d960f0f78300e3e93174cbbae43aa2b7e extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'content_header' => array($this, 'block_content_header'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"";
        // line 4
        echo twig_escape_filter($this->env, $this->env->getCharset(), "html", null, true);
        echo "\" />
        <meta name=\"robots\" content=\"noindex,nofollow\" />
        <title>SUPLA CLOUD</title>
\t\t";
        // line 7
        if ($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array())) {
            // line 8
            echo "\t\t\t<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
\t\t";
        } else {
            // line 10
            echo "\t\t\t<link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
\t\t\t<link href=\"";
            // line 11
            echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/css/supla-login.css"), "html", null, true);
            echo "\" rel=\"stylesheet\" />
\t\t";
        }
        // line 13
        echo "\t\t
\t\t<link href=\"";
        // line 14
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/css/fonts.css"), "html", null, true);
        echo "\" rel=\"stylesheet\" />
        <link href=\"";
        // line 15
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/bootstrap/css/bootstrap.min.css"), "html", null, true);
        echo "\" rel=\"stylesheet\" />
\t\t<link href=\"";
        // line 16
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/css/supla.css"), "html", null, true);
        echo "\" rel=\"stylesheet\" />

        <script src=\"";
        // line 18
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/jquery-2.1.4.min.js"), "html", null, true);
        echo "\"></script>
\t\t<script>
\t\t\twindow.jQuery || document.write('<script src=\"";
        // line 20
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/jquery-2.1.4.min.js"), "html", null, true);
        echo "\"><\\/script>')
\t\t</script>
        <script src=\"";
        // line 22
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/bootstrap/js/bootstrap.min.js"), "html", null, true);
        echo "\"></script>
\t\t<script src=\"";
        // line 23
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/jquery.turn-off-tv.js"), "html", null, true);
        echo "\"></script>
        <script src=\"";
        // line 24
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/pnotify.custom.min.js"), "html", null, true);
        echo "\"></script>
        ";
        // line 25
        if ($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array())) {
            // line 26
            echo "\t\t\t";
            if (((($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_loc_list") || ($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_aid_list")) || ($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_iodev_item"))) {
                // line 27
                echo "\t\t\t\t<script src=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/owl.carousel.min.js"), "html", null, true);
                echo "\"></script>
\t\t\t\t<script src=\"";
                // line 28
                echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/jquery.liveFilter.js"), "html", null, true);
                echo "\"></script>
\t\t\t\t<script src=\"";
                // line 29
                echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/pGenerator.jquery.js"), "html", null, true);
                echo "\"></script>
\t\t\t\t<script src=\"";
                // line 30
                echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/jquery.tooltipster.min.js"), "html", null, true);
                echo "\"></script>
\t\t\t";
            }
            // line 32
            echo "\t\t\t";
            if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_iodev_list")) {
                // line 33
                echo "\t\t\t\t<script src=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/isotope.pkgd.min.js"), "html", null, true);
                echo "\"></script>
\t\t\t\t<script src=\"";
                // line 34
                echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/iodev.js"), "html", null, true);
                echo "\"></script>
\t\t\t";
            }
            // line 36
            echo "\t\t";
        } else {
            // line 37
            echo "\t\t\t<script src=\"http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js\"></script>
\t\t\t<script type=\"text/javascript\" src=\"";
            // line 38
            echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/jquery.fullPage.min.js"), "html", null, true);
            echo "\"></script>
\t\t\t<script type=\"text/javascript\" src=\"";
            // line 39
            echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/modernizr.custom.js"), "html", null, true);
            echo "\"></script>
\t\t\t<script type=\"text/javascript\">
\t\t\t\t\$(document).ready(function() {
\t\t\t\t\t\$('#fullpage').fullpage({
\t\t\t\t\t\tanchors: ['firstPage', 'secondPage', '3rdPage', '4thpage', 'lastPage'],
\t\t\t\t\t\tmenu: '#menu',
\t\t\t\t\t\tscrollingSpeed: 1000
\t\t\t\t\t});
\t\t\t\t\t\$.fn.fullpage.setMouseWheelScrolling(false);
   \t\t\t\t\t\$.fn.fullpage.setAllowScrolling(false);
\t\t\t\t});
\t\t\t</script>
        ";
        }
        // line 52
        echo "        <script src=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/supla.js"), "html", null, true);
        echo "\"></script>
\t
        
    </head>
    <body ";
        // line 56
        if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_auth_login")) {
            echo " class=\"login\" ";
        }
        echo " ";
        if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_iodev_item")) {
            echo " class=\"device-details\" ";
        }
        // line 57
        echo "\t";
        if ((array_key_exists("homepage_viewed", $context) && ((isset($context["homepage_viewed"]) ? $context["homepage_viewed"] : $this->getContext($context, "homepage_viewed")) == false))) {
            echo " class=\"first-time\" ";
        }
        echo ">
\t
\t<div id=\"hello\">
\t\t<div class=\"row\">
\t\t\t<div class=\"col-xs-12\"><h1>Hi</h1></div>
\t\t</div>
\t</div>
    
    ";
        // line 65
        $this->loadTemplate("AppBundle:Dialogs:flashmsgs.html.twig", "AppBundle::layout.html.twig", 65)->display($context);
        // line 66
        echo "    
    ";
        // line 67
        $this->displayBlock('content_header', $context, $blocks);
        // line 99
        echo "\t
\t<div id=\"block\">
        ";
        // line 101
        $this->displayBlock('content', $context, $blocks);
        // line 102
        echo " \t</div>
\t
    </body>
\t<script>
\t\$(document).ready(function() {
\t\t\$('.nav-link').click(function(){
\t\t\t  var href= \$(this).attr('href');
\t\t\t  \$('#block').fadeOut( 300, function(){
\t\t\t\t\twindow.location=href;
\t\t\t  })
\t\t\t  return false;
\t\t});
\t});
\t</script>
</html>
";
    }

    // line 67
    public function block_content_header($context, array $blocks = array())
    {
        // line 68
        echo "    
        ";
        // line 69
        if ($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array())) {
            // line 70
            echo "\t\t
\t\t<div class=\"navbar-holder\">
\t\t<nav class=\"navbar navbar-default animated\">
        \t<div class=\"container\">
\t\t\t\t<ul class=\"nav navbar-nav navbar-center\">
\t\t\t\t  <li class=\"pull-left\"><a href=\"http://supla.org\" target=\"_blank\"><img src=";
            // line 75
            echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/img/logo.svg"), "html", null, true);
            echo "> supla</a></li>
\t\t\t\t  <li ";
            // line 76
            if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_homepage")) {
                echo " class=\"active\" ";
            }
            echo "><a class=\"nav-link\" href=\"";
            echo $this->env->getExtension('routing')->getPath("_homepage");
            echo "\"><i class=\"pe-7s-rocket\"></i><br /> ";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Start Here", array(), "messages");
            echo "</a></li>
\t\t\t\t  <li ";
            // line 77
            if (((($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_iodev_list") || ($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_iodev_item")) || ($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_iodev_channel_item_edit"))) {
                echo " class=\"active\" ";
            }
            echo "><a class=\"nav-link\" href=\"";
            echo $this->env->getExtension('routing')->getPath("_iodev_list");
            echo "\"><i class=\"pe-7s-plug\"></i><br /> ";
            echo $this->env->getExtension('translator')->getTranslator()->trans("I/O Devices", array(), "messages");
            echo "</a></li>
\t\t\t\t  <li ";
            // line 78
            if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_loc_list")) {
                echo " class=\"active\" ";
            }
            echo "><a class=\"nav-link\" href=\"";
            echo $this->env->getExtension('routing')->getPath("_loc_list");
            echo "\"><i class=\"pe-7s-home\"></i><br /> ";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Locations", array(), "messages");
            echo "</a></li>
\t\t\t\t  <li ";
            // line 79
            if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "request", array()), "get", array(0 => "_route"), "method") == "_aid_list")) {
                echo " class=\"active\" ";
            }
            echo "><a class=\"nav-link\" href=\"";
            echo $this->env->getExtension('routing')->getPath("_aid_list");
            echo "\"><i class=\"pe-7s-key\"></i><br /> ";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Access Identifiers", array(), "messages");
            echo "</a></li>
\t\t\t\t  <li class=\"dropdown pull-right\">
\t\t\t\t\t\t<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">";
            // line 81
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "security", array()), "token", array()), "user", array()), "email", array()), "html", null, true);
            echo " <span class=\"caret\"></span></a>
\t\t\t\t\t\t<ul class=\"dropdown-menu\">
\t\t\t\t\t  \t\t<li><a class=\"nav-link\" href=\"";
            // line 83
            echo $this->env->getExtension('routing')->getPath("_account_view");
            echo "\">";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Account", array(), "messages");
            echo "</a></li>
\t\t\t\t\t\t\t<li class=\"divider\"></li>
\t\t\t\t\t\t\t<li class=\"dropdown-header\">Languages</li>
\t\t\t\t\t\t\t<li><a href=\"#\">English</a></li>
\t\t\t\t\t\t\t<li><a href=\"#\">Deutsch</a></li>
\t\t\t\t\t\t\t<li><a href=\"#\">Polski</a></li>
\t\t\t\t\t\t\t<li class=\"divider\"></li>
\t\t\t\t\t\t\t<li><a id=\"black\" href=\"";
            // line 90
            echo $this->env->getExtension('routing')->getPath("_auth_logout");
            echo "\">";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Sign Out", array(), "messages");
            echo "</a></li>
\t\t\t\t\t\t</ul>
\t\t\t\t  \t</li>
\t\t\t\t</ul>
           </div>
      </nav>
\t  </div>
        ";
        }
        // line 98
        echo "    ";
    }

    // line 101
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "AppBundle::layout.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  302 => 101,  298 => 98,  285 => 90,  273 => 83,  268 => 81,  257 => 79,  247 => 78,  237 => 77,  227 => 76,  223 => 75,  216 => 70,  214 => 69,  211 => 68,  208 => 67,  189 => 102,  187 => 101,  183 => 99,  181 => 67,  178 => 66,  176 => 65,  162 => 57,  154 => 56,  146 => 52,  130 => 39,  126 => 38,  123 => 37,  120 => 36,  115 => 34,  110 => 33,  107 => 32,  102 => 30,  98 => 29,  94 => 28,  89 => 27,  86 => 26,  84 => 25,  80 => 24,  76 => 23,  72 => 22,  67 => 20,  62 => 18,  57 => 16,  53 => 15,  49 => 14,  46 => 13,  41 => 11,  38 => 10,  34 => 8,  32 => 7,  26 => 4,  21 => 1,);
    }
}
