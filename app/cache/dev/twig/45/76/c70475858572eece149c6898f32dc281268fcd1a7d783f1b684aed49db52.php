<?php

/* AppBundle:Location:assignaid.html.twig */
class __TwigTemplate_4576c70475858572eece149c6898f32dc281268fcd1a7d783f1b684aed49db52 extends Twig_Template
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
        echo "<div class=\"loc-accessids\">
    
     ";
        // line 3
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'form_start');
        echo "

    <div class=\"loc-accessids\">
\t<div class=\"row\">
\t";
        // line 7
        if (twig_length_filter($this->env, (isset($context["aids"]) ? $context["aids"] : $this->getContext($context, "aids")))) {
            // line 8
            echo "\t\t";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["aids"]) ? $context["aids"] : $this->getContext($context, "aids")));
            foreach ($context['_seq'] as $context["_key"] => $context["aid"]) {
                // line 9
                echo "\t\t\t<label class=\"col-xs-4\" data-id=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
                echo "\">
\t\t\t<input type=\"checkbox\" name=\"aid[";
                // line 10
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
                echo "]\" value=\"1\" ";
                if (twig_in_filter($context["aid"], (isset($context["selected"]) ? $context["selected"] : $this->getContext($context, "selected")))) {
                    echo "checked";
                }
                echo ">
\t\t\t<div class=\"item ";
                // line 11
                if (($this->getAttribute($context["aid"], "enabled", array()) == true)) {
                    echo " enabled ";
                } else {
                    echo " disabled ";
                }
                echo "\">
\t\t\t<span class=\"id\">ID<strong>";
                // line 12
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
                echo "</strong></span>
\t\t\t<div class=\"details-wrapper\">
\t\t\t Caption<br />
\t\t\t <strong>";
                // line 15
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "caption", array()), "html", null, true);
                echo "</strong><br />
\t\t\t Password<br />
\t\t\t <strong>";
                // line 17
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "password", array()), "html", null, true);
                echo "</strong>
\t\t\t </div>
\t\t\t</div>
\t\t\t</label>
\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['aid'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 22
            echo "\t";
        } else {
            // line 23
            echo "\t<div class=\"attention\">
\t\t<i class=\"pe-7s-attention\"></i>
\t\t<h2>No Access Identifiers</h2>
\t\t<p>There are no Access Identifiers which You can assign with this<br /> Location. Go to <a class=\"nav-link\" href=\"";
            // line 26
            echo $this->env->getExtension('routing')->getPath("_aid_list");
            echo "\">Access Identifiers</a> and create some.</p>
\t</div>
\t";
        }
        // line 29
        echo "\t</div>


\t    </div>
    <div class=\"overlay-controls\">
    ";
        // line 34
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'widget');
        echo "
    </div>
    ";
        // line 36
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'form_end');
        echo "         
 </div>";
    }

    public function getTemplateName()
    {
        return "AppBundle:Location:assignaid.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  106 => 36,  101 => 34,  94 => 29,  88 => 26,  83 => 23,  80 => 22,  69 => 17,  64 => 15,  58 => 12,  50 => 11,  42 => 10,  37 => 9,  32 => 8,  30 => 7,  23 => 3,  19 => 1,);
    }
}
