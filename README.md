# Framework Ostyna : Sing

Sing est moteur de template basique (avec peu de fonctionnalité)
Dans l'état, il peut simplement remplacer des variables par leur valeur et récupérer le contenu d'un autre fichier de template.

## 1. Les Blocs

Un blocs correspond à un fichier de template. Il peut être inclut dans un autre fichier de la manière suivante:
```sh
{% include path/from/templates/folder %}
```

## 2. Les Valeurs

Les valeurs envoyées depuis la méthode render du controller peuvent être utilisées dans un fichier de template.
```sh
{{ data }}
```
La valeur correspondant à la clé *data* du tableau de paramètres de la méthode render sera insérer à la place.

Il est possible d'utiliser des tableaux à une seule dimension.
```sh
{{ array.value }}
```
ou des objets avec une méthode "get" défini.
```sh
{{ object.id }}
```
utilisera la méthode getId de la classe attribuer à la clé object pour récupérer la valeur
