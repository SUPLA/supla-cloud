<?php

/* AppBundle:AccessID:aiddetails.html.twig */
class __TwigTemplate_455a38f89741faf9771583c0978f6c758f28eede77b2c5f02e2e12cfb5c4e998 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'iodevice_details' => array($this, 'block_iodevice_details'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        $this->displayBlock('iodevice_details', $context, $blocks);
    }

    public function block_iodevice_details($context, array $blocks = array())
    {
        // line 2
        echo "
  <div id=\"accessid-detail\" data-id=\"";
        // line 3
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "id", array()), "html", null, true);
        echo "\" data-item-path=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_aid_item", array("id" => $this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "id", array()))), "html", null, true);
        echo "\">
\t\t<div class=\"row margin-top-30 margin-bottom-60\">
\t\t\t<div class=\"info one-third\">
\t\t\t\t<h2>ID<strong>";
        // line 6
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "id", array()), "html", null, true);
        echo "</strong></h2>
\t\t\t\t<ul class=\"list-unstyled\">
\t\t\t\t\t<li class=\"row\">
\t\t\t\t\t\t<div class=\"col-xs-3\">
\t\t\t\t\t\t\t<span class=\"name\">";
        // line 10
        echo $this->env->getExtension('translator')->getTranslator()->trans("Caption", array(), "messages");
        echo "</span>
\t\t\t\t\t\t</div>
\t\t\t\t\t\t<div class=\"col-xs-9\">
\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t<input type=\"text\" class=\"form-control\" id=\"caption\" placeholder=\"";
        // line 14
        echo $this->env->getExtension('translator')->getTranslator()->trans("Caption", array(), "messages");
        echo "\" value=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "caption", array()), "html", null, true);
        echo "\">
\t\t\t\t\t\t\t\t<span class=\"input-group-btn\">
\t\t\t\t\t\t\t\t\t<button data-preloader=\"set-caption-pl\" class=\"btn btn-default last\" id=\"caption-save\" type=\"button\">";
        // line 16
        echo $this->env->getExtension('translator')->getTranslator()->trans("SAVE", array(), "messages");
        echo "</button>
\t\t\t\t\t\t\t\t\t<div class=\"btn btn-load\" id=\"set-caption-pl\" style=\"display: none;\"><div id=\"circleG\"><div id=\"circleG_1\" class=\"circleG\"></div><div id=\"circleG_2\" class=\"circleG\"></div><div id=\"circleG_3\" class=\"circleG\"></div></div></div>
\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>
\t\t\t\t\t</li>
\t\t\t\t\t<li class=\"row\">
\t\t\t\t\t\t<div class=\"col-xs-3\">
\t\t\t\t\t\t\t<span class=\"name\">";
        // line 24
        echo $this->env->getExtension('translator')->getTranslator()->trans("Password", array(), "messages");
        echo "</span>
\t\t\t\t\t\t</div>
\t\t\t\t\t\t<div class=\"col-xs-9\">
\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t<input type=\"text\" id=\"password\" class=\"form-control\" placeholder=\"";
        // line 28
        echo $this->env->getExtension('translator')->getTranslator()->trans("Password", array(), "messages");
        echo "\" value=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "password", array()), "html", null, true);
        echo "\">
\t\t\t\t\t\t\t\t<span class=\"input-group-btn\">
\t\t\t\t\t\t\t\t\t<button class=\"btn btn-default\" id=\"generate\" type=\"button\">";
        // line 30
        echo $this->env->getExtension('translator')->getTranslator()->trans("GENERATE", array(), "messages");
        echo "</button>
\t\t\t\t\t\t\t\t\t<button data-preloader=\"set-pwd-pl\" class=\"btn btn-default last\" id=\"password-save\" type=\"button\">";
        // line 31
        echo $this->env->getExtension('translator')->getTranslator()->trans("SAVE", array(), "messages");
        echo "</button>
