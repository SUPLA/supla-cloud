<?php

/* AppBundle:IODevice:channeltable.html.twig */
class __TwigTemplate_f6a55a03669f9bfb1a627e2a37f7d4d5d22d33b3b15650dea5b30a5feca0fa85 extends Twig_Template
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
        echo "   <table class=\"channeltable table table-hover\">
     <thead>
        <tr>
          <th>";
        // line 4
        echo $this->env->getExtension('translator')->getTranslator()->trans("Number", array(), "messages");
        echo "</th>
          <th>";
        // line 5
        echo $this->env->getExtension('translator')->getTranslator()->trans("I/O", array(), "messages");
        echo "</th>
          <th>";
        // line 6
        echo $this->env->getExtension('translator')->getTranslator()->trans("Type", array(), "messages");
        echo "</th>
          <th>";
        // line 7
        echo $this->env->getExtension('translator')->getTranslator()->trans("Function", array(), "messages");
        echo "</th>
          <th>";
        // line 8
        echo $this->env->getExtension('translator')->getTranslator()->trans("Caption", array(), "messages");
        echo "</th>
        </tr>
     </thread>
     <tbody>
     
     ";
        // line 13
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["channels"]) ? $context["channels"] : $this->getContext($context, "channels")));
        foreach ($context['_seq'] as $context["_key"] => $context["channel"]) {
            // line 14
            echo "       <tr>
         <th scope=\"row\"><a href=\"";
            // line 15
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_channel_item_edit", array("devid" => $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : $this->getContext($context, "iodevice")), "id", array()), "id" => $this->getAttribute($context["channel"], "id", array()))), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "number", array()), "html", null, true);
            echo "</a></th>
         <td><a href=\"";
            // line 16
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_channel_item_edit", array("devid" => $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : $this->getContext($context, "iodevice")), "id", array()), "id" => $this->getAttribute($context["channel"], "id", array()))), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "io", array()), "html", null, true);
            echo "</a></td>
         <td><a href=\"";
            // line 17
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_channel_item_edit", array("devid" => $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : $this->getContext($context, "iodevice")), "id", array()), "id" => $this->getAttribute($context["channel"], "id", array()))), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "type", array()), "html", null, true);
            echo "</a></td>
         <td><a href=\"";
            // line 18
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_channel_item_edit", array("devid" => $this->getAttribute((isset($context["iodevice"]) ? $context["iodevice"] : $this->getContext($context, "iodevice")), "id", array()), "id" => $this->getAttribute($context["channel"], "id", array()))), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "function", array()), "html", null, true);
            echo "</a></td>
         <td>";
            // line 19
            echo twig_escape_filter($this->env, $this->getAttribute($context["channel"], "caption", array()), "html", null, true);
            echo "</td>
       </tr>
      
     ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['channel'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 23
        echo "     
     </tbody>

   </table>";
    }

    public function getTemplateName()
    {
        return "AppBundle:IODevice:channeltable.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  89 => 23,  79 => 19,  73 => 18,  67 => 17,  61 => 16,  55 => 15,  52 => 14,  48 => 13,  40 => 8,  36 => 7,  32 => 6,  28 => 5,  24 => 4,  19 => 1,);
    }
}
