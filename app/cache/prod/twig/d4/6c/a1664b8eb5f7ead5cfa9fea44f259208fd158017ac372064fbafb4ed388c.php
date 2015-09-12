<?php

/* AppBundle:IODevice:iodevice.html.twig */
class __TwigTemplate_d46ca1664b8eb5f7ead5cfa9fea44f259208fd158017ac372064fbafb4ed388c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:IODevice:iodevice.html.twig", 1);
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
        echo "<div class=\"device-wrapper\" id=\"iodevice-detail\" data-id=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "id", array()), "html", null, true);
        echo "\" data-item-path=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_item", array("id" => $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "id", array()))), "html", null, true);
        echo "\">
\t<div class=\"container\">
\t\t<div class=\"supla-route\">
\t\t\t<div id=\"dot1\" class=\"dot1 ";
        // line 7
        if (($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "enabled", array()) == false)) {
            echo " red ";
        }
        echo "\" data-id=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "id", array()), "html", null, true);
        echo "\">
\t\t\t</div>
\t\t\t<div class=\"dot2 ";
        // line 9
        if (($this->getAttribute($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "location", array()), "enabled", array()) == false)) {
            echo " red ";
        }
        echo "\">
\t\t\t</div>
\t\t\t<div class=\"dot3 ";
        // line 11
        if (((isset($context["aid_enabled"]) ? $context["aid_enabled"] : null) == false)) {
            echo " red ";
        }
        echo "\">
\t\t\t</div>
\t\t</div>
\t\t<div class=\"supla-route-info\">
\t\t\t<div class=\"device\">
\t\t\t\t<h3>Device</h3>
\t\t\t\t<div class=\"device-info\">
\t\t\t\t\t<ul class=\"list-unstyled\">
\t\t\t\t\t\t<li><strong>";
        // line 19
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "name", array()), "html", null, true);
        echo "</strong></li>
\t\t\t\t\t\t<li>";
        // line 20
        echo $this->env->getExtension('translator')->getTranslator()->trans("GUID", array(), "messages");
        echo " <strong>";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "guidstring", array()), "html", null, true);
        echo "</strong></li>
\t\t\t\t\t\t<li>";
        // line 21
        echo $this->env->getExtension('translator')->getTranslator()->trans("Registred", array(), "messages");
        echo " <strong>";
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "regdate", array()), "d.m.Y H:m"), "html", null, true);
        echo "</strong></li>
\t\t\t\t\t\t<li>";
        // line 22
        echo $this->env->getExtension('translator')->getTranslator()->trans("Last connected", array(), "messages");
        echo " <strong>";
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "lastconnected", array()), "d.m.Y H:m"), "html", null, true);
        echo "</strong></li>
\t\t\t\t\t\t<li><div class=\"miniform\">
\t\t\t\t\t\t<textarea id=\"comment_value\" rows=\"2\" cols=\"27\" placeholder=\"";
        // line 24
        echo $this->env->getExtension('translator')->getTranslator()->trans("Comment", array(), "messages");
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "comment", array()), "html", null, true);
        echo "</textarea><button id=\"comment-save\" data-preloader=\"set-comment-pl\" class=\"btn btn-textarea\"><br />&nbsp;&nbsp;&nbsp;SAVE&nbsp;&nbsp;&nbsp;</button>
\t\t\t\t\t\t<div class=\"btn btn-load btn-big\" id=\"set-comment-pl\" style=\"display: none;\"><div id=\"circleG\"><div id=\"circleG_1\" class=\"circleG\"></div><div id=\"circleG_2\" class=\"circleG\"></div><div id=\"circleG_3\" class=\"circleG\"></div></div></div>
\t\t\t\t\t\t</div></li>
\t\t\t\t\t</ul>
\t\t\t\t\t
\t\t\t\t\t<br />
\t\t\t\t<div data-preloader=\"set-enabled-pl\" class=\"btn btn-default btn-enable\" ";
        // line 30
        if (($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "enabled", array()) == false)) {
            echo "style=\"display: none;\"";
        }
        echo "><strong>";
        echo $this->env->getExtension('translator')->getTranslator()->trans("ENABLED", array(), "messages");
        echo "</strong><br >";
        echo $this->env->getExtension('translator')->getTranslator()->trans("CLICK TO DISABLE", array(), "messages");
        echo "</div>