\t\t\t\t\t\t\t\t\t
\t\t\t\t\t\t\t\t\t<div id=\"set-pwd-pl\" style=\"display: none;\" class=\"btn btn-load\"><div id=\"circleG\"><div id=\"circleG_1\" class=\"circleG\"></div><div id=\"circleG_2\" class=\"circleG\"></div><div id=\"circleG_3\" class=\"circleG\"></div></div></div>
\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>
\t\t\t\t\t</li>
\t\t\t\t</ul>
\t\t\t\t<div class=\"buttons\">
\t\t\t\t<div data-preloader=\"set-enabled-pl\" class=\"btn btn-default btn-enable\" ";
        // line 40
        if (($this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "enabled", array()) == false)) {
            echo "style=\"display: none;\"";
        }
        echo "><strong>";
        echo $this->env->getExtension('translator')->getTranslator()->trans("ENABLED", array(), "messages");
        echo "</strong><br >";
        echo $this->env->getExtension('translator')->getTranslator()->trans("CLICK TO DISABLE", array(), "messages");
        echo "</div>
\t\t\t\t<div data-preloader=\"set-enabled-pl\" class=\"btn btn-default btn-disable\" ";
        // line 41
        if (($this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "enabled", array()) == true)) {
            echo "style=\"display: none;\"";
        }
        echo "><strong>";
        echo $this->env->getExtension('translator')->getTranslator()->trans("DISABLED", array(), "messages");
        echo "</strong><br >";
        echo $this->env->getExtension('translator')->getTranslator()->trans("CLICK TO ENABLE", array(), "messages");
        echo "</div> 
\t\t\t\t<div class=\"btn btn-load btn-big\" id=\"set-enabled-pl\" style=\"display: none;\"><div id=\"circleG\"><div id=\"circleG_1\" class=\"circleG\"></div><div id=\"circleG_2\" class=\"circleG\"></div><div id=\"circleG_3\" class=\"circleG\"></div></div></div>
\t\t\t\t<a class=\"btn btn-default btn-edit\" id=\"overlay-delete\"><strong>DELETE</strong></a>
\t\t\t\t</div>
\t\t\t</div>
\t\t\t

\t\t\t<div class=\"assignments two-third\">
\t\t\t\t<h2>Assigned Locations</h2>
\t\t\t\t<table class=\"locationtable table table-hover\">
\t\t\t\t <thead>
\t\t\t\t\t<tr>
\t\t\t\t\t  <th style=\"width: 45px; text-align: center;\">ID</th>
\t\t\t\t\t  <th style=\"width: 100px;\">Password</th>
\t\t\t\t\t  <th>Caption</th>
\t\t\t\t\t</tr>
\t\t\t\t </thead>
\t\t\t\t <tbody>
\t\t\t\t ";
        // line 59
        if (twig_length_filter($this->env, (isset($context["locations"]) ? $context["locations"] : null))) {
            // line 60
            echo "\t\t\t\t   ";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["locations"]) ? $context["locations"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["loc"]) {
                // line 61
                echo "\t\t\t\t       <tr data-id=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "id", array()), "html", null, true);
                echo "\" class=\"supla-tooltip\" title=\"
\t\t\t\t\t\t
\t\t\t\t\t\t&lt;a class&#61;&quot;details ";
                // line 63
                if (($this->getAttribute($context["loc"], "enabled", array()) == true)) {
                    echo "enabled";
                } else {
                    echo "disabled";
                }
                echo "&quot; href&#61;&quot;";
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_loc_item", array("id" => $this->getAttribute($context["loc"], "id", array()))), "html", null, true);
                echo "&quot;&gt;
\t\t\t\t\t\t&lt;span class&#61;&quot;id&quot;&gt;ID&lt;strong&gt;";
                // line 64
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "id", array()), "html", null, true);
                echo "&lt;/strong&gt;  &lt;/span&gt; &lt;br/ &gt;
\t\t\t\t\t\t&lt;div class&#61;&quot;details-wrapper&quot;&gt; Caption &lt;br/ &gt;
\t\t\t\t\t\t&lt;strong&gt;";
                // line 66
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "caption", array()), "html", null, true);
                echo "&lt;/strong&gt;&lt;br/ &gt;
\t\t\t\t\t\tPassword &lt;br/ &gt;
\t\t\t\t\t\t&lt;strong&gt;";
                // line 68
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "password", array()), "html", null, true);
                echo "&lt;/strong&gt;&lt;/div&gt;
