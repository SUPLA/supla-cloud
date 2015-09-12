<?php

/* AppBundle:Account:view.html.twig */
class __TwigTemplate_9a2b2a978af26090eff0881a94456b707c02df605c96e4bdf3674500801638d9 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:Account:view.html.twig", 1);
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
        echo "<div class=\"container margin-top-30 \">
    <h1 class=\"title\">";
        // line 5
        echo $this->env->getExtension('translator')->getTranslator()->trans("Account", array(), "messages");
        echo "</h1>
<ul class=\"user list-unstyled\">
\t<li><strong>";
        // line 7
        echo $this->env->getExtension('translator')->getTranslator()->trans("E-Mail", array(), "messages");
        echo "</strong> ";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : $this->getContext($context, "user")), "email", array()), "html", null, true);
        echo "</li>
\t<li><strong>";
        // line 8
        echo $this->env->getExtension('translator')->getTranslator()->trans("Password", array(), "messages");
        echo "</strong> <a href=\"#\" id=\"overlay-delete\">";
        echo $this->env->getExtension('translator')->getTranslator()->trans("Change", array(), "messages");
        echo "</a></li>
</ul>
<ul class=\"user list-unstyled\">
\t<li><strong>";
        // line 11
        echo $this->env->getExtension('translator')->getTranslator()->trans("Last logged", array(), "messages");
        echo "</strong> ";
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : $this->getContext($context, "user")), "lastlogin", array()), "Y-m-d H:m:s"), "html", null, true);
        echo "</li>
\t<li><strong>";
        // line 12
        echo $this->env->getExtension('translator')->getTranslator()->trans("Last logged IP", array(), "messages");
        echo "</strong> ";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : $this->getContext($context, "user")), "lastipv4", array()), "html", null, true);
        echo "</li>
</ul>
</div>


<div class=\"overlay-delete overlay-data\">
\t<div class=\"dialog\">
\t\t<h1>Change Password</h1>
\t\t<div class=\"password-form\">
\t\t  <div class=\"form-group\">
\t\t\t<input type=\"password\" class=\"form-control\" id=\"old-password\">
\t\t\t<label for=\"old-password\">Current Password</label>
\t\t  </div>
\t\t  <div class=\"form-group\">
\t\t\t<input type=\"password\" class=\"form-control\" id=\"new-password\">
\t\t\t<label for=\"new-password\">Password</label>
\t\t  </div>
\t\t  <div class=\"form-group\">
\t\t\t<input type=\"password\" class=\"form-control\" id=\"exampleInputPassword1\">
\t\t\t<label for=\"exampleInputPassword1\">Repeat Password</label>
\t\t  </div>
\t\t</div>
\t\t<div class=\"controls\">
\t\t\t<a href=\"#\" class=\"overlay-delete-close cancel\">CANCEL</a>
\t\t\t<a href=\"\" id=\"assign_type_save\" class=\"save green\" name=\"_assign_type[save]\"><i class=\"pe-7s-check\"></i></a></div>
\t\t</div>
\t</form>
</div>

<script src=\"";
        // line 41
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("assets/js/details.js"), "html", null, true);
        echo "\"></script>
";
    }

    public function getTemplateName()
    {
        return "AppBundle:Account:view.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  93 => 41,  59 => 12,  53 => 11,  45 => 8,  39 => 7,  34 => 5,  31 => 4,  28 => 3,  11 => 1,);
    }
}