\t\t\t\t<div data-preloader=\"set-enabled-pl\" class=\"btn btn-default btn-disable\" ";
        // line 31
        if (($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "enabled", array()) == true)) {
            echo "style=\"display: none;\"";
        }
        echo "><strong>";
        echo $this->env->getExtension('translator')->getTranslator()->trans("DISABLED", array(), "messages");
        echo "</strong><br >";
        echo $this->env->getExtension('translator')->getTranslator()->trans("CLICK TO ENABLE", array(), "messages");
        echo "</div> 
\t\t\t\t<div class=\"btn btn-load btn-big\" id=\"set-enabled-pl\" style=\"display: none;\"><div id=\"circleG\"><div id=\"circleG_1\" class=\"circleG\"></div><div id=\"circleG_2\" class=\"circleG\"></div><div id=\"circleG_3\" class=\"circleG\"></div></div></div>
\t\t\t\t\t\t
\t\t\t\t\t<div class=\"iodev_connection_state\" data-id=\"";
        // line 34
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "id", array()), "html", null, true);
        echo "\"><span class=\"unknown\">unknown</span></div>
\t\t\t\t\t<a class=\"btn btn-default btn-trash\" href=\"#\" id=\"overlay-delete\">DELETE</a>
\t\t\t\t</div>
\t\t\t</div>
\t\t\t<div class=\"location\">
\t\t\t\t<h3>Location</h3>
\t\t\t\t<div class=\"location-info\">
\t\t\t\t\t<a href=\"";
        // line 41
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_loc_item", array("id" => $this->getAttribute($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "location", array()), "id", array()))), "html", null, true);
        echo "\" class=\"item  ";
        if ($this->getAttribute($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "location", array()), "enabled", array())) {
            echo "enable";
        } else {
            echo "disabled";
        }
        echo "\">
\t\t\t\t\t<span class=\"aid\">ID<strong>";
        // line 42
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "location", array()), "id", array()), "html", null, true);
        echo "</strong></span> <br />
\t\t\t\t\t<span class=\"acaption\">Caption<br />
\t\t\t\t\t<strong><span class=\"caption_value\">";
        // line 44
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "location", array()), "caption", array()), "html", null, true);
        echo "</span></strong><br />
\t\t\t\t\t<span class=\"apassword\">Password<br />
\t\t\t\t\t<strong><span class=\"password_value\">";
        // line 46
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "location", array()), "password", array()), "html", null, true);
        echo "</span></strong><br />
\t\t\t\t\t";
        // line 47
        if ($this->getAttribute($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "location", array()), "enabled", array())) {
            echo "<span class=\"status\">ENABLED</span>";
        } else {
            echo "<span class=\"status disable\">DISABLED</span>";
        }
        // line 48
        echo "\t\t\t\t\t</a>
\t\t\t\t</div>
\t\t\t</div>
\t\t\t<div class=\"accessid\">
\t\t\t\t<h3>Access Identifier</h3>
\t\t\t\t\t<ul class=\"aid\">
\t\t\t\t\t  ";
        // line 54
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "location", array()), "accessIds", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["aid"]) {
            // line 55
            echo "\t\t\t\t\t\t <li><a class=\"supla-tooltip ";
            if (($this->getAttribute($context["aid"], "enabled", array()) == false)) {
                echo "disabled";
            }
            echo "\" title=\"
\t\t\t\t\t\t \t&lt;a class&#61;&quot;details ";
            // line 56
            if (($this->getAttribute($context["aid"], "enabled", array()) == true)) {
                echo "enabled";
            } else {
                echo "disabled";
            }
            echo "&quot; href&#61;&quot;";
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_aid_item", array("id" => $this->getAttribute($context["aid"], "id", array()))), "html", null, true);
            echo "&quot;&gt;
\t\t\t\t\t\t\t&lt;span class&#61;&quot;id&quot;&gt;ID&lt;strong&gt;";
            // line 57
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
            echo "&lt;/strong&gt;  &lt;/span&gt; &lt;br/ &gt;
\t\t\t\t\t\t\t&lt;div class&#61;&quot;details-wrapper&quot;&gt; Caption &lt;br/ &gt;
\t\t\t\t\t\t\t&lt;strong&gt;";
            // line 59
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "caption", array()), "html", null, true);
            echo "&lt;/strong&gt;&lt;br/ &gt;
\t\t\t\t\t\t\tPassword &lt;br/ &gt;
\t\t\t\t\t\t\t&lt;strong&gt;";
            // line 61
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "password", array()), "html", null, true);
            echo "&lt;/strong&gt;&lt;/div&gt;
