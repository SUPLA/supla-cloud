<?php

/* AppBundle:AccessID:aidlist.html.twig */
class __TwigTemplate_b593a3fd3ef56317e3f541baf1edda358deaf05e1b4b4d7ec6edaa5ea0d74797 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("AppBundle::layout.html.twig", "AppBundle:AccessID:aidlist.html.twig", 1);
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
        echo $this->env->getExtension('translator')->getTranslator()->trans("Access Identifiers", array(), "messages");
        echo "<a href=\"#0\" class=\"cd-btn\"><i class=\"pe-7s-help1\"></i></a> <input id=\"livefilter-input\" class=\"pull-right\" type=\"text\" placeholder=\"Search\"></h1>
</div>

\t<div class=\"scroll_list_wrapper access_id_list\">
\t\t<ul class=\"scroll_list owl-carousel\">
\t\t\t<a class=\"item new\" href=\"";
        // line 10
        echo $this->env->getExtension('routing')->getPath("_aid_new");
        echo "\"><i class=\"pe-7s-plus\"></i><br />";
        echo $this->env->getExtension('translator')->getTranslator()->trans("Create New Access Identifier", array(), "messages");
        echo "</a>
\t\t\t";
        // line 11
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable(twig_reverse_filter($this->env, (isset($context["accessids"]) ? $context["accessids"] : $this->getContext($context, "accessids"))));
        foreach ($context['_seq'] as $context["_key"] => $context["aid"]) {
            // line 12
            echo "\t\t\t\t<a data-id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
            echo "\" class=\"item ";
            if (($this->getAttribute($context["aid"], "id", array()) == (isset($context["aid_selected"]) ? $context["aid_selected"] : $this->getContext($context, "aid_selected")))) {
                echo "selected";
            }
            echo " ";
            if ($this->getAttribute($context["aid"], "enabled", array())) {
                echo "enable";
            } else {
                echo "disabled";
            }
            echo "\">
\t\t\t\t<span class=\"aid\">ID<strong>";
            // line 13
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "id", array()), "html", null, true);
            echo "</strong></span> <br />
\t\t\t\t<div class=\"details-wrapper\">Caption<br />
\t\t\t\t<strong><span class=\"caption_value\">";
            // line 15
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "caption", array()), "html", null, true);
            echo "</span></strong><br />
\t\t\t\tPassword<br />
\t\t\t\t<strong><span class=\"password_value\">";
            // line 17
            echo twig_escape_filter($this->env, $this->getAttribute($context["aid"], "password", array()), "html", null, true);
            echo "</span></strong></div>
\t\t\t\t";
            // line 18
            if ($this->getAttribute($context["aid"], "enabled", array())) {
                echo "<span class=\"status\">";
                echo $this->env->getExtension('translator')->getTranslator()->trans("Enabled", array(), "messages");
                echo "</span>";
            } else {
                echo "<span class=\"status disable\">";
                echo $this->env->getExtension('translator')->getTranslator()->trans("Disabled", array(), "messages");
                echo "</span>";
            }
            echo "</a>
