<?php

/* AppBundle:Location:locdetails.html.twig */
class __TwigTemplate_bc40c3f2466c681a7e519258abd8fb3d1da44d0cced76d9c899413220f2d31c4 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'location_details' => array($this, 'block_location_details'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        $this->displayBlock('location_details', $context, $blocks);
    }

    public function block_location_details($context, array $blocks = array())
    {
        // line 2
        echo "\t<div id=\"location-detail\" data-id=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["location"]) ? $context["location"] : null), "id", array()), "html", null, true);
        echo "\" data-item-path=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_loc_item", array("id" => $this->getAttribute((isset($context["location"]) ? $context["location"] : null), "id", array()))), "html", null, true);
        echo "\">
\t\t<div class=\"row margin-top-30 margin-bottom-70\">
\t\t\t<div class=\"info one-third\">
\t\t\t\t<h2>ID<strong>";
        // line 5
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["location"]) ? $context["location"] : null), "id", array()), "html", null, true);
        echo "</strong></h2>
\t\t\t\t<ul class=\"list-unstyled\">
\t\t\t\t\t<li class=\"row\">
\t\t\t\t\t\t<div class=\"col-xs-3\">
\t\t\t\t\t\t\t<span class=\"name\">";
        // line 9
        echo $this->env->getExtension('translator')->getTranslator()->trans("Caption", array(), "messages");
        echo "</span>
\t\t\t\t\t\t</div>
\t\t\t\t\t\t<div class=\"col-xs-9\">
\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t<input type=\"text\" class=\"form-control\" id=\"caption\" placeholder=\"";
        // line 13
        echo $this->env->getExtension('translator')->getTranslator()->trans("Caption", array(), "messages");
        echo "\" value=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["location"]) ? $context["location"] : null), "caption", array()), "html", null, true);
        echo "\">
\t\t\t\t\t\t\t\t<span class=\"input-group-btn\">
\t\t\t\t\t\t\t\t\t<button data-preloader=\"set-caption-pl\" class=\"btn btn-default last\" id=\"caption-save\" type=\"button\">";
        // line 15
        echo $this->env->getExtension('translator')->getTranslator()->trans("SAVE", array(), "messages");
        echo "</button>
\t\t\t\t\t\t\t\t\t<div id=\"set-caption-pl\" style=\"display: none;\" class=\"btn btn-load\"><div id=\"circleG\"><div id=\"circleG_1\" class=\"circleG\"></div><div id=\"circleG_2\" class=\"circleG\"></div><div id=\"circleG_3\" class=\"circleG\"></div></div></div>
\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>
\t\t\t\t\t</li>
\t\t\t\t\t<li class=\"row\">
\t\t\t\t\t\t<div class=\"col-xs-3\">
\t\t\t\t\t\t\t<span class=\"name\">";
        // line 23
        echo $this->env->getExtension('translator')->getTranslator()->trans("Password", array(), "messages");
        echo "</span>
\t\t\t\t\t\t</div>
\t\t\t\t\t\t<div class=\"col-xs-9\">
\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t<input type=\"text\" id=\"password\" class=\"form-control\" placeholder=\"";
        // line 27
        echo $this->env->getExtension('translator')->getTranslator()->trans("Password", array(), "messages");
        echo "\" value=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["location"]) ? $context["location"] : null), "password", array()), "html", null, true);
        echo "\">
\t\t\t\t\t\t\t\t<span class=\"input-group-btn\">
\t\t\t\t\t\t\t\t\t<button class=\"btn btn-default\" id=\"generate-short\" type=\"button\">";
        // line 29
        echo $this->env->getExtension('translator')->getTranslator()->trans("GENERATE", array(), "messages");
        echo "</button>
\t\t\t\t\t\t\t\t\t<button data-preloader=\"set-pwd-pl\" class=\"btn btn-default last\" id=\"password-save\" type=\"button\">";
        // line 30
        echo $this->env->getExtension('translator')->getTranslator()->trans("SAVE", array(), "messages");
        echo "</button>
