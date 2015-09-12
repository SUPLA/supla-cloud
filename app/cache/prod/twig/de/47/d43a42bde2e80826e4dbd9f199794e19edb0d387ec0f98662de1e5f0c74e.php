<?php

/* AppBundle:IODevice:list.html.twig */
class __TwigTemplate_de47d43a42bde2e80826e4dbd9f199794e19edb0d387ec0f98662de1e5f0c74e extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:IODevice:list.html.twig", 1);
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
    <h1 class=\"title\">";
        // line 4
        echo $this->env->getExtension('translator')->getTranslator()->trans("I/O Devices", array(), "messages");
        echo "<a href=\"#0\" class=\"cd-btn\"><i class=\"pe-7s-help1\"></i></a>
\t\t<div class=\"pull-right filter-container\">
\t\t\t<div id=\"filters\" class=\"btn-group\">
\t\t\t  <button class=\"btn btn-default active\" data-filter=\"\">All</button>
\t\t\t  <button class=\"btn btn-default\" data-filter=\".disabled\">Disabled</button>
\t\t\t  <button class=\"btn btn-default\" data-filter=\".enabled\">Enabled</button>
\t\t\t</div>
\t\t\t
\t\t\t<div id=\"filtersconnection\" class=\"btn-group\">
\t\t\t  <button class=\"btn btn-default active\" data-filter=\"\">All</button>
\t\t\t  <button class=\"btn btn-default\" data-filter=\".connected\">Connected</button>
\t\t\t  <button class=\"btn btn-default\" data-filter=\".disconnected\">Disconnected</button>
\t\t\t</div>
\t\t\t
\t\t\t<input type=\"text\" id=\"quicksearch\" class=\"pull-right\" placeholder=\"Search\" />
\t\t</div>
\t
\t</h1>
</div>

<div class=\"devices\">
";
        // line 25
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["iodevices"]) ? $context["iodevices"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["dev"]) {
            // line 26
            echo "  \t<a class=\"device nav-link ";
            if ($this->getAttribute($context["dev"], "enabled", array())) {
                echo "enabled";
            } else {
                echo "disabled";
            }
            echo " \" id=\"deviceitem\" data-id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "id", array()), "html", null, true);
            echo "\" href=\"";
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_item", array("id" => $this->getAttribute($context["dev"], "id", array()))), "html", null, true);
            echo "\">
    <h3 class=\"name\">";
            // line 27
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "name", array()), "html", null, true);
            echo "</h3>
    <span class=\"guid\">";
            // line 28
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "guid", array()), "html", null, true);
            echo "</span><br />
\t<span class=\"software\">SoftVer <strong>";
            // line 29
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "softwareversion", array()), "html", null, true);
            echo "</strong></span><br />
\t<span class=\"location\">Location <strong>ID";
            // line 30
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["dev"], "location", array()), "id", array()), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["dev"], "location", array()), "caption", array()), "html", null, true);
            echo "</strong></span><br />
\t";
            // line 31
            if ((($this->getAttribute($context["dev"], "comment", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($context["dev"], "comment", array()))) : (""))) {
                echo "<span class=\"comment\">Comment <br />";
                echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "comment", array()), "html", null, true);
                echo "</span><br />";
            }
            // line 32
            echo "\t<div class=\"iodev_connection_state status\" data-id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "id", array()), "html", null, true);
            echo "\"><span class=\"unknown\">unknown</span></div>
  </a>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['dev'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 35
        if (($this->getAttribute((isset($context["iodevices"]) ? $context["iodevices"] : null), "count", array()) < 3)) {
            // line 36
            echo "<a class=\"device more\" href=\"http://supla.org\" target=\"_blank\"><i class=\"pe-7s-light\"></i><br /><span class=\"name\">Lights</span><p>With supla You can controll lightening at Your home</p></a>
<a class=\"device more\" href=\"http://supla.org\" target=\"_blank\"><img src=";
            // line 37
            echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/img/thermometer.svg"), "html", null, true);
            echo "></img><br /><span class=\"name\">Temperature</span><p>...You can controll temperature</p></a>
<a class=\"device more\" href=\"http://supla.org\" target=\"_blank\"><img src=";
            // line 38
            echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/img/gate.svg"), "html", null, true);
            echo "></img><br /><span class=\"name\">Doors And Gates</span><p>...and even open gates or doors</p></a>
<a class=\"device more\" href=\"http://supla.org\" target=\"_blank\"><img src=";
            // line 39
            echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/img/window-rollers.svg"), "html", null, true);
            echo "></img><br /><span class=\"name\">Window Rollers</span><p>...raised and lowered blinds</p></a>
<a class=\"device more\" href=\"http://supla.org\" target=\"_blank\"><i class=\"pe-7s-radio\"></i><br /><span class=\"name\">Home Appliances</span><p>...or any home appliance</p></a>
<a class=\"device more\" href=\"http://supla.org\" target=\"_blank\"><i class=\"pe-7s-smile\"></i><br /><span class=\"name\">And More</span><p>You can do all these and many more things with Your phone, tablet or web browser</p></a>
<a class=\"device more\" href=\"http://supla.org\" target=\"_blank\"><i class=\"pe-7s-plane\"></i><br /><span class=\"name\">From Anywhere</span><p>so don't worry when You next time forget turn the lights off and get back to Your warm home in cold winter <strong>;)</strong></p></a>
";
        }
        // line 44
        echo "</div>


";
        // line 47
        $this->loadTemplate("AppBundle::Help/devlisthelp.html.twig", "AppBundle:IODevice:list.html.twig", 47)->display($context);
        // line 48
        echo "

";
    }

    public function getTemplateName()
    {
        return "AppBundle:IODevice:list.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  137 => 48,  135 => 47,  130 => 44,  122 => 39,  118 => 38,  114 => 37,  111 => 36,  109 => 35,  99 => 32,  93 => 31,  87 => 30,  83 => 29,  79 => 28,  75 => 27,  62 => 26,  58 => 25,  34 => 4,  31 => 3,  28 => 2,  11 => 1,);
    }
}