\t\t   ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['aid'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 20
        echo "\t\t</ul>
\t  </div>
\t  
<div class=\"container\">
\t<div class=\"details\" id=\"details\">\t
\t";
        // line 25
        echo (isset($context["details"]) ? $context["details"] : $this->getContext($context, "details"));
        echo "
\t</div>
</div>


<div class=\"cd-panel from-left\">
\t\t<div class=\"cd-panel-container\">
\t\t\t<div class=\"cd-panel-content\">
\t\t\t\t<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quam magnam accusamus obcaecati nisi eveniet quo veniam quibusdam veritatis autem accusantium doloribus nam mollitia maxime explicabo nemo quae aspernatur impedit cupiditate dicta molestias consectetur, sint reprehenderit maiores. Tempora, exercitationem, voluptate. Sapiente modi officiis nulla sed ullam, amet placeat, illum necessitatibus, eveniet dolorum et maiores earum tempora, quas iste perspiciatis quibusdam vero accusamus veritatis. Recusandae sunt, repellat incidunt impedit tempore iusto, nostrum eaque necessitatibus sint eos omnis! Beatae, itaque, in. Vel reiciendis consequatur saepe soluta itaque aliquam praesentium, neque tempora. Voluptatibus sit, totam rerum quo ex nemo pariatur tempora voluptatem est repudiandae iusto, architecto perferendis sequi, asperiores dolores doloremque odit. Libero, ipsum fuga repellat quae numquam cumque nobis ipsa voluptates pariatur, a rerum aspernatur aliquid maxime magnam vero dolorum omnis neque fugit laboriosam eveniet veniam explicabo, similique reprehenderit at. Iusto totam vitae blanditiis. Culpa, earum modi rerum velit voluptatum voluptatibus debitis, architecto aperiam vero tempora ratione sint ullam voluptas non! Odit sequi ipsa, voluptatem ratione illo ullam quaerat qui, vel dolorum eligendi similique inventore quisquam perferendis reprehenderit quos officia! Maxime aliquam, soluta reiciendis beatae quisquam. Alias porro facilis obcaecati et id, corporis accusamus? Ab porro fuga consequatur quisquam illo quae quas tenetur.</p>

\t\t\t\t<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Neque similique, at excepturi adipisci repellat ut veritatis officia, saepe nemo soluta modi ducimus velit quam minus quis reiciendis culpa ullam quibusdam eveniet. Dolorum alias ducimus, ad, vitae delectus eligendi, possimus magni ipsam repudiandae iusto placeat repellat omnis veritatis adipisci aliquam hic ullam facere voluptatibus ratione laudantium perferendis quos ut. Beatae expedita, itaque assumenda libero voluptatem adipisci maiores voluptas accusantium, blanditiis saepe culpa laborum iusto maxime quae aperiam fugiat odit consequatur soluta hic. Sed quasi beatae quia repellendus, adipisci facilis ipsa vel, aperiam, consequatur eaque mollitia quaerat. Iusto fugit inventore eveniet velit.</p>

\t\t\t\t<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Neque similique, at excepturi adipisci repellat ut veritatis officia, saepe nemo soluta modi ducimus velit quam minus quis reiciendis culpa ullam quibusdam eveniet. Dolorum alias ducimus, ad, vitae delectus eligendi, possimus magni ipsam repudiandae iusto placeat repellat omnis veritatis adipisci aliquam hic ullam facere voluptatibus ratione laudantium perferendis quos ut. Beatae expedita, itaque assumenda libero voluptatem adipisci maiores voluptas accusantium, blanditiis saepe culpa laborum iusto maxime quae aperiam fugiat odit consequatur soluta hic. Sed quasi beatae quia repellendus, adipisci facilis ipsa vel, aperiam, consequatur eaque mollitia quaerat. Iusto fugit inventore eveniet velit.</p>

\t\t\t\t<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quam magnam accusamus obcaecati nisi eveniet quo veniam quibusdam veritatis autem accusantium doloribus nam mollitia maxime explicabo nemo quae aspernatur impedit cupiditate dicta molestias consectetur, sint reprehenderit maiores. Tempora, exercitationem, voluptate. Sapiente modi officiis nulla sed ullam, amet placeat, illum necessitatibus, eveniet dolorum et maiores earum tempora, quas iste perspiciatis quibusdam vero accusamus veritatis. Recusandae sunt, repellat incidunt impedit tempore iusto, nostrum eaque necessitatibus sint eos omnis! Beatae, itaque, in. Vel reiciendis consequatur saepe soluta itaque aliquam praesentium, neque tempora. Voluptatibus sit, totam rerum quo ex nemo pariatur tempora voluptatem est repudiandae iusto, architecto perferendis sequi, asperiores dolores doloremque odit. Libero, ipsum fuga repellat quae numquam cumque nobis ipsa voluptates pariatur, a rerum aspernatur aliquid maxime magnam vero dolorum omnis neque fugit laboriosam eveniet veniam explicabo, similique reprehenderit at. Iusto totam vitae blanditiis. Culpa, earum modi rerum velit voluptatum voluptatibus debitis, architecto aperiam vero tempora ratione sint ullam voluptas non! Odit sequi ipsa, voluptatem ratione illo ullam quaerat qui, vel dolorum eligendi similique inventore quisquam perferendis reprehenderit quos officia! Maxime aliquam, soluta reiciendis beatae quisquam. Alias porro facilis obcaecati et id, corporis accusamus? Ab porro fuga consequatur quisquam illo quae quas tenetur.</p>
\t\t\t</div> <!-- cd-panel-content -->
\t\t</div> <!-- cd-panel-container -->
\t</div> <!-- cd-panel -->

            
";
    }

    public function getTemplateName()
    {
        return "AppBundle:AccessID:aidlist.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  104 => 25,  97 => 20,  81 => 18,  77 => 17,  72 => 15,  67 => 13,  52 => 12,  48 => 11,  42 => 10,  34 => 5,  31 => 4,  28 => 3,  11 => 1,);
    }
}
