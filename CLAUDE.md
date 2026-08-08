# Instructions Claude pour projet Kirby PHP

Ce fichier sert de consigne de départ pour Claude au lancement d'un nouveau projet Kirby PHP. Applique ces règles avant d'écrire du code.

## Démarrage du projet

- Utilise la dernière version LTS ou stable supportée de PHP disponible au moment de créer le projet.
- Utilise la dernière version stable de Kirby disponible au moment de créer le projet, compatible avec la version PHP choisie.
- Vérifie les prérequis officiels de Kirby avant l'installation ou la mise à jour.
- Fige les versions importantes dans `composer.json` et `composer.lock` pour garder un projet reproductible.
- Ne démarre pas un projet avec des versions obsolètes de PHP, Kirby ou des dépendances critiques.
- il faut que content/ ne soit pas tracker par GIT, bien l'ajouter au .gitignore.

## Composer obligatoire

- Utilise toujours Composer pour importer les dépendances PHP.
- Utilise toujours Composer pour importer les composants, plugins, extensions ou librairies Kirby PHP.
- Tout composant Kirby PHP réutilisable doit pouvoir être installé par Composer.
- Ne copie-colle pas directement du code externe dans le projet si une dépendance Composer propre existe.
- Si un composant interne du studio est réutilisé, structure-le comme un package Composer ou prépare-le pour pouvoir le devenir.

## Blueprints Kirby

- Les blueprints doivent suivre une logique DRY stricte.
- Réutilise le plus possible les champs, sections, tabs, layouts et groupes de configuration existants.
- Évite de dupliquer des blocs YAML identiques ou très proches entre plusieurs blueprints.
- Quand plusieurs pages, fichiers ou blocks partagent une structure, extrais une configuration commune ou un modèle réutilisable.
- Les blueprints doivent rester lisibles, cohérents et faciles à recopier dans un autre projet Kirby.
- IMPORTANT : les noms de fields sont TOUJOURS en anglais, le label TOUJOURS en français.
  Exemple : `text_content:` avec `label: Texte`, `link_url:` avec `label: Lien`.
  Ne jamais imiter les noms français hérités (`titre`, `contenu`, `section_texte`) présents dans d'anciens blueprints.

## PHP

- le nom des fonction et des variables doivent être en anglais
- Toute fonction PHP doit déclarer ses types de paramètres.
- Toute fonction PHP doit déclarer son type de retour quand c'est possible.
- Toute méthode de classe doit déclarer ses types de paramètres et son type de retour quand c'est possible.
- Utilise `declare(strict_types=1);` dans les fichiers PHP applicatifs quand le contexte du projet le permet.
- Évite les signatures vagues. Préfère des types précis, des objets de valeur ou des tableaux documentés.
- Si un tableau structuré est nécessaire, documente précisément sa forme avec PHPDoc.

## PHPDoc obligatoire

- Toute fonction doit avoir un PHPDoc utile.
- Toute classe doit avoir un PHPDoc utile.
- Toute méthode publique doit avoir un PHPDoc utile.
- Le PHPDoc doit aider l'autocomplétion et l'analyse statique des IDE.
- Documente les paramètres, les retours, les exceptions et les formes de tableaux quand c'est pertinent.
- N'écris pas de PHPDoc vide ou évident : il doit clarifier le contrat du code.

Exemple attendu :

```php
<?php

declare(strict_types=1);

/**
 * Build a normalized option list for a Kirby select field.
 *
 * @param array<string, string> $labels
 * @return array<int, array{value: string, text: string}>
 */
function buildSelectOptions(array $labels): array
{
    return array_map(
        static fn (string $value, string $text): array => [
            'value' => $value,
            'text' => $text,
        ],
        array_keys($labels),
        $labels
    );
}
```

## DRY et réutilisation

- Le développement doit être strictement DRY : ne répète pas des blocs de code, comportements, blueprints, templates, snippets, types ou appels API déjà existants.
- Avant de créer une nouvelle fonction, classe, snippet, blueprint ou configuration, cherche si une version réutilisable existe déjà dans le projet.
- Si deux parties du code se ressemblent, extrais une fonction, une classe, un trait, un service, un helper ou un module commun.
- Mutualise les composants communs pour réduire la redondance au maximum.
- Centralise les constantes, mappings, helpers et règles métier partagés.
- Évite les dépendances cachées entre fichiers : les entrées, sorties, paramètres et types doivent être clairs.

## Modularité

- Découpe le code PHP en fichiers séparés par logique métier ou responsabilité technique.
- Utilise des fonctions, classes, services ou modules indépendants et réutilisables.
- Chaque classe ou module doit avoir une responsabilité claire.
- Les templates Kirby ne doivent pas contenir de logique métier lourde si elle peut être extraite dans une fonction, une classe ou un service.
- Les snippets doivent rester simples, lisibles et faciles à déplacer.
- Les appels API, transformations de données, validations et règles métier doivent être isolés dans des modules réutilisables.

## Code commun entre projets

- Le studio maintient beaucoup de projets Kirby : le code commun doit être favorisé systématiquement.
- Chaque composant Kirby PHP doit être conçu pour pouvoir être réimporté dans un autre projet du studio avec un minimum d'adaptation.
- Chaque classe, fonction, snippet, blueprint ou plugin interne doit être pensé pour la réutilisation entre projets.
- Limite les couplages à des noms de pages, structures de contenus, chemins, langues ou conventions trop spécifiques au projet.
- Quand une dépendance projet est inévitable, documente-la clairement près du code concerné.
- Privilégie des paramètres typés, des options configurables, des fonctions pures et des contrats explicites.

## Sécurité et configuration

- Place les clefs API et secrets dans un fichier `.env` ou dans le mécanisme de configuration sécurisé prévu par le projet.
- Ajoute ou vérifie `.env` dans `.gitignore`.
- Ne commite jamais de secret dans un dépôt public.
- Fournis un `.env.example` sans secret réel quand le projet a besoin de variables d'environnement.
- Vérifie les permissions, accès Panel et configurations sensibles avant une mise en production.

## Vérification avant livraison

- Lance les checks disponibles du projet avant de conclure : lint PHP, analyse statique, tests et audit Composer si les scripts existent.
- Corrige les erreurs de types et les problèmes PHPDoc avant de proposer le code comme terminé.
- Ne laisse pas de duplications évidentes, de dépendances importées hors Composer ou de code PHP non typé dans le projet.
