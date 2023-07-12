<?php

namespace Ostyna\Sing\Utils;

use Ostyna\Component\Error\FatalException;
use Ostyna\Component\Utils\CoreUtils;

class TemplateUtils {
  
  public static function sing(string $html_file_path, array $parameters = []): string {
    
    $code = file_get_contents($html_file_path);
    $compiled_code = self::compile_parameters($code, $parameters);

    return $compiled_code;
  }

  private static function compile_parameters(string $code, array $parameters = []): string {

    $code = self::replace_blocks($code);
    $code = self::replace_variables($code, $parameters);

    $code = self::replace_undefined($code);

    return $code;
  }

  private static function replace_variables(string $code, array $parameters = []): string {

    if(count($parameters) > 0) {
 
      preg_match_all('~\{{\s*(.*?)\s*\}}~is', $code, $matches);

      foreach($matches[1] as $expression) {
        $replacer = "";
        $data = explode('.', $expression, 2);
        // On vérifie si le tableau $data contient plus d'un élément
        // Ce qui veut dire que c'est un objet 
        // rediriger vers une page d'erreur si l'environement est dev
        if(count($data) > 1) {
          if(isset($parameters[$data[0]])) {
            $object = $parameters[$data[0]];

            if(is_object($object)) {
              $method = "get".ucfirst($data[1]);
              if(method_exists($object, $method)) {
                $replacer = preg_quote($object->$method());
              } elseif(method_exists($object, $data[1])) {
                $method = $data[1];
                $replacer = preg_quote($object->$method());
              } else {
                throw new FatalException("Aucune méthode correspondante à $expression", 0);
                // ERROR MISSING METHOD
              }
            } elseif (is_array($object)) {
              if(isset($object[$data[1]])) {
                $replacer = $object[$data[1]];
              } else {
                throw new FatalException("Aucune valeur de tableau correspondante à $expression", 0);
                // ERROR MISSING ARRAY VALUE
              }
            }

            $code = preg_replace('~\{{\s*('.$expression.')\s*\}}~is', $replacer, $code);
          } else {
            throw new FatalException("Aucune valeur correspondante à $expression définie", 0);
            // EXPRESSION NON DEFINI
          }
        } else {
          if(!is_array($expression) || !is_object($expression)){
            if(isset($parameters[$expression])) {
              $replacer = $parameters[$expression];
            } elseif($expression === "env") {
              $replacer = CoreUtils::get_env('APP_ENV');
            } else {
              throw new FatalException("Aucune valeur correspondante à $expression définie", 0);
              // EXPRESSION NON DEFINI
            }
            $code = preg_replace('~\{{\s*('.$expression.')\s*\}}~is', $replacer , $code);
          }
        }
      }
    }
    return $code;

  }

  private static function replace_blocks(string $code): string {

    preg_match_all('/{% ?include ?(.*?) ?%}/is', $code, $matches);
    foreach ($matches[1] as $block) {
      if(file_exists(CoreUtils::get_project_root().'/templates/web/'.$block)) {
        $blocks = preg_quote($block, '/');
        $code = preg_replace('/{% ?include ?('.$blocks.') ?%}/is', file_get_contents(CoreUtils::get_project_root().'/templates/web/'.$block) , $code);
      }
    }

    return $code;
  }

  private static function replace_undefined(string $code): string {
    $code = preg_replace('~\{{\s*(.*?)\s*\}}~is', '' , $code);
    $code = preg_replace('/{% ?block ?(.*?) ?%}/is', '' , $code);
    return $code;
  }

}