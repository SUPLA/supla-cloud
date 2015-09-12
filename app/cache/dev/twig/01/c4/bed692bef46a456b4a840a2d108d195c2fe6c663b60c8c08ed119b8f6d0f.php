<?php

/* AppBundle:Auth:login.html.twig */
class __TwigTemplate_01c4bed692bef46a456b4a840a2d108d195c2fe6c663b60c8c08ed119b8f6d0f extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:Auth:login.html.twig", 1);
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
        echo "<div id=\"authpage\">
<div id=\"fullpage\">
\t<div class=\"section\" id=\"section0\">
\t    <div class=\"slide login active\">
\t\t\t";
        // line 7
        if ((isset($context["error"]) ? $context["error"] : $this->getContext($context, "error"))) {
            // line 8
            echo "\t\t\t\t<div class=\"error\">";
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute((isset($context["error"]) ? $context["error"] : $this->getContext($context, "error")), "message", array()), array(), "messages"), "html", null, true);
            echo "</div>
\t\t\t";
        }
        // line 10
        echo "\t\t\t
\t\t\t<form id=\"login\" class=\"simform\" action=\"";
        // line 11
        echo $this->env->getExtension('routing')->getPath("_auth_login_check");
        echo "\" method=\"post\">
\t\t\t\t\t<div class=\"simform-inner\">
\t\t\t\t\t\t<ol class=\"questions\">
\t\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t\t<input id=\"q1\" name=\"_username\" type=\"email\" placeholder=\"Type Your Email\" value=\"";
        // line 15
        echo twig_escape_filter($this->env, (isset($context["last_username"]) ? $context["last_username"] : $this->getContext($context, "last_username")), "html", null, true);
        echo "\"/>
\t\t\t\t\t\t\t</li>
\t\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t\t<input id=\"q2\" name=\"_password\" type=\"password\" placeholder=\"And Your Password\"/>
\t\t\t\t\t\t\t</li>
\t\t\t\t\t\t</ol>
\t\t\t\t\t\t<button class=\"submit\" type=\"submit\">Send answers</button>
\t\t\t\t\t\t<div class=\"controls\">
\t\t\t\t\t\t\t<button class=\"next show\"><i class=\"pe-7s-right-arrow\"></i></button>
\t\t\t\t\t\t\t<div class=\"progress\"></div>
\t\t\t\t\t\t\t<span class=\"number\">
\t\t\t\t\t\t\t\t<span class=\"number-current\"></span>
\t\t\t\t\t\t\t\t<span class=\"number-total\"></span>
\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t<span class=\"error-message\"></span>
\t\t\t\t\t\t</div>
\t\t\t\t\t</div>
\t\t\t\t</form>
\t\t\t
\t\t</div>
\t    <div class=\"slide\">
\t\t\t<form id=\"registration\" class=\"simform\">
\t\t\t\t<div class=\"simform-inner\">
\t\t\t\t\t<ol class=\"questions\">
\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t<span><label for=\"q1\">What's your email?</label></span>
\t\t\t\t\t\t\t<input id=\"q1\" name=\"q1\" type=\"email\"/>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t<span><label for=\"q2\">Type Your Email</label></span>
\t\t\t\t\t\t\t<input id=\"q2\" name=\"q2\" type=\"password\"/>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t<span><label for=\"q3\">Retype Your Password</label></span>
\t\t\t\t\t\t\t<input id=\"q3\" name=\"q3\" type=\"text\"/>
\t\t\t\t\t\t</li>
\t\t\t\t\t</ol><!-- /questions -->
\t\t\t\t\t<button class=\"submit\" type=\"submit\">Send answers</button>
\t\t\t\t\t<div class=\"controls\">
\t\t\t\t\t\t<button class=\"next\"><i class=\"pe-7s-angle-right\"></i></button>
\t\t\t\t\t\t<div class=\"progress\"></div>
\t\t\t\t\t\t<span class=\"number\">
\t\t\t\t\t\t\t<span class=\"number-current\"></span>
\t\t\t\t\t\t\t<span class=\"number-total\"></span>
\t\t\t\t\t\t</span>
\t\t\t\t\t\t<span class=\"error-message\"></span>
\t\t\t\t\t</div><!-- / controls -->
\t\t\t\t</div><!-- /simform-inner -->
\t\t\t\t<span class=\"final-message\"></span>
\t\t\t</form></div>
\t\t<div class=\"slide password-remainder\"><h1>Password Remeinder</h1></div>
\t</div>
\t<div class=\"section\" id=\"section1\">
\t    <div class=\"slide \"><h1>Simple Demo</h1></div>
\t    <div class=\"slide active\"><h1>Only text</h1></div>
\t    <div class=\"slide\"><h1>And text</h1></div>
\t    <div class=\"slide\"><h1>And more text</h1></div>
\t</div>
\t<div class=\"section\" id=\"section2\"><h1>No wraps, no extra markup</h1></div>
\t<div class=\"section\" id=\"section3\"><h1>Just the simplest demo ever</h1></div>
</div>

<footer>
<a class=\"brand\" href=\"http://supla.org\">&copy; supla 2015</a>
<ul class=\"menu list-inline\">
\t<li><a href=\"#3rdPage\">What is it?</a></li>
\t<li><a href=\"#firstPage\">Sign In</li>
\t<li><a href=\"#firstPage/1\">Sign Up</li>
\t<li><a href=\"#firstPage/2\">Password Remeinder</a></li>
</ul>
</footer>
</div>

<script src=\"";
        // line 88
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/classie.js"), "html", null, true);
        echo "\"></script>
\t\t<script src=\"";
        // line 89
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/stepsForm.js"), "html", null, true);
        echo "\"></script>
\t\t\t<script>
                var theForm = document.getElementById( 'login' );
                theForm.setAttribute( \"autocomplete\", \"off\" );
    
                new stepsForm( theForm, {
                onSubmit : function( form ) {
                classie.addClass( theForm.querySelector( '.simform-inner' ), 'hide' );
\t\t\t\t \$(\"#authpage\").fadeOut(500, function(){ form.submit() });
                }
                });
\t\t\t\tvar theForm = document.getElementById( 'registration' );
                theForm.setAttribute( \"autocomplete\", \"off\" );
    
                new stepsForm( theForm, {
                onSubmit : function( form ) {
                classie.addClass( theForm.querySelector( '.simform-inner' ), 'hide' );
\t\t\t\tform.submit()
                }
                });
            </script>
";
    }

    public function getTemplateName()
    {
        return "AppBundle:Auth:login.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  135 => 89,  131 => 88,  55 => 15,  48 => 11,  45 => 10,  39 => 8,  37 => 7,  31 => 3,  28 => 2,  11 => 1,);
    }
}
