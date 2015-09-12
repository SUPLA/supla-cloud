<?php

/* AppBundle:Form:fields.html.twig */
class __TwigTemplate_3f7b85fd7f9c8959abf04422a321c3b198c3318d16c87ad5948d32f680414e63 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'channelfunction_widget' => array($this, 'block_channelfunction_widget'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 2
        $this->displayBlock('channelfunction_widget', $context, $blocks);
    }

    public function block_channelfunction_widget($context, array $blocks = array())
    {
        // line 3
        echo "    ";
        ob_start();
        // line 4
        echo "    <div class=\"channel_function_select\">
        <div class=\"panel panel-default\">
           <div class=\"panel-heading\">
               <div class=\"dropdown\" id=\"channel_function_select\" data-channel_id=\"";
        // line 7
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["channel"]) ? $context["channel"] : $this->getContext($context, "channel")), "id", array()), "html", null, true);
        echo "\" data-prefix=\"";
        echo twig_escape_filter($this->env, (isset($context["id"]) ? $context["id"] : $this->getContext($context, "id")), "html", null, true);
        echo "_\">
               
                   <button class=\"btn btn-default dropdown-toggle\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"true\">
                     <span id=\"function_name\">";
        // line 10
        echo twig_escape_filter($this->env, (isset($context["selected"]) ? $context["selected"] : $this->getContext($context, "selected")), "html", null, true);
        echo "</span>
                     <span class=\"caret\"></span>
                   </button>
               
                   <ul class=\"dropdown-menu\" aria-labelledby=\"dropdownMenu1\">
                      ";
        // line 15
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["functions"]) ? $context["functions"] : $this->getContext($context, "functions")));
        foreach ($context['_seq'] as $context["_key"] => $context["fnc"]) {
            // line 16
            echo "                      <li><a href=\"#\" data-id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["fnc"], "id", array()), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["fnc"], "name", array()), "html", null, true);
            echo "</a></li>
                      ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['fnc'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 18
        echo "                   </ul>
               </div>
          </div>
          <div class=\"panel-body\">
          ";
        // line 22
        echo (isset($context["function_params"]) ? $context["function_params"] : $this->getContext($context, "function_params"));
        echo "
          </div>
        </div>
        
        ";
        // line 26
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")));
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 27
            echo "        ";
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($context["child"], 'widget');
            echo "
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 29
        echo "    </div>
            
    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }

    public function getTemplateName()
    {
        return "AppBundle:Form:fields.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  91 => 29,  82 => 27,  78 => 26,  71 => 22,  65 => 18,  54 => 16,  50 => 15,  42 => 10,  34 => 7,  29 => 4,  26 => 3,  20 => 2,);
    }
}