\t\t\t\t\t\t\t\t\t<div id=\"set-pwd-pl\" style=\"display: none;\" class=\"btn btn-load\"><div id=\"circleG\"><div id=\"circleG_1\" class=\"circleG\"></div><div id=\"circleG_2\" class=\"circleG\"></div><div id=\"circleG_3\" class=\"circleG\"></div></div></div>
\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>
\t\t\t\t\t</li>
\t\t\t\t</ul>
\t\t\t\t<div class=\"buttons\">
\t\t\t\t<div data-preloader=\"set-enabled-pl\" class=\"btn btn-default btn-enable\" ";
        // line 38
        if (($this->getAttribute((isset($context["location"]) ? $context["location"] : null), "enabled", array()) == false)) {
            echo "style=\"display: none;\"";
        }
        echo "><strong>";
        echo $this->env->getExtension('translator')->getTranslator()->trans("ENABLED", array(), "messages");
        echo "</strong><br >";
        echo $this->env->getExtension('translator')->getTranslator()->trans("CLICK TO DISABLE", array(), "messages");
        echo "</div>
\t\t\t\t<div data-preloader=\"set-enabled-pl\" class=\"btn btn-default btn-disable\" ";
        // line 39
        if (($this->getAttribute((isset($context["location"]) ? $context["location"] : null), "enabled", array()) == true)) {
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
\t\t\t\t<div>
\t\t\t\t\t<h2>";
        // line 48
        echo $this->env->getExtension('translator')->getTranslator()->trans("Assigned Access Identifiers", array(), "messages");
        echo "</h2>
\t\t\t\t\t<table class=\"locationtable table table-hover\">
\t\t\t\t\t <thead>
\t\t\t\t\t\t<tr>
\t\t\t\t\t\t  <th style=\"width: 45px; text-align: center;\">ID</th>
\t\t\t\t\t\t  <th style=\"width: 100px;\">Password</th>
\t\t\t\t\t\t  <th>Caption</th>
\t\t\t\t\t\t</tr>
\t\t\t\t\t\t";
        // line 56
        if (twig_length_filter($this->env, (isset($context["aids"]) ? $context["aids"] : null))) {
            // line 57
            echo "\t\t\t\t\t\t\t";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["aids"]) ? $context["aids"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["aid"]) {
                // line 58
                echo "\t\t\t\t\t\t\t<tr data-id=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
                echo "\" class=\"supla-tooltip\" title=\"
\t\t\t\t\t\t\t
\t\t\t\t\t\t\t&lt;a class&#61;&quot;details ";
                // line 60
                if (($this->getAttribute($context["aid"], "enabled", array()) == true)) {
                    echo "enabled";
                } else {
                    echo "disabled";
                }
                echo "&quot; href&#61;&quot;";
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_aid_item", array("id" => $this->getAttribute($context["aid"], "id", array()))), "html", null, true);
                echo "&quot;&gt;
\t\t\t\t\t\t\t&lt;span class&#61;&quot;id&quot;&gt;ID&lt;strong&gt;";
                // line 61
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
                echo "&lt;/strong&gt;  &lt;/span&gt; &lt;br/ &gt;
\t\t\t\t\t\t\t&lt;div class&#61;&quot;details-wrapper&quot;&gt; Caption &lt;br/ &gt;
\t\t\t\t\t\t\t&lt;strong&gt;";
                // line 63
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "caption", array()), "html", null, true);
                echo "&lt;/strong&gt;&lt;br/ &gt;
\t\t\t\t\t\t\tPassword &lt;br/ &gt;
\t\t\t\t\t\t\t&lt;strong&gt;";
                // line 65
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "password", array()), "html", null, true);
                echo "&lt;/strong&gt;&lt;/div&gt;
\t\t\t\t\t\t\t
\t\t\t\t\t\t\t&lt;br/ &gt;
\t\t\t\t\t\t\t
\t\t\t\t\t\t\t&lt;span class&#61;&quot;status&quot;&gt;";
                // line 69
                if (($this->getAttribute($context["aid"], "enabled", array()) == true)) {
                    echo "enabled";
                } else {
                    echo "disabled";
                }
                echo "&lt;/span&gt;
\t\t\t\t\t\t\t&lt;/a&gt;
\t\t\t\t\t\t\t
\t\t\t\t\t\t\t\">
\t\t\t\t\t\t\t <td class=\"text-center\"><a href=\"";
                // line 73
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_aid_item", array("id" => $this->getAttribute($context["aid"], "id", array()))), "html", null, true);
                echo "\" ";
                if (twig_test_empty($this->getAttribute($context["aid"], "enabled", array()))) {
                    echo " class=\"disabled\" ";
                }
                echo "><strong>";
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
                echo "</strong></a></td>
