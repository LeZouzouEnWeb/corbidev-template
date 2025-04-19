<?php

require_once plugin_dir_path(__DIR__) . 'includes/TemplateEngine.php';

/* Cette classe est nommée TemplateManager et est probablement utilisée pour gérer des modèles en PHP. */
class TemplateManager {
    /* Cette propriété est utilisée pour stocker une instance du
    Classe `MemplateEngine`, qui est probablement responsable du rendu des modèles HTML. En faisant ceci
    propriété privée, il est encapsulé dans la classe et ne peut être accessible ou modifié que à partir de
    dans la classe elle-même. Cela aide à maintenir l'encapsulation et l'intégrité des données dans le
    claSS. */
    private $engine;

    /**
     *Le constructeur initialise un objet TemplateEngine et ajoute un menu d'administration à l'aide d'un rappel
     *méthode.
     */
    public function __construct() {
        $this->engine = new TemplateEngine();
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    /**
     *La fonction `add_admin_menu` ajoute une page de menu pour gérer les modèles dans le panneau d'administration WordPress.
     */
    public function add_admin_menu() {
        add_menu_page(
            'Gestion des Templates', 
            'Templates', 
            'manage_options', 
            'template-manager', 
            [$this, 'render_admin_page'],
            'dashicons-admin-generic'
        );
    }

    /**
     *La fonction `render_admin_page` prépare les variables et rend un modèle HTML pour un administrateur
     *Page en php.
     */
    public function render_admin_page() {
        $variables = [
            'site_name' => get_bloginfo('name'),
            'admin_url' => admin_url(),
            'is_admin' => current_user_can('manage_options'),
            'users' => ['Alice', 'Bob', 'Charlie'],
            'year' => date('Y')
        ];
        
        echo $this->engine->render('example.html', $variables);
    }
}
