<?php

/* AppBundle:Form/ChannelFunctions:opensensor.html.twig */
class __TwigTemplate_0f68986da721f1092cf4bbf4768a85a3b4ef1d5b4a98bb6904912c404b79be16 extends Twig_Template
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
        echo "<div id=\"channel_function_params\">

    <div class=\"cfp_channel\">
      <span>";
        // line 4
        echo $this->env->getExtension('translator')->getTranslator()->trans("Sensor for channel", array(), "messages");
        echo "</span>
    
      <div class=\"dropdown\" id=\"subchannel_select\" data-param_id=\"param1\">
        <button class=\"btn btn-default dropdown-toggle\" type=\"button\" id=\"dropdownMenuChannels\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"true\">
          <span id=\"subchannel_name\">";
        // line 8
        echo twig_escape_filter($this->env, (isset($context["subchannel_selected"]) ? $context["subchannel_selected"] : $this->getContext($context, "subchannel_selected")), "html", null, true);
        echo "</span>
        <span class=\"caret\"></span>
        </button>
        <ul class=\"dropdown-menu\" aria-labelledby=\"dropdownMenuChannels\">
           <li><a href=\"#\" data-id=\"0\">";
        // line 12
        echo $this->env->getExtension('translator')->getTranslator()->trans("None", array(), "messages");
        echo "</a></li>
           ";
        // line 13
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["channels"]) ? $context["channels"] : $this->getContext($context, "channels")));
        foreach ($context['_seq'] as $context["_key"] => $context["channel"]) {
            // line 14
            echo "           <li><a href=\"#\" data-id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "id", array()), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "name", array()), "html", null, true);
            echo "</a></li>
           ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['channel'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 16
        echo "        </ul>
     </div>
  </div>
</div>";
    }

    public function getTemplateName()
    {
        return "AppBundle:Form/ChannelFunctions:opensensor.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  57 => 16,  46 => 14,  42 => 13,  38 => 12,  31 => 8,  24 => 4,  19 => 1,);
    }
}