\t\t\t\t\t\t\t <td>";
                // line 74
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "password", array()), "html", null, true);
                echo "</td>
\t\t\t\t\t\t\t <td scope=\"row\"><a class=\"caption\" href=\"";
                // line 75
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_aid_item", array("id" => $this->getAttribute($context["aid"], "id", array()))), "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "caption", array()), "html", null, true);
                echo "</a></td>
\t\t\t\t\t\t\t</tr>
\t\t\t\t\t\t   ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['aid'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 78
            echo "\t\t\t\t\t\t";
        } else {
            // line 79
            echo "\t\t\t\t\t\t\t<tr><td></td><td colspan=\"2\">No Access Identifiers</td></tr>
\t\t\t\t\t\t";
        }
        // line 81
        echo "\t\t\t\t\t </thead>
\t\t\t\t\t <tbody>
\t
\t\t\t\t\t   <tr>
\t\t\t\t\t\t <td><a href=\"#\"><i class=\"pe-7s-more\"></i></a></td>
\t\t\t\t\t\t <td colspan=2><a href=\"#\" id=\"overlay-assignments\"><strong>";
        // line 86
        echo $this->env->getExtension('translator')->getTranslator()->trans("Assign Access Identifiers", array(), "messages");
        echo "</strong></a></td>
\t\t\t\t\t   </tr>
\t\t\t\t\t  
\t\t\t\t\t\t  
\t\t\t\t\t </tbody>
\t\t\t\t   </table>
\t\t\t\t</div>
\t\t\t\t<div class=\"margin-top-40\">
\t\t\t\t<h2>";
        // line 94
        echo $this->env->getExtension('translator')->getTranslator()->trans("I/O Devices", array(), "messages");
        echo "</h2>
\t\t\t\t<table class=\"locationtable table table-hover\">
\t\t\t\t <thead>
\t\t\t\t\t<tr>
\t\t\t\t\t  <th style=\"width: 45px; text-align: center;\">No</th>
\t\t\t\t\t  <th>Name</th>
\t\t\t\t\t  <th style=\"width: 220px;\">Status</th>
\t\t\t\t\t</tr>
\t\t\t\t </thead>
\t\t\t\t <tbody>
\t\t\t\t ";
        // line 104
        if (twig_length_filter($this->env, (isset($context["iodevices"]) ? $context["iodevices"] : null))) {
            // line 105
            echo "\t\t\t\t\t ";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["iodevices"]) ? $context["iodevices"] : null));
            $context['loop'] = array(
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            );
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["iodevice"]) {
                echo " 
\t\t\t\t\t 
\t\t\t\t\t <tr class=\"supla-tooltip\" 
\t\t\t\t\t  title=\"
\t\t\t\t\t\t \t&lt;a class&#61;&quot;details device ";
                // line 109
                if (($this->getAttribute($context["iodevice"], "enabled", array()) == true)) {
                    echo "enabled";
                } else {
                    echo "disabled";
                }
                echo "&quot; href&#61;&quot;";
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_item", array("id" => $this->getAttribute($context["iodevice"], "id", array()))), "html", null, true);
                echo "&quot;&gt;
\t\t\t\t\t\t\t&lt;span class&#61;&quot;id&quot;&gt;";
                // line 110
                echo twig_escape_filter($this->env, $this->getAttribute($context["iodevice"], "name", array()), "html", null, true);
                echo "  &lt;/span&gt; &lt;br/ &gt;
\t\t\t\t\t\t\t&lt;span class&#61;&quot;guid&quot;&gt;GUID: ";
                // line 111
                echo twig_escape_filter($this->env, $this->getAttribute($context["iodevice"], "guidstring", array()), "html", null, true);
                echo "&lt;/span&gt;
\t\t\t\t\t\t\t&lt;br/ &gt;
\t\t\t\t\t\t\t&lt;div class&#61;&quot;details-wrapper&quot;&gt; Software &lt;strong&gt;";
                // line 113
                echo twig_escape_filter($this->env, $this->getAttribute($context["iodevice"], "softwareversion", array()), "html", null, true);
                echo "&lt;/strong&gt;&lt;br/ &gt;
\t\t\t\t\t\t\tLast connected &lt;strong&gt;";
                // line 114
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($context["iodevice"], "lastconnected", array()), "d.m.Y H:m"), "html", null, true);
                echo "&lt;/strong&gt;&lt;/div&gt;
