<?php

/* AppBundle:IODevice:devtable.html.twig */
class __TwigTemplate_0a311c4641bcfd13f0ee9ab339d87fbf634f6f7a2cf86a3f29ce3474c7e61e0f extends Twig_Template
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
        echo "   <table class=\"iodevicetable table table-hover\">
     <thead>
        <tr>
          <th>";
        // line 4
        echo $this->env->getExtension('translator')->getTranslator()->trans("No.", array(), "messages");
        echo "</th>
          <th>";
        // line 5
        echo $this->env->getExtension('translator')->getTranslator()->trans("GUID", array(), "messages");
        echo "</th>
          <th>";
        // line 6
        echo $this->env->getExtension('translator')->getTranslator()->trans("Name", array(), "messages");
        echo "</th>
          ";
        // line 7
        if (array_key_exists("show_location", $context)) {
            // line 8
            echo "          <th>";
            echo $this->env->getExtension('translator')->getTranslator()->trans("Location", array(), "messages");
            echo "</th>
          ";
        }
        // line 10
        echo "          <th>";
        echo $this->env->getExtension('translator')->getTranslator()->trans("Enabled", array(), "messages");
        echo "</th>
          <th>";
        // line 11
        echo $this->env->getExtension('translator')->getTranslator()->trans("SoftVer", array(), "messages");
        echo "</th>
          <th>";
        // line 12
        echo $this->env->getExtension('translator')->getTranslator()->trans("Connected", array(), "messages");
        echo "</th>
          <th>";
        // line 13
        echo $this->env->getExtension('translator')->getTranslator()->trans("Comment", array(), "messages");
        echo "</th>
        </tr>
     </thread>
     <tbody>
     
     ";
        // line 18
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["iodevices"]) ? $context["iodevices"] : $this->getContext($context, "iodevices")));
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
        foreach ($context['_seq'] as $context["_key"] => $context["dev"]) {
            // line 19
            echo "       <tr>
         <th>";
            // line 20
            echo twig_escape_filter($this->env, ($this->getAttribute($context["loop"], "index0", array()) + 1), "html", null, true);
            echo ".</th>
         <td scope=\"row\"><a href=\"";
            // line 21
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_item", array("id" => $this->getAttribute($context["dev"], "id", array()))), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "guidstring", array()), "html", null, true);
            echo "</a></td>
         <td><a href=\"";
            // line 22
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_iodev_item", array("id" => $this->getAttribute($context["dev"], "id", array()))), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "name", array()), "html", null, true);
            echo "</a></td>
         ";
            // line 23
            if (array_key_exists("show_location", $context)) {
                // line 24
                echo "          <td>";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["dev"], "location", array()), "caption", array()), "html", null, true);
                echo "</td>
         ";
            }
            // line 26
            echo "         <td><span class=\"glyphicon glyphicon-off\" style=\"color:";
            if ($this->getAttribute($context["dev"], "enabled", array())) {
                echo "green";
            } else {
                echo "red";
            }
            echo "\"></span></td>
         <td>";
            // line 27
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "softwareversion", array()), "html", null, true);
            echo "</td>
         <td><div class=\"iodev_connection_state\" data-id=\"";
            // line 28
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "id", array()), "html", null, true);
            echo "\"><span class=\"unknown\">unknown</span></div></td>
         <td>";
            // line 29
            echo twig_escape_filter($this->env, $this->getAttribute($context["dev"], "comment", array()), "html", null, true);
            echo "</td>
       </tr>
      
     ";
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
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['dev'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 33
        echo "     
     </tbody>

   </table>";
    }

    public function getTemplateName()
    {
        return "AppBundle:IODevice:devtable.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  144 => 33,  126 => 29,  122 => 28,  118 => 27,  109 => 26,  103 => 24,  101 => 23,  95 => 22,  89 => 21,  85 => 20,  82 => 19,  65 => 18,  57 => 13,  53 => 12,  49 => 11,  44 => 10,  38 => 8,  36 => 7,  32 => 6,  28 => 5,  24 => 4,  19 => 1,);
    }
}
