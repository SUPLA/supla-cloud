<?php

/* AppBundle:Dialogs:confirm.html.twig */
class __TwigTemplate_62243b92bad4695f2b473e0cec6476db7c4b99e3ecb4cda941b8a65cb90dad9c extends Twig_Template
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
        echo "<!-- Modal Confirm Dialog -->
<div class=\"modal fade\" id=\"";
        // line 2
        echo twig_escape_filter($this->env, (isset($context["dialogId"]) ? $context["dialogId"] : $this->getContext($context, "dialogId")), "html", null, true);
        echo "\" tabindex=\"-1\" role=\"dialog\">
  <div class=\"modal-dialog\" role=\"document\">
    <div class=\"modal-content\">
      <div class=\"modal-body\">
       ";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["message"]) ? $context["message"] : $this->getContext($context, "message")), "html", null, true);
        echo "
      </div>
      <div class=\"modal-footer\">
        <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">";
        // line 9
        echo twig_escape_filter($this->env, (isset($context["cancel"]) ? $context["cancel"] : $this->getContext($context, "cancel")), "html", null, true);
        echo "</button>
        <button name=\"confirmBtn\" data-desturl=\"";
        // line 10
        echo twig_escape_filter($this->env, (isset($context["destUrl"]) ? $context["destUrl"] : $this->getContext($context, "destUrl")), "html", null, true);
        echo "\" data-dialogid=\"";
        echo twig_escape_filter($this->env, (isset($context["dialogId"]) ? $context["dialogId"] : $this->getContext($context, "dialogId")), "html", null, true);
        echo "\" type=\"button\" class=\"btn btn-";
        echo twig_escape_filter($this->env, (isset($context["confirm_btn_type"]) ? $context["confirm_btn_type"] : $this->getContext($context, "confirm_btn_type")), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, (isset($context["confirm"]) ? $context["confirm"] : $this->getContext($context, "confirm")), "html", null, true);
        echo "</button>
      </div>
    </div>
  </div>
</div>";
    }

    public function getTemplateName()
    {
        return "AppBundle:Dialogs:confirm.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  39 => 10,  35 => 9,  29 => 6,  22 => 2,  19 => 1,);
    }
}
