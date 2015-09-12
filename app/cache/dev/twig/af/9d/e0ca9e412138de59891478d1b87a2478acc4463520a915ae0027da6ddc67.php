<?php

/* AppBundle:Dialogs:flashmsg.html.twig */
class __TwigTemplate_af9de0ca9e412138de59891478d1b87a2478acc4463520a915ae0027da6ddc67 extends Twig_Template
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
        echo "           ";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "session", array()), "flashbag", array()), "get", array(0 => (isset($context["msgtype"]) ? $context["msgtype"] : $this->getContext($context, "msgtype"))), "method"));
        foreach ($context['_seq'] as $context["_key"] => $context["flashMessage"]) {
            echo "        
           
               new PNotify({
                    title: '";
            // line 4
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute($context["flashMessage"], "title", array())), "html", null, true);
            echo "',
                    text: '";
            // line 5
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute($context["flashMessage"], "message", array())), "html", null, true);
            echo "',
                    type: '";
            // line 6
            if (((isset($context["msgtype"]) ? $context["msgtype"] : $this->getContext($context, "msgtype")) == "warning")) {
            } else {
                echo twig_escape_filter($this->env, (isset($context["msgtype"]) ? $context["msgtype"] : $this->getContext($context, "msgtype")), "html", null, true);
            }
            echo "'
               });

           ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['flashMessage'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "AppBundle:Dialogs:flashmsg.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  36 => 6,  32 => 5,  28 => 4,  19 => 1,);
    }
}