\t\t\t\t\t\t\t
\t\t\t\t\t\t\t&lt;/a&gt;\">
\t\t\t\t\t   <td class=\"text-center\"><strong>";
                // line 117
                echo twig_escape_filter($this->env, ($this->getAttribute($context["loop"], "index0", array()) + 1), "html", null, true);
                echo "</strong></td>
\t\t\t\t\t   <td><a href=\"";
                // line 118
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_item", array("id" => $this->getAttribute($context["iodevice"], "id", array()))), "html", null, true);
                echo "\" class=\"caption\">";
                echo twig_escape_filter($this->env, $this->getAttribute($context["iodevice"], "name", array()), "html", null, true);
                echo "</a></td>
\t\t\t\t\t   <td><div class=\"iodev_connection_state\" data-id=\"";
                // line 119
                echo twig_escape_filter($this->env, $this->getAttribute($context["iodevice"], "id", array()), "html", null, true);
                echo "\"><span class=\"unknown\">unknown</span></div><span class=\"";
                if (($this->getAttribute($context["iodevice"], "enabled", array()) == true)) {
                    echo "enabled\">";
                    echo $this->env->getExtension('translator')->getTranslator()->trans("Enabled", array(), "messages");
                } else {
                    echo "disabled\">";
                    echo $this->env->getExtension('translator')->getTranslator()->trans("Disabled", array(), "messages");
                }
                echo "</span></td>
\t\t\t\t\t </tr>
\t\t\t\t\t";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['length'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['iodevice'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 122
            echo "\t\t\t\t";
        } else {
            // line 123
            echo "\t\t\t\t<tr><td></td><td colspan=\"2\">No Devices</td></tr>
\t\t\t\t";
        }
        // line 125
        echo "

\t\t\t\t </tbody>
\t\t\t   </table>
\t\t\t  
\t\t\t</div>
\t\t</div>



<div class=\"overlay-delete overlay-data\">
\t<div class=\"dialog\">
\t\t<h1>Are You sure?</h1>
\t\t<p>Please confirm removal of Location ID <strong>";
        // line 138
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["location"]) ? $context["location"] : null), "id", array()), "html", null, true);
        echo "</strong>. You will be no longer able to connect to devices in this Location.</p>
\t\t<div class=\"controls\">
\t\t\t<a href=\"#\" class=\"overlay-delete-close cancel\">CANCEL</a>
\t\t\t<a href=\"";
        // line 141
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_loc_item_delete", array("id" => $this->getAttribute((isset($context["location"]) ? $context["location"] : null), "id", array()))), "html", null, true);
        echo "\" class=\"save\"><i class=\"pe-7s-check\"></i></a></div>
\t\t</div>
\t</form>
</div>


<div class=\"overlay overlay-data overlay-assignments\">
\t<div class=\"overlay-title\"><h1>Assign Access Identifiers</h1>
\t  <p>Choose which Access Identifiers will be assigned with Location ID <strong>";
        // line 149
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["location"]) ? $context["location"] : null), "id", array()), "html", null, true);
        echo "</strong></p></div>
      <div class=\"overlay-list\">
            <div class=\"assign-list\">
            
\t\t\t</div>
\t</div>
</div>
\t
<script src=\"";
        // line 157
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/details.js"), "html", null, true);
        echo "\"></script> 
";
    }

    public function getTemplateName()
    {
        return "AppBundle:Location:locdetails.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  388 => 157,  377 => 149,  366 => 141,  360 => 138,  345 => 125,  341 => 123,  338 => 122,  313 => 119,  307 => 118,  303 => 117,  297 => 114,  293 => 113,  288 => 111,  284 => 110,  274 => 109,  251 => 105,  249 => 104,  236 => 94,  225 => 86,  218 => 81,  214 => 79,  211 => 78,  200 => 75,  196 => 74,  186 => 73,  175 => 69,  168 => 65,  163 => 63,  158 => 61,  148 => 60,  142 => 58,  137 => 57,  135 => 56,  124 => 48,  106 => 39,  96 => 38,  85 => 30,  81 => 29,  74 => 27,  67 => 23,  56 => 15,  49 => 13,  42 => 9,  35 => 5,  26 => 2,  20 => 1,);
    }
}
