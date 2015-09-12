<?php

/* AppBundle:AccessID:assignloc.html.twig */
class __TwigTemplate_8271b914871a02296061df5c5fedf56f851e9aec94cf7008cead4b17ed98c2b2 extends Twig_Template
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

    <div class=\"aid-locations\">
\t<div class=\"row\">
\t";
        // line 7
        if (twig_length_filter($this->env, (isset($context["locations"]) ? $context["locations"] : $this->getContext($context, "locations")))) {
            // line 8
            echo "\t\t";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["locations"]) ? $context["locations"] : $this->getContext($context, "locations")));
            foreach ($context['_seq'] as $context["_key"] => $context["loc"]) {
                // line 9
                echo "\t\t         
\t\t  <label class=\"col-xs-4\" data-id=\"";
                // line 10
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "id", array()), "html", null, true);
                echo "\">
\t\t\t <input type=\"checkbox\" name=\"lid[";
                // line 11
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "id", array()), "html", null, true);
                echo "]\" value=\"1\" ";
                if (twig_in_filter($context["loc"], (isset($context["selected"]) ? $context["selected"] : $this->getContext($context, "selected")))) {
                    echo "checked";
                }
                echo ">
\t\t\t <div class=\"item ";
                // line 12
                if (($this->getAttribute($context["loc"], "enabled", array()) == true)) {
                    echo " enabled ";
                } else {
                    echo " disabled ";
                }
                echo "\">
\t\t\t <span class=\"id\">ID<strong>";
                // line 13
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "id", array()), "html", null, true);
                echo "</strong></span>
\t\t\t <div class=\"details-wrapper\">
\t\t\t Caption<br />
\t\t\t <strong>";
                // line 16
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "caption", array()), "html", null, true);
                echo "</strong><br />
\t\t\t Password<br />
\t\t\t <strong>";
                // line 18
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "password", array()), "html", null, true);
                echo "</strong>
\t\t\t </div>
\t\t\t <span class=\"status\">";
                // line 20
                if (($this->getAttribute($context["loc"], "enabled", array()) == true)) {
                    echo " enabled ";
                } else {
                    echo " disabled ";
                }
                echo "</span>
\t\t\t </div>
\t\t   </label>
\t\t  
\t\t ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['loc'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 25
            echo "\t";
        } else {
            // line 26
            echo "\t<div class=\"attention\">
\t\t<i class=\"pe-7s-attention\"></i>
\t\t<h2>No Locations</h2>
\t\t<p>There are no Locations which You can assign with this<br /> Access Identifier. Go to <a class=\"nav-link\" href=\"";
            // line 29
            echo $this->env->getExtension('routing')->getPath("_loc_list");
            echo "\">Locations</a> and create some.</p>
\t</div>
\t";
        }
        // line 32
        echo "\t</div>
\t</div>
    <div class=\"overlay-controls\">
    ";
        // line 35
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'widget');
        echo "
    </div>
    ";
        // line 37
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'form_end');
        echo "         
 </div>";
    }

    public function getTemplateName()
    {
        return "AppBundle:AccessID:assignloc.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  115 => 37,  110 => 35,  105 => 32,  99 => 29,  94 => 26,  91 => 25,  76 => 20,  71 => 18,  66 => 16,  60 => 13,  52 => 12,  44 => 11,  40 => 10,  37 => 9,  32 => 8,  30 => 7,  23 => 3,  19 => 1,);
    }
}