\t\t\t\t\t\t
\t\t\t\t\t\t&lt;br/ &gt;
\t\t\t\t\t\t
\t\t\t\t\t\t&lt;span class&#61;&quot;status&quot;&gt;";
                // line 72
                if (($this->getAttribute($context["loc"], "enabled", array()) == true)) {
                    echo "enabled";
                } else {
                    echo "disabled";
                }
                echo "&lt;/span&gt;
\t\t\t\t\t\t&lt;/a&gt;
\t\t\t\t\t\t
\t\t\t\t\t\t\">
\t\t\t\t\t\t <td class=\"text-center\"><a href=\"";
                // line 76
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_loc_item", array("id" => $this->getAttribute($context["loc"], "id", array()))), "html", null, true);
                echo "\" ";
                if (twig_test_empty($this->getAttribute($context["loc"], "enabled", array()))) {
                    echo " class=\"disabled\" ";
                }
                echo "><strong>";
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "id", array()), "html", null, true);
                echo "</strong></a></td>
\t\t\t\t\t\t <td>";
                // line 77
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "password", array()), "html", null, true);
                echo "</td>
\t\t\t\t\t\t <td scope=\"row\"><a class=\"caption\" href=\"";
                // line 78
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_loc_item", array("id" => $this->getAttribute($context["loc"], "id", array()))), "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute($context["loc"], "caption", array()), "html", null, true);
                echo "</a></td>
\t\t\t\t\t   </tr>
\t\t\t\t   ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['loc'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 81
            echo "\t\t\t\t  ";
        } else {
            // line 82
            echo "\t\t\t\t  \t<tr><td></td><td colspan=\"2\">No Locations</td></tr>
\t\t\t\t  ";
        }
        // line 84
        echo "
\t\t\t\t   <tr>
\t\t\t\t\t <td><a href=\"#\"><i class=\"pe-7s-more\"></i></a></td>
\t\t\t\t\t <td colspan=2><a href=\"#\" id=\"overlay-assignments\"><strong>";
        // line 87
        echo $this->env->getExtension('translator')->getTranslator()->trans("Assign Locations", array(), "messages");
        echo "</strong></a></td>
\t\t\t\t   </tr>
\t\t\t\t  
\t\t\t\t\t  
\t\t\t\t </tbody>
\t\t\t   </table>
\t\t\t</div>
\t</div>

<div class=\"overlay-delete overlay-data\">
\t<div class=\"dialog\">
\t\t<h1>Are You sure?</h1>
\t\t<p>Please confirm removal of Access Identifier <strong>";
        // line 99
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "id", array()), "html", null, true);
        echo "</strong>. You will be no longer able to connect through this Access ID.</p>
\t\t<div class=\"controls\">
\t\t\t<a href=\"#\" class=\"overlay-delete-close cancel\">CANCEL</a>
\t\t\t<a href=\"";
        // line 102
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_aid_item_delete", array("id" => $this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "id", array()))), "html", null, true);
        echo "\" id=\"assign_type_save\" class=\"save\"><i class=\"pe-7s-check\"></i></a></div>
\t\t</div>
</div>
\t
<div class=\"overlay overlay-data overlay-assignments\">
\t<div class=\"overlay-title\"><h1>Assign Locations</h1>
\t  <p>Choose which locations will be assigned to Access Identifier <strong>";
        // line 108
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["accessid"]) ? $context["accessid"] : null), "id", array()), "html", null, true);
        echo "</strong></p></div>
      <div class=\"overlay-list\">
\t\t  <div class=\"assign-list\">   
\t\t  </div>
\t</div>
</div>
\t
\t<script src=\"";
        // line 115
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/details.js"), "html", null, true);
        echo "\"></script>
\t
";
    }

    public function getTemplateName()
    {
        return "AppBundle:AccessID:aiddetails.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  264 => 115,  254 => 108,  245 => 102,  239 => 99,  224 => 87,  219 => 84,  215 => 82,  212 => 81,  201 => 78,  197 => 77,  187 => 76,  176 => 72,  169 => 68,  164 => 66,  159 => 64,  149 => 63,  143 => 61,  138 => 60,  136 => 59,  109 => 41,  99 => 40,  87 => 31,  83 => 30,  76 => 28,  69 => 24,  58 => 16,  51 => 14,  44 => 10,  37 => 6,  29 => 3,  26 => 2,  20 => 1,);
    }
}
