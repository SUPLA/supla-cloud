<?php

/* AppBundle:IODevice:channeledit.html.twig */
class __TwigTemplate_11064952ed0fbd979c17dbdde8877a08844831c5bb24e527766af3ada2e79f3d extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:IODevice:channeledit.html.twig", 1);
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
        echo "<div class=\"container\">
    <h1 class=\"title\">";
        // line 5
        echo $this->env->getExtension('translator')->getTranslator()->trans("Channel", array(), "messages");
        echo "</h1>

    <h5>";
        // line 7
        echo $this->env->getExtension('translator')->getTranslator()->trans("Channel number", array(), "messages");
        echo ": ";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["channel"]) ? $context["channel"] : null), "channelnumber", array()), "html", null, true);
        echo "</h5>
    <h5>";
        // line 8
        echo $this->env->getExtension('translator')->getTranslator()->trans("Device GUID", array(), "messages");
        echo ": <a href=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_item", array("id" => $this->getAttribute($this->getAttribute((isset($context["channel"]) ? $context["channel"] : null), "iodevice", array()), "id", array()))), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["channel"]) ? $context["channel"] : null), "iodevice", array()), "guidstring", array()), "html", null, true);
        echo "</a></h5>
    <h5>";
        // line 9
        echo $this->env->getExtension('translator')->getTranslator()->trans("Device name", array(), "messages");
        echo ": <a href=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_item", array("id" => $this->getAttribute($this->getAttribute((isset($context["channel"]) ? $context["channel"] : null), "iodevice", array()), "id", array()))), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["channel"]) ? $context["channel"] : null), "iodevice", array()), "name", array()), "html", null, true);
        echo "</a></h5>
    <h5>";
        // line 10
        echo $this->env->getExtension('translator')->getTranslator()->trans("Type", array(), "messages");
        echo ": ";
        echo twig_escape_filter($this->env, (isset($context["channel_type"]) ? $context["channel_type"] : null), "html", null, true);
        echo "</h5>
    ";
        // line 11
        if (((isset($context["show_sensorstate"]) ? $context["show_sensorstate"] : null) == true)) {
            // line 12
            echo "    <div class=\"sensor_state\" data-id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["channel"]) ? $context["channel"] : null), "id", array()), "html", null, true);
            echo "\" id=\"sensor_state\">";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Current sensor state", array(), "messages");
            echo ": <div id=\"sensor_state_value\"></div></div>
    ";
        }
        // line 14
        echo "    ";
        if (((isset($context["show_temperature"]) ? $context["show_temperature"] : null) == true)) {
            // line 15
            echo "    <div class=\"thermometer_state\" data-id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["channel"]) ? $context["channel"] : null), "id", array()), "html", null, true);
            echo "\" id=\"thermometer_state\">";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Current temperature", array(), "messages");
            echo ": <div id=\"temperature_value\"></div></div>
    ";
        }
        // line 16
        echo " 
    ";
        // line 17
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : null), 'form_start');
        echo "
    ";
        // line 18
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'widget');
        echo "
    ";
        // line 19
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : null), 'form_end');
        echo "
    
</div>    
";
    }

    public function getTemplateName()
    {
        return "AppBundle:IODevice:channeledit.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  99 => 19,  95 => 18,  91 => 17,  88 => 16,  80 => 15,  77 => 14,  69 => 12,  67 => 11,  61 => 10,  53 => 9,  45 => 8,  39 => 7,  34 => 5,  31 => 4,  28 => 3,  11 => 1,);
    }
}
