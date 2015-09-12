<?php

/* AppBundle:Form/ChannelFunctions:gateway.html.twig */
class __TwigTemplate_2d628511b5c0d7ce66444c1f4bd7409bbac5c21ed43f8257bc8c5b38f39a6c8e extends Twig_Template
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
        echo "<div id=\"channel_function_params\" data-default-param1=\"";
        echo twig_escape_filter($this->env, (isset($context["default_time_val"]) ? $context["default_time_val"] : $this->getContext($context, "default_time_val")), "html", null, true);
        echo "\">
    <div class=\"cfp_relay_time\" id=\"cfp_relay_time\" data-paramname=\"param1\">
       <span>";
        // line 3
        echo $this->env->getExtension('translator')->getTranslator()->trans("Time of relay", array(), "messages");
        echo "</span>
       <div class=\"btn-group\" role=\"group\" aria-label=\"...\">
         ";
        // line 5
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["timesel"]) ? $context["timesel"] : $this->getContext($context, "timesel")));
        foreach ($context['_seq'] as $context["_key"] => $context["ts"]) {
            // line 6
            echo "           <button type=\"button\" id=\"relay_time_select\" class=\"btn btn-";
            if (((((isset($context["cinstance"]) ? $context["cinstance"] : $this->getContext($context, "cinstance")) == true) && ($this->getAttribute((isset($context["channel"]) ? $context["channel"] : $this->getContext($context, "channel")), "param1", array()) == $this->getAttribute($context["ts"], "val", array()))) || (((isset($context["cinstance"]) ? $context["cinstance"] : $this->getContext($context, "cinstance")) == false) && ((isset($context["default_time_val"]) ? $context["default_time_val"] : $this->getContext($context, "default_time_val")) == $this->getAttribute($context["ts"], "val", array()))))) {
                echo "success";
            } else {
                echo "default";
            }
            echo " btn-sm\" data-val=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["ts"], "val", array()), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["ts"], "name", array()), "html", null, true);
            echo "</button>
         ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['ts'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 7
        echo "  
       </div>
       <span>";
        // line 9
        echo $this->env->getExtension('translator')->getTranslator()->trans("sec.", array(), "messages");
        echo "</span>
    </div>

    <div class=\"cfp_channel\">
      <span>";
        // line 13
        echo $this->env->getExtension('translator')->getTranslator()->trans("Open sensor", array(), "messages");
        echo "</span>
    
      <div class=\"dropdown\" id=\"subchannel_select\" data-param_id=\"param2\">
        <button class=\"btn btn-default dropdown-toggle\" type=\"button\" id=\"dropdownMenuChannels\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"true\">
          <span id=\"subchannel_name\">";
        // line 17
        echo twig_escape_filter($this->env, (isset($context["subchannel_selected"]) ? $context["subchannel_selected"] : $this->getContext($context, "subchannel_selected")), "html", null, true);
        echo "</span>
        <span class=\"caret\"></span>
        </button>
        <ul class=\"dropdown-menu\" aria-labelledby=\"dropdownMenuChannels\">
           <li><a href=\"#\" data-id=\"0\">";
        // line 21
        echo $this->env->getExtension('translator')->getTranslator()->trans("None", array(), "messages");
        echo "</a></li>
           ";
        // line 22
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["channels"]) ? $context["channels"] : $this->getContext($context, "channels")));
        foreach ($context['_seq'] as $context["_key"] => $context["sensor"]) {
            // line 23
            echo "           <li><a href=\"#\" data-id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["sensor"], "id", array()), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["sensor"], "name", array()), "html", null, true);
            echo "</a></li>
           ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['sensor'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 25
        echo "        </ul>
     </div>
  </div>
</div>";
    }

    public function getTemplateName()
    {
        return "AppBundle:Form/ChannelFunctions:gateway.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  95 => 25,  84 => 23,  80 => 22,  76 => 21,  69 => 17,  62 => 13,  55 => 9,  51 => 7,  34 => 6,  30 => 5,  25 => 3,  19 => 1,);
    }
}
