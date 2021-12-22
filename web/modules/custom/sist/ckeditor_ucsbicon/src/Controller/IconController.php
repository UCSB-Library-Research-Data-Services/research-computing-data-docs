<?php

/**
 * @file
 * Contains \Drupal\ckeditor_ucsbicon\Controller\IconController.
 */

namespace Drupal\ckeditor_ucsbicon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class IconController  extends ControllerBase {

    public function loadicons(Request $request) {
        $data = array();
        $cid = 'ucsbicons';
        if ($cache = \Drupal::cache()->get($cid)) {
            $data = $cache->data;
        } else {
            $icon_folder = drupal_get_path('module', 'ckeditor_ucsbicon') . '/ucsbicon/icons/SVG';
            // Check if directory exists.
            if (!is_dir($_SERVER['HTTP_HOST'] . "/" . $icon_folder)) {
                $files = \Drupal::service('file_system')->scanDirectory($icon_folder, '/.*\.(svg)$/', []);
                asort($files);
                $addSlash = function($n)
                {
                    $filePath = $_SERVER['HTTP_HOST'] . '/' . $n;
                        $content = (string) \Drupal::httpClient()
                        ->get($filePath)
                        ->getBody();
                    return $content;
                };
                if(is_array($files)){
                    $data = array_map($addSlash, array_column($files, 'uri'));
                }
                //set caching
                \Drupal::cache()->set('ucsbicons', $data);
            }
        }
        $json_object = new \stdClass();
        $json_object->files = $data;
        return new JsonResponse($json_object);
    }
}
