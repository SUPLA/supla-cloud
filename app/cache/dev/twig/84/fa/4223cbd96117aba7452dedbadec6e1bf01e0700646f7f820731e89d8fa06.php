<?php

/* GoogleRecaptchaBundle:Form:google_recaptcha_widget.html.twig */
class __TwigTemplate_84fa4223cbd96117aba7452dedbadec6e1bf01e0700646f7f820731e89d8fa06 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'google_recaptcha_widget' => array($this, 'block_google_recaptcha_widget'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 11
        echo "
";
        // line 12
        $this->displayBlock('google_recaptcha_widget', $context, $blocks);
    }

    public function block_google_recaptcha_widget($context, array $blocks = array())
    {
        // line 13
        echo "    ";
        ob_start();
        // line 14
        echo "        ";
        if ($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "google_recaptcha_enabled", array())) {
            // line 15
            echo "            ";
            if ($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "options", array(), "any", true, true)) {
                // line 16
                echo "                <script type=\"text/javascript\">
                    var RecaptchaOptions = ";
                // line 17
                echo twig_jsonencode_filter($this->getAttribute((isset($context["attr"]) ? $context["attr"] : $this->getContext($context, "attr")), "options", array()));
                echo "
                </script>
            ";
            }
            // line 20
            echo "            <script src=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "url_challenge", array()), "html", null, true);
            echo "\" type=\"text/javascript\"></script>
            <noscript>
                <iframe src=\"";
            // line 22
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "url_noscript", array()), "html", null, true);
            echo "\" height=\"300\" width=\"500\"></iframe><br/>
                <textarea name=\"recaptcha_challenge_field\" rows=\"3\" cols=\"40\"></textarea>
                <input type=\"hidden\" name=\"recaptcha_response_field\" value=\"manual_challenge\"/>
            </noscript>
        ";
        }
        // line 27
        echo "    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }

    public function getTemplateName()
    {
        return "GoogleRecaptchaBundle:Form:google_recaptcha_widget.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  61 => 27,  53 => 22,  47 => 20,  41 => 17,  38 => 16,  35 => 15,  32 => 14,  29 => 13,  23 => 12,  20 => 11,);
    }
}
