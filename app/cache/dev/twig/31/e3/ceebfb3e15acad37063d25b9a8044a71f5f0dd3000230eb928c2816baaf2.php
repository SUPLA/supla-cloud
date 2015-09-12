<?php

/* AppBundle:Account:aidlist.html.twig */
class __TwigTemplate_31e3ceebfb3e15acad37063d25b9a8044a71f5f0dd3000230eb928c2816baaf2 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:Account:aidlist.html.twig", 1);
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
        echo "    <h1 class=\"title\">";
        echo $this->env->getExtension('translator')->getTranslator()->trans("Access Identifiers", array(), "messages");
        echo " <input id=\"livefilter-input\" class=\"pull-right\" type=\"text\" placeholder=\"Search\"></h1>
\t<button class=\"example-show\" data-direction=\"left\">left</button>
               
</div>
       <div class=\"scroll_list_wrapper access_id_list\">
            <ul class=\"scroll_list\">
\t\t\t  <a class=\"item new\" href=\"";
        // line 10
        echo $this->env->getExtension('routing')->getPath("_aid_new");
        echo "\"><i class=\"pe-7s-plus\"></i><br />";
        echo $this->env->getExtension('translator')->getTranslator()->trans("Create New Access Identifier", array(), "messages");
        echo "</a>
\t\t\t\t\t\t\t\t\t\t\t\t
\t\t\t   ";
        // line 12
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable(twig_reverse_filter($this->env, (isset($context["accessids"]) ? $context["accessids"] : $this->getContext($context, "accessids"))));
        foreach ($context['_seq'] as $context["_key"] => $context["aid"]) {
            // line 13
            echo "\t\t\t   <a data-id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
            echo "\" class=\"item ";
            if ($this->getAttribute($context["aid"], "enabled", array())) {
                echo "enabled";
            } else {
                echo "disabled";
            }
            echo "\">
\t\t\t   <span class=\"aid\">ID <strong>";
            // line 14
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
            echo "</strong></span> <br />
\t\t\t   <span class=\"acaption\">Caption<br />
\t\t\t   <strong>";
            // line 16
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "caption", array()), "html", null, true);
            echo "</strong><br />
\t\t\t   <span class=\"apassword\">Password<br />
\t\t\t   <strong>";
            // line 18
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "password", array()), "html", null, true);
            echo "</strong><br />
\t\t\t   ";
            // line 19
            if ($this->getAttribute($context["aid"], "enabled", array())) {
                echo "<span class=\"status\">ENABLED</span>";
            } else {
                echo "<span class=\"status disable\">DISABLED</span>";
            }
            // line 20
            echo "\t\t\t   </a>
\t\t\t   ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['aid'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 22
        echo "             </ul>
             </div>
\t\t<div class=\"container\">
        <div class=\"details\" id=\"details\">           
     </div>
</div>
           
";
    }

    public function getTemplateName()
    {
        return "AppBundle:Account:aidlist.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  90 => 22,  83 => 20,  77 => 19,  73 => 18,  68 => 16,  63 => 14,  52 => 13,  48 => 12,  41 => 10,  31 => 4,  28 => 3,  11 => 1,);
    }
}