\t\t\t\t\t\t\t
\t\t\t\t\t\t\t&lt;br/ &gt;
\t\t\t\t\t\t\t
\t\t\t\t\t\t\t&lt;span class&#61;&quot;status&quot;&gt;";
            // line 65
            if (($this->getAttribute($context["aid"], "enabled", array()) == true)) {
                echo "enabled";
            } else {
                echo "disabled";
            }
            echo "&lt;/span&gt;
\t\t\t\t\t\t\t&lt;/a&gt;
\t\t\t\t\t\t\t\" href=\"";
            // line 67
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_aid_item", array("id" => $this->getAttribute($context["aid"], "id", array()))), "html", null, true);
            echo "\">ID<strong>";
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
            echo "</strong> ";
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "caption", array()), "html", null, true);
            echo "</a></li>
\t\t\t\t\t  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['aid'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 69
        echo "\t\t\t\t    </ul>
\t\t\t</div>
\t\t</div>
\t</div>
</div>


<div class=\"container\">
<h1>";
        // line 77
        echo $this->env->getExtension('translator')->getTranslator()->trans("Channels", array(), "messages");
        echo " <input id=\"livefilter-input\" class=\"pull-right\" type=\"text\" placeholder=\"Search\"></h1> 
</div>

   <div class=\"scroll_list_wrapper channel_list\">
\t\t<ul class=\"scroll_list owl-carousel\">
\t\t\t";
        // line 82
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["channels"]) ? $context["channels"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["channel"]) {
            // line 83
            echo "\t\t\t   <a class=\"item\" href=\"";
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_channel_item_edit", array("devid" => $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "id", array()), "id" => $this->getAttribute($context["channel"], "id", array()))), "html", null, true);
            echo "\">
\t\t\t\t <span class=\"no\"><strong>";
            // line 84
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "number", array()), "html", null, true);
            echo "</strong> ";
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "io", array()), "html", null, true);
            echo "</span>
\t\t\t\t <div class=\"details-wrapper\">
\t\t\t\t Type<br />
\t\t\t\t <strong>";
            // line 87
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "type", array()), "html", null, true);
            echo "</strong><br />
\t\t\t\t Function<br />
\t\t\t\t <strong>";
            // line 89
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "function", array()), "html", null, true);
            echo "</strong><br />
\t\t\t\t ";
            // line 90
            if ( !twig_test_empty($this->getAttribute($context["channel"], "caption", array()))) {
                echo " Caption<br />
\t\t\t\t <strong>";
                // line 91
                echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "caption", array()), "html", null, true);
                echo "</strong>";
            }
            echo "</div>
\t\t\t   </a>
\t\t\t ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['channel'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 94
        echo "\t\t</ul>
\t</div>
</div>

<div class=\"overlay-delete overlay-data\">
\t<div class=\"dialog\">
\t\t<h1>Are You sure?</h1>
\t\t<p>Please confirm removal of <strong>";
        // line 101
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "name", array()), "html", null, true);
        echo "</strong>. You will be no longer able to use this device with SUPLA.</p>
\t\t<div class=\"controls\">
\t\t\t<a href=\"#\" class=\"overlay-delete-close cancel\">CANCEL</a>
\t\t\t<a href=\"";
        // line 104
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_item_delete", array("id" => $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : null), "id", array()))), "html", null, true);
        echo "\" class=\"save\"><i class=\"pe-7s-check\"></i></a></div>
\t\t</div>
\t</form>
</div>

     

<script src=\"";
        // line 111
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/details.js"), "html", null, true);
        echo "\"></script>
<script>
    \$(document).ready(function() {
     
      \$(\"#owl-example\").owlCarousel();
     
    });
</script>
";
    }

    public function getTemplateName()
    {
        return "AppBundle:IODevice:iodevice.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  316 => 111,  306 => 104,  300 => 101,  291 => 94,  280 => 91,  276 => 90,  272 => 89,  267 => 87,  259 => 84,  254 => 83,  250 => 82,  242 => 77,  232 => 69,  220 => 67,  211 => 65,  204 => 61,  199 => 59,  194 => 57,  184 => 56,  177 => 55,  173 => 54,  165 => 48,  159 => 47,  155 => 46,  150 => 44,  145 => 42,  135 => 41,  125 => 34,  113 => 31,  103 => 30,  92 => 24,  85 => 22,  79 => 21,  73 => 20,  69 => 19,  56 => 11,  49 => 9,  40 => 7,  31 => 4,  28 => 3,  11 => 1,);
    }
}
