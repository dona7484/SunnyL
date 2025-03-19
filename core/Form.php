<?php
// namespace App\Core;

// Déclaration de la classe Form qui sera responsable de la création et gestion des formulaires
class Form 
{
    // Propriété privée qui va contenir les éléments du formulaire
    private $formElements;

    // Méthode pour récupérer les éléments du formulaire
    public function getFormElements() 
    {
        return $this->formElements;
    }

    // Méthode privée pour ajouter des attributs HTML à un élément du formulaire
    private function addAttributes(array $attributes): string
    {
        $att = ''; // Initialisation de la chaîne vide pour stocker les attributs
        // On parcourt le tableau d'attributs
        foreach ($attributes as $attribute => $value) {
            // Concaténation des attributs sous forme de chaîne HTML
            $att .= " $attribute=\"$value\"";
        }
        return $att; // Retourne la chaîne contenant les attributs HTML
    }

    // Méthode pour démarrer le formulaire, accepte l'action, la méthode, et les attributs
    public function startForm(string $action ='#', string $method = 'POST', array $attributes = []): self
    {
        // Création de la balise <form> avec l'action et la méthode spécifiées
        $this->formElements = "<form action='$action' method='$method'";
        // Si des attributs sont passés, on les ajoute à la balise <form>
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        return $this; // On retourne l'objet Form pour permettre la chaîne des appels
    }

    // Méthode pour ajouter un label dans le formulaire
    public function addLabel(string $for, string $text, array $attributes = []): self
    {
        // Création de la balise <label> avec l'attribut "for" et les attributs supplémentaires
        $this->formElements .= "<label for='$for'";
        // Ajout des attributs au label s'il y en a
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        $this->formElements .= $text . "</label>"; // Ajout du texte du label
        return $this; // Retour de l'objet pour permettre une chaîne d'appels
    }

    // Méthode pour ajouter un champ de type input dans le formulaire
    public function addInput(string $type, string $name, array $attributes = []): self
    {
        // Création de la balise <input> avec les attributs nécessaires
        $this->formElements .= "<input type='$type' name='$name'";
        // Ajout des attributs supplémentaires s'il y en a
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        return $this; // Retourne l'objet pour permettre une chaîne d'appels
    }

    // Méthode pour ajouter une zone de texte (textarea) dans le formulaire
    public function addTextarea(string $name, string $text ='', array $attributes = []): self
    {
        // Création de la balise <textarea> avec les attributs nécessaires
        $this->formElements .= "<textarea name='$name'";
        // Ajout des attributs supplémentaires s'il y en a
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        $this->formElements .= $text . "</textarea>"; // Ajout du texte initial de la zone de texte
        return $this; // Retourne l'objet pour permettre une chaîne d'appels
    }

    // Méthode pour ajouter une liste déroulante (select) dans le formulaire
    public function addSelect(string $name, array $options, array $attributes = []): self
    {
        // Création de la balise <select> avec les attributs nécessaires
        $this->formElements .= "<select name='$name'";
        // Ajout des attributs supplémentaires s'il y en a
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        // On parcourt les options de la liste déroulante et on les ajoute
        foreach ($options as $key => $value) {
            $this->formElements .= "<option value='$key'>$value</option>";
        }
        $this->formElements .= "</select>"; // Fermeture de la balise <select>
        return $this; // Retourne l'objet pour permettre une chaîne d'appels
    }

    // Méthode pour fermer le formulaire (balise </form>)
    public function endForm(): self
    {
        $this->formElements .= "</form>"; // Ajout de la balise de fermeture </form>
        return $this; // Retourne l'objet pour permettre une chaîne d'appels
    }

    // Méthode statique pour valider les données POST reçues dans le formulaire
    public static function validatePost(array $post, array $fields): bool
    {
        // On parcourt les champs nécessaires dans le formulaire
        foreach ($fields as $field) {
            // Si un champ est absent ou vide dans le tableau $_POST, on retourne false
            if (!isset($post[$field]) || !isset($post[$field])) {
                return false;
            }
        }
        return true; // Retourne true si tous les champs sont valides
    }

    // Méthode statique pour valider les fichiers envoyés dans le formulaire
    public static function validateFiles(array $files, array $fields): bool
    {
        foreach ($fields as $field) {
            if (!isset($files[$field]) || $files[$field]['error'] !== UPLOAD_ERR_OK) {
                return false;
            }
        }
        return true;
    }

    public function render(): string {
        return $this->formElements;
    }


}
?>    