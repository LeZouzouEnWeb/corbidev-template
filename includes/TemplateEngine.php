<?php

class TemplateEngine {
    private $cacheDir;

    /**
     *La fonction crée un répertoire de cache s'il n'existe pas déjà.
     */
    public function __construct() {
        $this->cacheDir = plugin_dir_path(__DIR__) . 'cache/';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     *La fonction de rendu dans PHP récupère et analyse un fichier de modèle avec des variables facultatives pour
     *rendu.
     * 
     * @param modèle
     *Fichier de modèle que vous souhaitez rendre. Il est utilisé pour localiser le fichier de modèle dans les «modèles»
     *Répertoire.
     * Variables @param Le paramètre `` dans la fonction `Render 'est un tableau facultatif qui
     *Vous permet de transmettre des données au fichier de modèle. Ces variables peuvent être utilisées dans le modèle
     *Fichier pour générer dynamiquement du contenu en fonction des valeurs fournies dans le tableau. Par exemple, vous
     *peut passer un tableau comme `['nom
     * 
     * @return Si le fichier de modèle existe, la fonction renvoie le contenu analysé du modèle
     *Fichier avec les variables fournies. Si le fichier de modèle n'existe pas, il renverra un message
     *indiquant que le modèle n'a pas été trouvé.
     */
    public function render($templateFile, $variables = []) {
        $filePath = plugin_dir_path(__DIR__) . "templates/$templateFile";

        if (!file_exists($filePath)) return "<p>Template non trouvé: $templateFile</p>";

        $content = file_get_contents($filePath);
        return $this->parseTemplate($content, $variables);
    }

    /**
     *La fonction «parseTemplate» dans PHP analyse un contenu de modèle en remplaçant les variables, manipulant
     *Comprend, appliquer des filtres, évaluer les conditions et les boucles de traitement.
     * 
     * @param Content La fonction `Partetemplate` que vous avez fournie est une fonction PHP qui analyse A
     *Modèle de contenu avec variables, inclut, filtres, conditions et boucles. Il remplace
     *Les espaces réservés avec des valeurs réelles et des processus de contrôle des structures dans le contenu du modèle.
     * Variables @param Le paramètre «Variables» dans la fonction «Partetemplate» est un tableau qui
     *Contient des paires de valeurs clés représentant les variables qui seront utilisées dans le modèle. Ces
     *Les variables peuvent être des valeurs ou des tableaux simples. La fonction traite le contenu du modèle par
     *Remplacement des espaces réservés par les valeurs correspondantes du tableau des «variables».
     * 
     * @return La fonction `Partemplate` renvoie le contenu traité après l'analyse incluse,
     *Remplacement des variables, appliquant des filtres, des conditions de manutention et des boucles de traitement dans le modèle
     *contenu.
     */
    private function parseTemplate($content, $variables) {
        // Gestion des includes {include 'header.html'}

        $content = preg_replace_callback('/{include \'(.*?)\'}/', function ($matches) {
            $includeFile = $matches[1];
            $includePath = plugin_dir_path(__DIR__) . "templates/$includeFile";

            return file_exists($includePath) ? file_get_contents($includePath) : "<p>Erreur: Fichier $includeFile introuvable</p>";
        }, $content);

        // Remplacement des variables simples

        foreach ($variables as $key => $value) {
            if (!is_array($value)) {
                $content = str_replace("{" . $key . "}", $value, $content);
            }
        }

        // Gestion des filtres {variable|upper}

        $content = preg_replace_callback('/{(\w+)\|(\w+)}/', function ($matches) use ($variables) {
            $key = $matches[1];
            $filter = $matches[2];
            $value = $variables[$key] ?? '';

            if ($filter === 'upper') return strtoupper($value);
            if ($filter === 'lower') return strtolower($value);

            return $value;
        }, $content);

        // Conditions management {if condition} ... {else} ... {endif}

        $content = preg_replace_callback('/{if (\w+)}(.*?){endif}/s', function ($matches) use ($variables) {
            $key = $matches[1];
            $body = $matches[2];

            $parts = explode('{else}', $body);
            $truePart = trim($parts[0]);
            $falsePart = isset($parts[1]) ? trim($parts[1]) : '';

            return !empty($variables[$key]) ? $truePart : $falsePart;
        }, $content);

        // Gestion des boucles {foreach list as item}...{endforeach}

        $content = preg_replace_callback('/{foreach (\w+) as (\w+)}(.*?){endforeach}/s', function ($matches) use ($variables) {
            $listKey = $matches[1];
            $itemKey = $matches[2];
            $body = $matches[3];

            if (!isset($variables[$listKey]) || !is_array($variables[$listKey])) {
                return '';
            }

            $result = '';
            foreach ($variables[$listKey] as $item) {
                $tempVars = [$itemKey => $item];
                $result .= $this->parseTemplate($body, $tempVars);
            }

            return $result;
        }, $content);

        return $content;
    }
}
