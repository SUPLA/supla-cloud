<?php

/* WebProfilerBundle:Collector:ajax.html.twig */
class __TwigTemplate_14509dbcaca064ce55750003599289395e7959cdbdb14294c819edbbb81bebca extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "WebProfilerBundle:Collector:ajax.html.twig", 1);
        $this->blocks = array(
            'toolbar' => array($this, 'block_toolbar'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@WebProfiler/Profiler/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_toolbar($context, array $blocks = array())
    {
        // line 4
        echo "    ";
        $context["icon"] = ('' === $tmp = "        <span>
            <img width=\"13\" height=\"28\" alt=\"AJAX requests\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAcCAYAAAB75n/uAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3gUJDAkZ7bv96AAAAP1JREFUSMftlr0NgzAQhZ8RovIQeIdUkdJFWcINFAyQJl0UpU2TAShwwxZ0kVKxAwxBRXOpQEBsfkMRiets2e/T+ex3ZkSENcPCyvH/ALs5KMsXPfZnJJz3bnKDCEoKVo2z2KM7bq252RnoxP0wRx768OKMFgFM4lXoINavxE0Qu0+0KI54vi84OE7rbE3iLQgiUlIwe2oNYm9HYc4H11WQyQCpUiYNt06X8faSN8AGAOyvl9mwas4TXE8JABAAFG6AVEk2KQOpUhYF7iizmypem52QikUwG1ivkw40p7oGQiptJmNtelSRu5Cl4tp+UB1Xt8fOEQcAtn28huIDUf6Q+fofUk0AAAAASUVORK5CYII=\">
            <span class=\"sf-toolbar-ajax-requests\">0</span>
        </span>
    ") ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 10
        echo "    ";
        $context["text"] = ('' === $tmp = "        <div class=\"sf-toolbar-info-piece\">
            <b>AJAX requests</b>
            <span class=\"sf-toolbar-ajax-info\"></span>
        </div>
        <div class=\"sf-toolbar-info-piece\">
            <table class=\"sf-toolbar-ajax-requests\">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>URL</th>
                        <th>Time</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody class=\"sf-toolbar-ajax-request-list\"></tbody>
            </table>
        </div>
    ") ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 29
        echo "    ";
        $this->loadTemplate("@WebProfiler/Profiler/toolbar_item.html.twig", "WebProfilerBundle:Collector:ajax.html.twig", 29)->display(array_merge($context, array("link" => false)));
    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Collector:ajax.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  58 => 29,  38 => 10,  31 => 4,  28 => 3,  11 => 1,);
    }
}
